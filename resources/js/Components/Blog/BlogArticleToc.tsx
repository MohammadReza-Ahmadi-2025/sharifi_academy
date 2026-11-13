import { TelegramIcon, WhatsappIcon } from '@/Components/Site/social-type-icons';
import { cn } from '@/lib/utils';
import type { RichHtmlTocItem } from '@/lib/rich-html-post-process';
import { Check, Link2, List } from 'lucide-react';
import { useCallback, useEffect, useState, type MouseEvent } from 'react';

type Props = {
    toc: RichHtmlTocItem[];
    onNavigate: (id: string) => void;
};

function indentClass(level: RichHtmlTocItem['level']): string {
    if (level === 2) return 'pr-2';
    if (level === 3) return 'pr-4';
    if (level === 4) return 'pr-6';
    return '';
}

export function BlogArticleToc({ toc, onNavigate }: Props) {
    const [activeId, setActiveId] = useState(toc[0]?.id ?? '');
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        if (toc.length === 0) {
            return;
        }

        const elements = toc
            .map((item) => document.getElementById(item.id))
            .filter((el): el is HTMLElement => el !== null);

        if (elements.length === 0) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

                if (visible[0]?.target?.id) {
                    setActiveId(visible[0].target.id);
                }
            },
            { rootMargin: '-15% 0px -65% 0px', threshold: [0, 0.25, 0.5, 1] },
        );

        elements.forEach((el) => observer.observe(el));

        return () => observer.disconnect();
    }, [toc]);

    const onClick = useCallback(
        (event: MouseEvent<HTMLAnchorElement>, id: string) => {
            event.preventDefault();
            setActiveId(id);
            onNavigate(id);
        },
        [onNavigate],
    );

    const shareUrl = typeof window !== 'undefined' ? window.location.href : '';

    const copyLink = async () => {
        try {
            await navigator.clipboard.writeText(window.location.href);
            setCopied(true);
            window.setTimeout(() => setCopied(false), 1800);
        } catch {
            // ignore
        }
    };

    if (toc.length === 0) {
        return null;
    }

    return (
        <aside className="lg:sticky lg:top-24">
            <div className="overflow-hidden rounded-2xl border border-secondary/60 bg-neutral-white shadow-[0_8px_28px_-18px_rgba(0,40,90,0.28)]">
                <div className="flex items-center gap-2 border-b border-secondary/50 px-4 py-3.5">
                    <span className="flex size-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <List className="size-4" aria-hidden />
                    </span>
                    <p className="text-sm font-extrabold text-neutral-black">فهرست مطالب</p>
                </div>

                <nav aria-label="فهرست مطالب" className="max-h-[min(60vh,28rem)] overflow-y-auto px-2 py-2">
                    <ul className="space-y-0.5 text-sm">
                        {toc.map((item) => {
                            const active = activeId === item.id;
                            return (
                                <li key={item.id} className={indentClass(item.level)}>
                                    <a
                                        href={`#${item.id}`}
                                        onClick={(event) => onClick(event, item.id)}
                                        className={cn(
                                            'relative block rounded-xl px-3 py-2.5 font-medium leading-snug transition',
                                            active
                                                ? 'bg-primary/10 font-bold text-primary before:absolute before:inset-y-2 before:end-0 before:w-1 before:rounded-full before:bg-primary'
                                                : 'text-neutral-black/70 hover:bg-surface-muted hover:text-primary',
                                        )}
                                    >
                                        {item.title}
                                    </a>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                <div className="flex items-center justify-between gap-2 border-t border-secondary/50 px-4 py-3">
                    <span className="text-xs font-bold text-neutral-black/45">اشتراک‌گذاری</span>
                    <div className="flex items-center gap-1.5">
                        <button
                            type="button"
                            onClick={copyLink}
                            className="inline-flex size-9 items-center justify-center rounded-full border border-secondary/60 text-neutral-black/60 transition hover:border-primary/40 hover:text-primary"
                            aria-label="کپی نشانی مطلب"
                            title="کپی لینک"
                        >
                            {copied ? <Check className="size-4 text-primary" /> : <Link2 className="size-4" />}
                        </button>
                        <a
                            href={`https://t.me/share/url?url=${encodeURIComponent(shareUrl)}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex size-9 items-center justify-center rounded-full border border-secondary/60 text-neutral-black/60 transition hover:border-primary/40 hover:text-primary"
                            aria-label="اشتراک در تلگرام"
                            title="تلگرام"
                        >
                            <TelegramIcon size={16} />
                        </a>
                        <a
                            href={`https://wa.me/?text=${encodeURIComponent(shareUrl)}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex size-9 items-center justify-center rounded-full border border-secondary/60 text-neutral-black/60 transition hover:border-primary/40 hover:text-primary"
                            aria-label="اشتراک در واتساپ"
                            title="واتساپ"
                        >
                            <WhatsappIcon size={16} />
                        </a>
                    </div>
                </div>
            </div>
        </aside>
    );
}
