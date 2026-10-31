<?php

namespace App\Support\Seo;

use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\Storage\PublicStorageUrl;

class SiteJsonLd
{
    /**
     * @return array{organization: array<string, mixed>, webSite: array<string, mixed>}
     */
    public static function forHome(SiteSettingsService $settings, string $appName): array
    {
        $siteName = filled($settings->get('site_name'))
            ? (string) $settings->get('site_name')
            : $appName;

        $homeUrl = url('/');
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';

        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $siteName,
            'url' => $homeUrl,
        ];

        $description = $settings->get('seo_meta_description') ?: $settings->get('site_description');
        if (filled($description)) {
            $organization['description'] = (string) $description;
        }

        $logo = self::absoluteImage($settings->get('site_logo') ?: $settings->get('seo_og_image'));
        if ($logo !== null) {
            $organization['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
            $organization['image'] = $logo;
        }

        $email = $settings->get('contact_email');
        if (filled($email)) {
            $organization['email'] = (string) $email;
        }

        $phone = $settings->get('contact_phone');
        if (filled($phone)) {
            $organization['telephone'] = (string) $phone;
        }

        $address = $settings->get('address');
        if (filled($address)) {
            $organization['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => (string) $address,
            ];
        }

        $webSite = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'name' => $siteName,
            'url' => $homeUrl,
            'publisher' => [
                '@id' => $organizationId,
            ],
            'inLanguage' => 'fa-IR',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search.index').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        return [
            'organization' => $organization,
            'webSite' => $webSite,
        ];
    }

    /**
     * @return array{title: string, description: string|null, canonical: string, keywords: string, og_image: string|null, og_type: string}
     */
    public static function homeSeo(SiteSettingsService $settings, string $appName): array
    {
        $title = filled($settings->get('seo_meta_title'))
            ? (string) $settings->get('seo_meta_title')
            : (filled($settings->get('site_name')) ? (string) $settings->get('site_name') : $appName);

        $description = filled($settings->get('seo_meta_description'))
            ? (string) $settings->get('seo_meta_description')
            : (filled($settings->get('site_description')) ? (string) $settings->get('site_description') : null);

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => url('/'),
            'keywords' => (string) ($settings->get('seo_meta_keywords') ?? ''),
            'og_image' => self::absoluteImage($settings->get('seo_og_image') ?: $settings->get('site_logo')),
            'og_type' => 'website',
        ];
    }

    private static function absoluteImage(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        $path = PublicStorageUrl::normalize($value);
        if ($path === '') {
            $path = PublicStorageUrl::forPath($value);
        }

        if ($path === '') {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : url($path);
    }
}
