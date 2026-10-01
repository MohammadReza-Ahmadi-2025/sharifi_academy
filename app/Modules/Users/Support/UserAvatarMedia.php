<?php

namespace App\Modules\Users\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserAvatarMedia
{
    public const COLLECTION = 'avatar';

    public const MAX_KILOBYTES = 2048;

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:'.self::MAX_KILOBYTES,
            ],
            'avatar_alt' => ['nullable', 'string', 'max:255'],
            'avatar_title' => ['nullable', 'string', 'max:120'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ];
    }

    public static function syncFromRequest(User $user, Request $request): void
    {
        if ($request->boolean('remove_avatar')) {
            $user->clearMediaCollection(self::COLLECTION);

            return;
        }

        if (! $request->hasFile('avatar')) {
            return;
        }

        $user->clearMediaCollection(self::COLLECTION);

        $adder = $user->addMediaFromRequest('avatar');

        $title = $request->input('avatar_title');
        if (is_string($title) && trim($title) !== '') {
            $adder->usingName(trim($title));
        }

        $alt = $request->input('avatar_alt');
        $properties = [];
        if (is_string($alt) && trim($alt) !== '') {
            $properties['alt'] = trim($alt);
        }

        if ($properties !== []) {
            $adder->withCustomProperties($properties);
        }

        $adder->toMediaCollection(self::COLLECTION);
    }

    public static function payload(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $media = $user->getFirstMedia(self::COLLECTION);

        if ($media === null) {
            return null;
        }

        return self::mediaToArray($media);
    }

    /**
     * @return array{url: string, alt: string|null, title: string|null}
     */
    public static function mediaToArray(Media $media): array
    {
        return [
            'url' => $media->getUrl(),
            'alt' => $media->getCustomProperty('alt'),
            'title' => $media->name,
        ];
    }
}
