<?php

namespace App\Models\Concerns;

/**
 * Dukungan konten multi-bahasa pada model CMS.
 *
 * Setiap atribut yang translatable disimpan pada dua kolom dengan suffix:
 *   - `_id`  -> Bahasa Indonesia
 *   - `_en`  -> English
 *
 * Method t() mengembalikan nilai sesuai locale aktif dengan fallback berjenjang:
 * locale aktif -> Bahasa Indonesia -> kolom dasar (tanpa suffix).
 */
trait Translatable
{
    /**
     * Ambil nilai atribut sesuai locale aktif (atau locale tertentu).
     */
    public function t(string $attribute, ?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();

        if (in_array($locale, ['en', 'id'], true)) {
            $localized = $this->getAttribute($attribute.'_'.$locale);
            if ($localized !== null && $localized !== '') {
                return (string) $localized;
            }
        }

        $indonesian = $this->getAttribute($attribute.'_id');
        if ($indonesian !== null && $indonesian !== '') {
            return (string) $indonesian;
        }

        $base = $this->getAttribute($attribute);

        return $base !== null ? (string) $base : null;
    }

    /**
     * Daftar atribut yang memiliki versi bahasa. Di-override oleh model.
     *
     * @return array<int, string>
     */
    public function translatableAttributes(): array
    {
        return [];
    }

    /**
     * Nilai mentah per locale (untuk mengisi form admin).
     *
     * @return array{id: ?string, en: ?string}
     */
    public function translations(string $attribute): array
    {
        return [
            'id' => $this->getAttribute($attribute.'_id') ?: $this->getAttribute($attribute),
            'en' => $this->getAttribute($attribute.'_en'),
        ];
    }
}
