<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitasi HTML untuk output rich-text (TinyMCE) agar aman dari XSS
 * namun tetap mempertahankan format dasar: paragraf, bold/italic,
 * list, link, heading, gambar, dan tabel.
 *
 * Tanpa dependensi eksternal — memakai DOMDocument bawaan PHP.
 */
class HtmlSanitizer
{
    /** Tag yang diizinkan (dibiarkan utuh). */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'span', 'div',
    ];

    /** Tag berbahaya: dibuang beserta seluruh isinya. */
    private const DROP_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'textarea', 'select', 'button', 'link', 'meta', 'base', 'noscript',
        'svg', 'math', 'applet', 'frame', 'frameset',
    ];

    /** Atribut yang diizinkan per tag (kunci '*' berlaku untuk semua). */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        '*' => ['class'],
    ];

    /**
     * Bersihkan HTML dari tag/atribut berbahaya.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8">'.$html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            // Fallback: buang tag berbahaya secara kasar bila DOM gagal.
            return self::stripDangerous($html);
        }

        // Buang deklarasi XML sementara yang dipakai untuk memaksa UTF-8,
        // agar tidak ikut tersimpan/di-render sebagai teks.
        foreach (iterator_to_array($dom->childNodes) as $child) {
            if ($child instanceof DOMProcessingInstruction) {
                $dom->removeChild($child);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        $root = $body ?? $dom->documentElement;

        if ($root instanceof DOMNode) {
            self::walk($root);
        }

        $result = '';
        if ($body instanceof DOMNode) {
            foreach (iterator_to_array($body->childNodes) as $child) {
                $result .= $dom->saveHTML($child);
            }
        } else {
            $result = $dom->saveHTML();
        }

        return trim($result);
    }

    /**
     * Rekursif: buang tag berbahaya, sanitasi atribut, unwrap tag tak dikenal.
     */
    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            // 1) Tag berbahaya -> hapus seluruh node (beserta isinya).
            if (in_array($tag, self::DROP_TAGS, true)) {
                $node->removeChild($child);
                continue;
            }

            // 2) Rekursi dulu ke anak-anak.
            self::walk($child);

            // 3) Tag tidak dikenal -> unwrap (pertahankan isinya).
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::unwrap($child);
                continue;
            }

            // 4) Sanitasi atribut pada tag yang diizinkan.
            self::sanitizeAttributes($child);
        }
    }

    /**
     * Ganti node dengan anak-anaknya (unwrap).
     */
    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent instanceof DOMNode) {
            return;
        }

        while ($element->firstChild instanceof DOMNode) {
            $parent->insertBefore($element->firstChild, $element);
        }
        $parent->removeChild($element);
    }

    /**
     * Hapus atribut yang tidak diizinkan & blokir URL javascript:.
     */
    private static function sanitizeAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
        $global = self::ALLOWED_ATTRIBUTES['*'] ?? [];
        $whitelist = array_merge($allowed, $global);

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            // Selalu buang event handler & atribut on*.
            if (str_starts_with($name, 'on') || $name === 'style') {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (! in_array($name, $whitelist, true)) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            // Blokir javascript: pada href/src.
            if (in_array($name, ['href', 'src'], true)) {
                $value = trim((string) $attribute->nodeValue);
                if (preg_match('/^\s*(javascript|vbscript|data):/i', $value)) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }
    }

    /**
     * Fallback kasar berbasis regex bila DOMDocument gagal.
     */
    private static function stripDangerous(string $html): string
    {
        $html = preg_replace('/<\s*(script|style|iframe|object|embed|form|input|textarea|select|button|svg|math)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html) ?? $html;
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*(javascript|vbscript|data):/i', '$1=$2#', $html) ?? $html;

        return $html;
    }
}
