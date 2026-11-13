import { BlogArticleToc } from '@/Components/Blog/BlogArticleToc';
import { normalizeRichHtml } from '@/Components/Site/SiteRichHtml';
import { processBlogRichHtml } from '@/lib/rich-html-post-process';
import { cn } from '@/lib/utils';
import { useCallback, useEffect, useMemo } from 'react';

type Props = {
    html: string;
    className?: string;
};

const HIGHLIGHT_MS = 2200;

function highlightHeading(el: HTMLElement): void {
    el.classList.remove('site-rich-html__heading--highlight');
    void el.offsetWidth;
    el.classList.add('site-rich-html__heading--highlight');
    window.setTimeout(() => {
        el.classList.remove('site-rich-html__heading--highlight');
    }, HIGHLIGHT_MS);
}

function scrollToHeadingId(id: string): void {
    const el = document.getElementById(id);
    if (!el) {
        return;
    }

    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    highlightHeading(el);
    window.history.replaceState(null, '', `#${id}`);
}

export function BlogPostContent({ html, className }: Props) {
    const normalized = useMemo(() => normalizeRichHtml(html), [html]);
    const { html: processed, toc } = useMemo(() => processBlogRichHtml(normalized), [normalized]);

    const onNavigate = useCallback((id: string) => {
        scrollToHeadingId(id);
    }, []);

    useEffect(() => {
        if (processed === '') {
            return;
        }

        const hash = window.location.hash.replace(/^#/, '');
        if (hash === '') {
            return;
        }

        const frame = window.requestAnimationFrame(() => {
            const el = document.getElementById(hash);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                highlightHeading(el);
            }
        });

        return () => window.cancelAnimationFrame(frame);
    }, [processed]);

    if (processed === '') {
        return null;
    }

    return (
        <div className="blog-post-content min-w-0 max-w-full">
            {toc.length > 0 ? (
                <div className="mb-6 lg:hidden">
                    <BlogArticleToc toc={toc} onNavigate={onNavigate} />
                </div>
            ) : null}

            <div
                className={cn(
                    toc.length > 0 &&
                        'lg:grid lg:grid-cols-[minmax(0,1fr)_16.5rem] lg:items-start lg:gap-8 xl:grid-cols-[minmax(0,1fr)_17.5rem] xl:gap-10',
                )}
            >
                <div
                    className={cn('site-rich-html min-w-0', className)}
                    dir="rtl"
                    dangerouslySetInnerHTML={{ __html: processed }}
                />

                {toc.length > 0 ? (
                    <div className="hidden lg:block">
                        <BlogArticleToc toc={toc} onNavigate={onNavigate} />
                    </div>
                ) : null}
            </div>
        </div>
    );
}

export function estimateReadingMinutes(html: string): number {
    const text = normalizeRichHtml(html)
        .replace(/<[^>]+>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
    const words = text ? text.split(' ').length : 0;
    return Math.max(1, Math.ceil(words / 180));
}
