import type { InstructorResumeProfile, ResumeEntry } from '@/types/instructor-resume';
import { formatFileSize } from '@/Components/shared/file-upload';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
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

function ResumeTimelineEntry({ entry }: { entry: ResumeEntry }) {
    return (
        <li className="relative ps-6">
            <span
                className="absolute start-0 top-1.5 size-2.5 rounded-full bg-primary ring-4 ring-primary/15"
                aria-hidden
            />
            <div className="flex flex-wrap items-baseline justify-between gap-2">
                <h4 className="text-base font-bold text-neutral-black">{entry.title}</h4>
                {entry.is_current ? (
                    <span className="text-[0.7rem] font-bold text-emerald-700">فعلی</span>
                ) : null}
            </div>
            {entry.organization ? (
                <p className="mt-0.5 text-sm font-medium text-primary/80">{entry.organization}</p>
            ) : null}
            {entry.period ? <p className="mt-1 text-xs text-neutral-black/45">{entry.period}</p> : null}
            {entry.description ? (
                <p className="mt-2 whitespace-pre-line text-sm leading-7 text-neutral-black/70">
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
        </li>
    );
}

type Props = {
    profile: InstructorResumeProfile;
    /** هدر بزرگ‌تر برای صفحه عمومی */
    variant?: 'page' | 'compact';
    className?: string;
    /** When true, header (name/avatar) is rendered by the parent page. */
    hideHeader?: boolean;
};

export function InstructorResumeProfileView({
    profile,
    variant = 'page',
    className,
    hideHeader = false,
}: Props) {
    const avatarAlt = profile.avatar?.alt ?? profile.fullName;
    const isPage = variant === 'page';
    const hasSkills = Boolean(profile.skills && profile.skills.length > 0);
    const hasSections = profile.sections.length > 0;

    return (
        <div className={cn('space-y-8', className)}>
            {!hideHeader ? (
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
                        <h1
                            className={cn(
                                'font-bold text-neutral-black',
                                isPage ? 'text-2xl md:text-3xl' : 'text-lg',
                            )}
                        >
                            {profile.fullName}
                        </h1>
                        {profile.headline ? (
                            <p className="mt-1 text-sm text-neutral-black/60 md:text-base">{profile.headline}</p>
                        ) : null}
                        {profile.bio ? (
                            <SiteRichHtml
                                html={profile.bio}
                                className="mt-3 text-sm leading-relaxed text-neutral-black/75 md:text-[0.95rem]"
                            />
                        ) : null}
                    </div>
                </header>
            ) : null}

            {hasSkills ? (
                <section>
                    <h2 className="mb-3 text-sm font-extrabold tracking-wide text-neutral-black/50">مهارت‌ها</h2>
                    <ul className="flex flex-wrap gap-2">
                        {profile.skills!.map((skill) => (
                            <li
                                key={skill.id}
                                className="rounded-full border border-primary/15 bg-primary/[0.06] px-3.5 py-1.5 text-xs font-semibold text-primary"
                            >
                                {skill.name}
                                {skill.level != null ? (
                                    <span className="ms-1 font-medium text-primary/55">
                                        · {skill.level.toLocaleString('fa-IR')}/۵
                                    </span>
                                ) : null}
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            {!hasSections ? (
                hideHeader ? null : (
                    <p className="py-8 text-center text-sm text-neutral-black/55">اطلاعات رزومه هنوز ثبت نشده است.</p>
                )
            ) : isPage ? (
                profile.sections.map((section) => (
                    <section key={section.type} className="space-y-4">
                        <h2 className="text-lg font-extrabold text-neutral-black">{section.label}</h2>
                        <ul className="relative space-y-6 border-s-2 border-primary/15 ms-1">
                            {section.items.map((entry) => (
                                <ResumeTimelineEntry key={entry.id} entry={entry} />
                            ))}
                        </ul>
                    </section>
                ))
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
