import type { InstructorResumeProfile, ResumeEntry } from '@/types/instructor-resume';
import { formatFileSize } from '@/Components/shared/file-upload';
import { cn } from '@/lib/utils';
import { ExternalLink, FileText } from 'lucide-react';

function initials(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return '؟';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 1);
    }

    return `${parts[0].slice(0, 1)}${parts[1].slice(0, 1)}`;
}

function AttachmentList({ attachments }: { attachments: ResumeEntry['attachments'] }) {
    if (!attachments?.length) {
        return null;
    }

    return (
        <ul className="mt-3 flex flex-wrap gap-2">
            {attachments.map((file) => (
                <li key={file.id}>
                    <a
                        href={file.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex max-w-full items-center gap-2 rounded-lg border border-secondary/50 bg-neutral-white px-3 py-1.5 text-xs font-medium text-primary transition hover:border-primary/30 hover:bg-primary/5"
                    >
                        <FileText className="size-3.5 shrink-0" aria-hidden />
                        <span className="truncate">{file.name}</span>
                        <span className="shrink-0 text-neutral-black/45">({formatFileSize(file.size)})</span>
                    </a>
                </li>
            ))}
        </ul>
    );
}

function ResumeEntryCard({ entry, className }: { entry: ResumeEntry; className?: string }) {
    return (
        <article
            className={cn(
                'rounded-xl border border-secondary/45 bg-neutral-white p-4 shadow-[0_1px_8px_-4px_rgba(0,72,158,0.1)]',
                className,
            )}
        >
            <div className="flex flex-wrap items-start justify-between gap-2">
                <div className="min-w-0">
                    <h4 className="font-semibold text-neutral-black">{entry.title}</h4>
                    {entry.organization ? (
                        <p className="mt-0.5 text-sm text-neutral-black/65">{entry.organization}</p>
                    ) : null}
                    {entry.period ? <p className="mt-1 text-xs text-neutral-black/50">{entry.period}</p> : null}
                </div>
                {entry.is_current ? (
                    <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-[0.65rem] font-semibold text-emerald-700">
                        فعلی
                    </span>
                ) : null}
            </div>
            {entry.description ? (
                <p className="mt-3 whitespace-pre-line text-sm leading-relaxed text-neutral-black/75">
                    {entry.description}
                </p>
            ) : null}
            {entry.link ? (
                <a
                    href={entry.link}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline"
                >
                    مشاهده لینک
                    <ExternalLink className="size-3" aria-hidden />
                </a>
            ) : null}
            <AttachmentList attachments={entry.attachments} />
        </article>
    );
}

type Props = {
    profile: InstructorResumeProfile;
    /** هدر بزرگ‌تر برای صفحه عمومی */
    variant?: 'page' | 'compact';
    className?: string;
};

export function InstructorResumeProfileView({ profile, variant = 'page', className }: Props) {
    const avatarAlt = profile.avatar?.alt ?? profile.fullName;
    const isPage = variant === 'page';

    return (
        <div className={cn('space-y-8', className)}>
            <header
                className={cn(
                    'flex flex-col gap-4 border-b border-secondary/40 pb-6 sm:flex-row sm:items-start',
                    isPage && 'sm:gap-6',
                )}
            >
                <div
                    className={cn(
                        'shrink-0 overflow-hidden rounded-2xl bg-secondary/30',
                        isPage ? 'size-24 sm:size-28' : 'size-16',
                    )}
                >
                    {profile.avatar?.url ? (
                        <img src={profile.avatar.url} alt={avatarAlt} className="size-full object-cover" />
                    ) : (
                        <span
                            className={cn(
                                'flex size-full items-center justify-center font-bold text-primary',
                                isPage ? 'text-2xl' : 'text-lg',
                            )}
                        >
                            {initials(profile.fullName)}
                        </span>
                    )}
                </div>
                <div className="min-w-0 flex-1">
                    <h1 className={cn('font-bold text-neutral-black', isPage ? 'text-2xl md:text-3xl' : 'text-lg')}>
                        {profile.fullName}
                    </h1>
                    {profile.headline ? (
                        <p className="mt-1 text-sm text-neutral-black/60 md:text-base">{profile.headline}</p>
                    ) : null}
                    {profile.bio ? (
                        <p className="mt-3 whitespace-pre-line text-sm leading-relaxed text-neutral-black/75 md:text-[0.95rem]">
                            {profile.bio}
                        </p>
                    ) : null}
                </div>
            </header>

            {profile.skills && profile.skills.length > 0 ? (
                <section>
                    <h2 className="mb-3 text-base font-bold text-neutral-black">مهارت‌ها</h2>
                    <ul className="flex flex-wrap gap-2">
                        {profile.skills.map((skill) => (
                            <li
                                key={skill.id}
                                className="rounded-full border border-secondary/50 bg-secondary/15 px-3 py-1 text-xs font-medium text-neutral-black/80"
                            >
                                {skill.name}
                                {skill.level != null ? (
                                    <span className="ms-1 text-neutral-black/45">
                                        · {skill.level.toLocaleString('fa-IR')}/۵
                                    </span>
                                ) : null}
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            {profile.sections.length === 0 ? (
                <p className="text-center text-sm text-neutral-black/55 py-8">اطلاعات رزومه هنوز ثبت نشده است.</p>
            ) : (
                profile.sections.map((section) => (
                    <section key={section.type} className="space-y-3">
                        <h2 className="text-base font-bold text-primary md:text-lg">{section.label}</h2>
                        <div className="space-y-3">
                            {section.items.map((entry) => (
                                <ResumeEntryCard key={entry.id} entry={entry} />
                            ))}
                        </div>
                    </section>
                ))
            )}
        </div>
    );
}
