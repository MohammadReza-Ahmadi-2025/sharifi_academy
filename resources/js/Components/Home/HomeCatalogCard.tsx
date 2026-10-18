import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import type { HomeCatalogItem } from '@/Components/Home/types';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

type Props = {
    item: HomeCatalogItem;
    hrefPrefix: string;
    showPrice: boolean;
    contentType?: string;
    className?: string;
};

export function HomeCatalogCard({ item, hrefPrefix, showPrice, contentType, className }: Props) {
    const href = item.slug ? `${hrefPrefix}${item.slug}` : hrefPrefix;
    const descriptionIsHtml = item.description_format === 'html' || contentType === 'post';
    const hasDescription = Boolean(item.description?.trim());

    const shellClass = cn(
        'group flex h-full flex-col overflow-hidden rounded-2xl border border-secondary/50 bg-neutral-white p-2 shadow-[0_2px_12px_-4px_rgba(0,72,158,0.08)] transition duration-300',
        !descriptionIsHtml && 'hover:-translate-y-0.5 hover:border-primary/20 hover:shadow-[0_12px_28px_-10px_rgba(0,72,158,0.2)]',
        className,
    );

    const mediaBlock = (
        <div className="relative aspect-[4/3] overflow-hidden rounded-xl bg-secondary/20">
            <img
                src={item.image_url ?? '/banner.png'}
                alt={item.title}
                className="size-full object-cover transition duration-500 group-hover:scale-[1.04]"
            />
            <div
                className="pointer-events-none absolute inset-0 bg-gradient-to-t from-neutral-black/25 via-transparent to-transparent opacity-70 transition group-hover:opacity-90"
                aria-hidden
            />
            <span
                className="pointer-events-none absolute bottom-2.5 start-2.5 inline-flex items-center gap-1 rounded-full bg-neutral-white/95 px-2.5 py-1 text-[0.65rem] font-semibold text-primary opacity-0 shadow-sm backdrop-blur-sm transition duration-300 group-hover:opacity-100"
            >
                مشاهده
                <ArrowLeft className="size-3" aria-hidden />
            </span>
        </div>
    );

    if (descriptionIsHtml && hasDescription) {
        return (
            <article className={cn(shellClass, 'hover:border-primary/20 hover:shadow-[0_12px_28px_-10px_rgba(0,72,158,0.16)]')}>
                <Link href={href} className="block">
                    {mediaBlock}
                    <h3 className="px-2 pt-3.5 text-[0.95rem] font-bold leading-snug text-neutral-black transition group-hover:text-primary md:text-base">
                        {item.title}
                    </h3>
                </Link>
                <div className="px-2 pb-2 pt-1.5">
                    <SiteRichHtml
                        html={item.description ?? ''}
                        className="site-rich-html--card-preview text-neutral-black/70"
                    />
                </div>
                {showPrice && typeof item.price === 'number' ? (
                    <p className="mx-2 mb-2 mt-auto flex items-baseline gap-1 border-t border-dashed border-secondary/50 pt-3 text-sm font-bold text-primary">
                        {item.price.toLocaleString('fa-IR')}
                        <span className="text-xs font-medium text-neutral-black/45">ریال</span>
                    </p>
                ) : null}
            </article>
        );
    }

    return (
        <Link href={href} className={shellClass}>
            {mediaBlock}
            <div className="flex flex-1 flex-col px-2 pb-2 pt-3.5">
                <h3 className="line-clamp-2 text-[0.95rem] font-bold leading-snug text-neutral-black transition group-hover:text-primary md:text-base">
                    {item.title}
                </h3>
                {hasDescription ? (
                    <p className="mt-1.5 line-clamp-2 text-xs leading-relaxed text-neutral-black/55 md:text-sm">
                        {item.description}
                    </p>
                ) : null}
                {showPrice && typeof item.price === 'number' ? (
                    <p className="mt-auto flex items-baseline gap-1 border-t border-dashed border-secondary/50 pt-3 text-sm font-bold text-primary">
                        {item.price.toLocaleString('fa-IR')}
                        <span className="text-xs font-medium text-neutral-black/45">ریال</span>
                    </p>
                ) : null}
            </div>
        </Link>
    );
}
