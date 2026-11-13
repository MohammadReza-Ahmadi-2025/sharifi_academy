import { wrapRichHtmlTables } from '@/lib/rich-html-post-process';
import { cn } from '@/lib/utils';
import { useLayoutEffect, useMemo, useRef } from 'react';

type Props = {
    html: string;
    className?: string;
};

/**
 * If content was saved/escaped as entities (&lt;p&gt;...), unescape once so tags can render.
 */
export function normalizeRichHtml(html: string): string {
    const trimmed = html.trim();
    if (trimmed === '') {
        return '';
    }

    const looksEncoded = /&lt;[a-z]/i.test(trimmed) && !/<[a-z][\s\S]*>/i.test(trimmed);
    if (!looksEncoded) {
        return trimmed;
    }

    if (typeof document === 'undefined') {
        return trimmed
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&#39;/g, "'")
            .replace(/&amp;/g, '&');
    }

    const textarea = document.createElement('textarea');
    textarea.innerHTML = trimmed;

    return textarea.value.trim();
}

/**
 * خروجی CKEditor — تگ‌های HTML رندر می‌شوند (معادل {!! !!} در Blade).
 * محتوا باید سمت سرور با HTML Purifier پاک شده باشد؛ اسکریپت اجازه ندارد.
 */
export function SiteRichHtml({ html, className }: Props) {
    const rootRef = useRef<HTMLDivElement>(null);
    const normalized = useMemo(() => normalizeRichHtml(html), [html]);
    const isBlogLayout = className?.includes('site-rich-html--blog') ?? false;

    useLayoutEffect(() => {
        if (!isBlogLayout) {
            return;
        }

        wrapRichHtmlTables(rootRef.current);
    }, [normalized, isBlogLayout]);

    if (normalized === '') {
        return null;
    }

    return (
        <div
            ref={rootRef}
            className={cn('site-rich-html', isBlogLayout && 'site-rich-html--blog-contained', className)}
            dir="rtl"
            dangerouslySetInnerHTML={{ __html: normalized }}
        />
    );
}
