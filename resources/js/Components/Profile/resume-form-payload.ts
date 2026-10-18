import type { FileUploadItem } from '@/Components/shared/file-upload';

type ResumeFormPayload = Record<string, unknown>;

export function withResumeAttachmentsPayload(
    data: ResumeFormPayload,
    attachmentFiles: FileUploadItem[],
    removeAttachmentIds: number[],
): ResumeFormPayload {
    const newFiles = attachmentFiles.filter((item) => item.file instanceof File).map((item) => item.file as File);

    return {
        ...data,
        attachments: newFiles.length > 0 ? newFiles : undefined,
        remove_attachment_ids: removeAttachmentIds.length > 0 ? removeAttachmentIds : undefined,
    };
}
