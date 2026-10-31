<?php

namespace App\Modules\Profile\Support;

class InstructorSeo
{
    /**
     * @param  array<string, mixed>  $profile
     * @return array{title: string, description: string|null, canonical: string, keywords: string, og_image: string|null, og_type: string}
     */
    public static function forShow(array $profile, string $appName): array
    {
        $name = (string) ($profile['fullName'] ?? 'استاد');
        $headline = filled($profile['headline'] ?? null) ? (string) $profile['headline'] : null;
        $bio = filled($profile['bio'] ?? null) ? (string) $profile['bio'] : null;
        $description = $headline
            ?: ($bio !== null ? mb_substr(strip_tags($bio), 0, 160) : "رزومه مدرس {$name} در {$appName}");

        $avatarUrl = is_array($profile['avatar'] ?? null)
            ? ($profile['avatar']['url'] ?? null)
            : null;

        return [
            'title' => "رزومه {$name}",
            'description' => $description,
            'canonical' => (string) ($profile['profile_url'] ?? route('instructors.show', ['user' => $profile['id']])),
            'keywords' => '',
            'og_image' => is_string($avatarUrl) && $avatarUrl !== '' ? $avatarUrl : null,
            'og_type' => 'profile',
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public static function breadcrumbJsonLd(array $profile): array
    {
        $canonical = (string) ($profile['profile_url'] ?? url('/'));

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'خانه',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => (string) ($profile['fullName'] ?? 'استاد'),
                    'item' => $canonical,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public static function personJsonLd(array $profile, string $appName): array
    {
        $canonical = (string) ($profile['profile_url'] ?? url('/'));
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            '@id' => $canonical.'#person',
            'name' => (string) ($profile['fullName'] ?? 'استاد'),
            'url' => $canonical,
            'jobTitle' => filled($profile['headline'] ?? null) ? (string) $profile['headline'] : 'مدرس',
            'worksFor' => [
                '@type' => 'Organization',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];

        if (filled($profile['bio'] ?? null)) {
            $data['description'] = strip_tags((string) $profile['bio']);
        }

        $avatarUrl = is_array($profile['avatar'] ?? null) ? ($profile['avatar']['url'] ?? null) : null;
        if (is_string($avatarUrl) && $avatarUrl !== '') {
            $data['image'] = $avatarUrl;
        }

        $skills = collect($profile['skills'] ?? [])
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
        if ($skills !== []) {
            $data['knowsAbout'] = $skills;
        }

        return $data;
    }

    /**
     * @param  array{title: string, description: string|null, canonical: string}  $seo
     * @return array<string, mixed>
     */
    public static function profilePageJsonLd(array $seo, string $appName): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'name' => $seo['title'],
            'description' => $seo['description'],
            'url' => $seo['canonical'],
            'mainEntity' => [
                '@id' => $seo['canonical'].'#person',
            ],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];
    }
}
