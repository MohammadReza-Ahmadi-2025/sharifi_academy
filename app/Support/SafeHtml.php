<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitize rich HTML from CKEditor / admin editors before storage and display.
 * Scripts, event handlers, and javascript: URLs are stripped.
 */
class SafeHtml
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $trimmed = trim($html);
        if ($trimmed === '') {
            return '';
        }

        return trim(self::purifier()->purify($trimmed));
    }

    public static function nullable(?string $html): ?string
    {
        $cleaned = self::clean($html);

        return $cleaned === '' ? null : $cleaned;
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier instanceof HTMLPurifier) {
            return self::$purifier;
        }

        $cachePath = storage_path('app/htmlpurifier');
        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Cache.SerializerPermissions', 0755);
        $config->set('HTML.SafeIframe', false);
        $config->set('HTML.SafeObject', false);
        $config->set('HTML.SafeEmbed', false);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'tel' => true,
        ]);
        // CKEditor-friendly subset; no script / form / iframe / style XSS vectors.
        $config->set(
            'HTML.Allowed',
            'p,br,hr,strong,b,em,i,u,s,strike,sub,sup,ul,ol,li,'
            .'a[href|title|target|rel|name],'
            .'h1,h2,h3,h4,h5,h6,blockquote,pre,code,'
            .'table,thead,tbody,tfoot,tr,th[colspan|rowspan],td[colspan|rowspan],'
            .            'img[src|alt|title|width|height],'
            .'span,div'
        );

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
