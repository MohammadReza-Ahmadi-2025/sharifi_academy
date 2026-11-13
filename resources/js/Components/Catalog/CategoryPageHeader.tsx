import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';

type Props = {
    title: string;
    imageUrl: string | null;
    total: number;
    itemsLabel: string;
    description: string | null;
};

export function CategoryPageHeader({ title, imageUrl, total, itemsLabel, description }: Props) {
    const hasDescription = Boolean(description?.trim());
    const heroSrc = imageUrl?.trim() || '/banner.png';

    return (
        <header className="relative isolate overflow-hidden border-b border-secondary/40">
            <div className="absolute inset-0">
                <img
                    src={heroSrc}
                    alt=""
                    className="size-full object-cover scale-105"
                    fetchPriority="high"
                    decoding="async"
                />
                <div
                    className="absolute inset-0 bg-gradient-to-l from-neutral-black/80 via-neutral-black/55 to-primary/45"
                    aria-hidden
                />
                <div
                    className="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgb(var(--color-primary-rgb)/0.35),transparent_55%)]"
                    aria-hidden
                />
            </div>

            <div className="relative mx-auto max-w-[88rem] px-4 pb-12 pt-8 sm:px-6 sm:pb-14 sm:pt-10 lg:px-8 lg:pb-16 lg:pt-12">
                <nav className="mb-8 flex flex-wrap items-center gap-2 text-sm text-white/70">
                    <Link href="/" className="inline-flex items-center gap-1 transition hover:text-white">
                        <ChevronLeft className="size-4" />
                        خانه
                    </Link>
                    <span className="text-white/35">/</span>
                    <span className="font-medium text-white/90">{title}</span>
                </nav>

                <div className="max-w-3xl animate-[category-hero-in_0.7s_cubic-bezier(0.22,1,0.36,1)_both]">
                    <p className="mb-3 text-sm font-semibold tracking-wide text-white/75">
                        {total.toLocaleString('fa-IR')} {itemsLabel}
                    </p>
                    <h1 className="text-3xl font-black leading-[1.25] text-white drop-shadow-sm sm:text-4xl lg:text-5xl">
                        {title}
                    </h1>
                </div>

                {hasDescription ? (
                    <details className="group mt-8 max-w-2xl rounded-2xl border border-white/15 bg-white/10 backdrop-blur-md open:bg-white/95 open:backdrop-blur-none">
                        <summary className="cursor-pointer list-none px-4 py-3 text-sm font-bold text-white marker:content-none group-open:text-primary [&::-webkit-details-marker]:hidden">
                            <span className="group-open:hidden">درباره این دسته</span>
                            <span className="hidden group-open:inline">بستن توضیحات</span>
                        </summary>
                        <div className="border-t border-secondary/50 px-4 py-4 text-neutral-black/80">
                            <SiteRichHtml html={description} />
                        </div>
                    </details>
                ) : null}
            </div>
        </header>
    );
}
