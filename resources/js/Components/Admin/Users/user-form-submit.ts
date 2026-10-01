import type { FileUploadItem } from '@/Components/shared/file-upload';

type UserFormPayload = Record<string, unknown>;

export function withAvatarPayload(
    data: UserFormPayload,
    avatarFiles: FileUploadItem[],
    options?: { removeAvatar?: boolean },
): UserFormPayload {
    const fileItem = avatarFiles[0];

    return {
        ...data,
        avatar: fileItem?.file ?? null,
        avatar_alt: fileItem?.alt ?? '',
        avatar_title: fileItem?.title ?? '',
        remove_avatar: options?.removeAvatar ?? false,
    };
}
