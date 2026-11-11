import { cn } from '@/lib/utils';

type Props = {
    html: string;
    className?: string;
};

/**
 * If content was saved/escaped as entities (&lt;p&gt;...), unescape once so tags can render.
 */
function normalizeRichHtml(html: string): string {
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
    const normalized = normalizeRichHtml(html);

    if (normalized === '') {
        return null;
    }

    return (
        <div
            className={cn('site-rich-html', className)}
            dir="rtl"
            dangerouslySetInnerHTML={{ __html: normalized }}
        />
    );
}
