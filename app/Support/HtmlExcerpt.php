<?php

namespace App\Support;

class HtmlExcerpt
{
    public static function plainText(?string $html, int $limit = 160): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 1).'…';
    }
}
