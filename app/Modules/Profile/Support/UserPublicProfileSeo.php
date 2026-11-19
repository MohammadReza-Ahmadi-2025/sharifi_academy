<?php

namespace App\Modules\Profile\Support;

final class UserPublicProfileSeo
{
    /**
     * @param  array<string, mixed>  $profile
     * @return array{title: string, description: string|null, canonical: string, keywords: string, og_image: string|null, og_type: string}
     */
    public static function forShow(array $profile, string $appName): array
    {
        $name = (string) ($profile['fullName'] ?? 'کاربر');
        $headline = filled($profile['headline'] ?? null) ? (string) $profile['headline'] : null;
        $bio = filled($profile['bio'] ?? null) ? (string) $profile['bio'] : null;
        $postsCount = (int) ($profile['posts_count'] ?? 0);

        $description = $headline
            ?: ($bio !== null
                ? mb_substr(strip_tags($bio), 0, 160)
                : ($postsCount > 0
                    ? "پروفایل و پست‌های {$name} در {$appName}"
                    : "پروفایل {$name} در {$appName}"));

        $avatarUrl = is_array($profile['avatar'] ?? null)
            ? ($profile['avatar']['url'] ?? null)
            : null;

        $title = $headline ? "{$name} · {$headline}" : $name;

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => (string) ($profile['profile_url'] ?? url('/')),
            'keywords' => $name,
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
                    'name' => (string) ($profile['fullName'] ?? 'کاربر'),
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
            'name' => (string) ($profile['fullName'] ?? 'کاربر'),
            'url' => $canonical,
            'worksFor' => [
                '@type' => 'Organization',
                'name' => $appName,
            ],
        ];

        if (filled($profile['headline'] ?? null)) {
            $data['jobTitle'] = (string) $profile['headline'];
        }

        $avatarUrl = is_array($profile['avatar'] ?? null)
            ? ($profile['avatar']['url'] ?? null)
            : null;
        if (is_string($avatarUrl) && $avatarUrl !== '') {
            $data['image'] = $avatarUrl;
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
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $appName,
                'url' => url('/'),
            ],
        ];
    }
}
