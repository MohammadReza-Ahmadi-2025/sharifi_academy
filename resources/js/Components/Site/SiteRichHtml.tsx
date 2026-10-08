import { cn } from '@/lib/utils';

type Props = {
    html: string;
    className?: string;
};

/** خروجی CKEditor / HTML ادمین — روی سایت عمومی */
export function SiteRichHtml({ html, className }: Props) {
    const trimmed = html.trim();

    if (trimmed === '') {
        return null;
    }

    return (
        <div
            className={cn('site-rich-html', className)}
            dir="rtl"
            // محتوا از پنل ادمین می‌آید؛ تگ‌های HTML باید رندر شوند نه به‌صورت متن خام
            dangerouslySetInnerHTML={{ __html: trimmed }}
        />
    );
}
