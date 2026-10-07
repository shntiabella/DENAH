<?php

namespace App\Support;

use Generator;
use RuntimeException;

/**
 * Membaca file GeoJSON/JSON besar secara bertahap (streaming) sehingga
 * seluruh isi file tidak dimuat sekaligus ke dalam memori.
 *
 * Mendukung FeatureCollection — setiap feature dibaca dan di-decode satu
 * per satu — serta Feature tunggal (diproses sebagai satu fitur utuh).
 * Scanner hanya menghitung kurung kurawal di luar string, sehingga aman
 * terhadap tanda kurung di dalam nilai properti.
 */
final class GeoJsonStreamReader
{
    private const CHUNK_SIZE = 1048576;

    /** @var resource */
    private $handle;

    private string $chunk = '';

    private int $chunkPos = 0;

    private bool $eof = false;

    /** @var array<int, string> */
    private array $pushback = [];

    private ?bool $featuresLocated = null;

    /**
     * @throws RuntimeException saat file tidak bisa dibuka.
     */
    public function __construct(string $path)
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Tidak dapat membuka file GeoJSON untuk diproses.');
        }
        $this->handle = $handle;
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * Deteksi apakah dokumen merupakan FeatureCollection.
     * Jika false, dokumen diperlakukan sebagai satu Feature tunggal.
     */
    public function isFeatureCollection(): bool
    {
        if ($this->featuresLocated === null) {
            $this->featuresLocated = $this->locateFeaturesBracket();
        }

        return $this->featuresLocated;
    }

    /**
     * Iterasi setiap feature dari FeatureCollection.
     *
     * @return Generator<int, mixed> menghasilkan array feature yang sudah di-decode
     *
     * @throws RuntimeException bila struktur array "features" tidak valid.
     */
    public function features(): Generator
    {
        if (! $this->isFeatureCollection()) {
            throw new RuntimeException('Dokumen bukan FeatureCollection, tidak dapat mengiterasi features.');
        }

        while (true) {
            $this->skipWhitespace();
            $byte = $this->peekByte();
            if ($byte === null) {
                break;
            }
            if ($byte === ']') {
                $this->readByte();
                break;
            }
            if ($byte === ',') {
                $this->readByte();

                continue;
            }
            if ($byte === '{') {
                $decoded = json_decode($this->readObjectJson(), true);
            } else {
                $decoded = json_decode($this->readScalarToken(), true);
            }

            yield $decoded;
        }
    }

    /**
     * Mencari pasangan key "features" di level atas lalu memposisikan
     * pembacaan tepat setelah tanda '[' pembuka array features.
     */
    private function locateFeaturesBracket(): bool
    {
        while (true) {
            $byte = $this->readByte();
            if ($byte === null) {
                return false;
            }
            if ($byte !== '"') {
                continue;
            }

            $key = $this->readJsonString();
            if ($key !== 'features') {
                $this->skipValue();

                continue;
            }

            $this->skipWhitespace();
            if ($this->readByte() !== ':') {
                return false;
            }
            $this->skipWhitespace();
            if ($this->peekByte() !== '[') {
                return false;
            }
            $this->readByte();

            return true;
        }
    }

    /**
     * Membaca nilai JSON string setelah tanda kutip pembuka dikonsumsi.
     * Tanda escape dipertahankan apa adanya.
     */
    private function readJsonString(): string
    {
        $value = '';
        while (($byte = $this->readByte()) !== null) {
            if ($byte === '\\') {
                $escaped = $this->readByte();
                if ($escaped === null) {
                    break;
                }
                $value .= $byte.$escaped;

                continue;
            }
            if ($byte === '"') {
                return $value;
            }
            $value .= $byte;
        }

        return $value;
    }

    /**
     * Melewati satu nilai JSON (string/objek/array/primitif) setelah
     * pembacaan key selesai. Posisi akhir: tepat sebelum delimiter luar.
     */
    private function skipValue(): void
    {
        $this->skipWhitespace();
        if ($this->peekByte() === ':') {
            $this->readByte();
            $this->skipWhitespace();
        }

        $byte = $this->peekByte();
        if ($byte === null) {
            return;
        }
        if ($byte === '"') {
            $this->readByte();
            $this->readJsonString();

            return;
        }
        if ($byte === '{') {
            $this->skipContainer('{', '}');

            return;
        }
        if ($byte === '[') {
            $this->skipContainer('[', ']');

            return;
        }

        while (($byte = $this->readByte()) !== null) {
            if ($byte === ',' || $byte === '}' || $byte === ']') {
                $this->pushback[] = $byte;

                return;
            }
        }
    }

    /**
     * Melewati objek/array bertingkat secara string-aware.
     */
    private function skipContainer(string $open, string $close): void
    {
        $this->readByte();
        $depth = 1;
        while (($byte = $this->readByte()) !== null) {
            if ($byte === '"') {
                $this->readByte();
                $this->readJsonString();

                continue;
            }
            if ($byte === $open) {
                $depth++;
            } elseif ($byte === $close) {
                $depth--;
                if ($depth === 0) {
                    return;
                }
            }
        }
    }

    /**
     * Membaca teks JSON satu objek feature (sampai kurung penutup padanannya).
     */
    private function readObjectJson(): string
    {
        $this->readByte();
        $json = '{';
        $depth = 1;
        while (($byte = $this->readByte()) !== null) {
            $json .= $byte;
            if ($byte === '"') {
                while (($char = $this->readByte()) !== null) {
                    $json .= $char;
                    if ($char === '\\') {
                        $next = $this->readByte();
                        if ($next === null) {
                            break;
                        }
                        $json .= $next;

                        continue;
                    }
                    if ($char === '"') {
                        break;
                    }
                }

                continue;
            }
            if ($byte === '{') {
                $depth++;
            } elseif ($byte === '}') {
                $depth--;
                if ($depth === 0) {
                    return $json;
                }
            }
        }

        throw new RuntimeException('File GeoJSON tidak lengkap (kurung objek tidak ditutup).');
    }

    /**
     * Membaca token primitif (null/true/false/number) sampai delimiter.
     */
    private function readScalarToken(): string
    {
        $token = '';
        while (($byte = $this->readByte()) !== null) {
            if ($byte === ',' || $byte === ']') {
                $this->pushback[] = $byte;
                break;
            }
            $token .= $byte;
        }

        return trim($token);
    }

    private function skipWhitespace(): void
    {
        while (($byte = $this->peekByte()) !== null) {
            if ($byte !== ' ' && $byte !== "\t" && $byte !== "\r" && $byte !== "\n") {
                break;
            }
            $this->readByte();
        }
    }

    /**
     * @phpstan-impure
     */
    private function readByte(): ?string
    {
        if ($this->pushback !== []) {
            return array_pop($this->pushback);
        }
        if ($this->chunkPos >= strlen($this->chunk)) {
            if ($this->eof) {
                return null;
            }
            $next = fread($this->handle, self::CHUNK_SIZE);
            if ($next === false || $next === '') {
                $this->eof = true;

                return null;
            }
            $this->chunk = $next;
            $this->chunkPos = 0;
        }

        return $this->chunk[$this->chunkPos++];
    }

    /**
     * @phpstan-impure
     */
    private function peekByte(): ?string
    {
        $byte = $this->readByte();
        if ($byte !== null) {
            $this->pushback[] = $byte;
        }

        return $byte;
    }
}
