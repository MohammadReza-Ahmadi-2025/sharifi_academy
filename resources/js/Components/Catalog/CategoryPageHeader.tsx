import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { Link } from '@inertiajs/react';
import { ChevronLeft, Layers } from 'lucide-react';

type Props = {
    title: string;
    imageUrl: string | null;
    total: number;
    itemsLabel: string;
    description: string | null;
};

export function CategoryPageHeader({ title, imageUrl, total, itemsLabel, description }: Props) {
    const hasDescription = Boolean(description?.trim());

    return (
        <header className="mb-8 border-b border-secondary/70 pb-8 lg:mb-10 lg:pb-10">
            <nav className="mb-5 flex flex-wrap items-center gap-2 text-sm text-neutral-black/55">
                <Link href="/" className="inline-flex items-center gap-1 transition hover:text-primary">
                    <ChevronLeft className="size-4" />
                    خانه
                </Link>
                <span className="text-neutral-black/30">/</span>
                <span className="font-medium text-neutral-black/75">{title}</span>
            </nav>

            <div className="flex flex-col gap-6 lg:flex-row lg:items-stretch lg:gap-8">
                <div className="flex min-w-0 flex-1 flex-col justify-center">
                    <div className="mb-3 inline-flex w-fit items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                        <Layers className="size-3.5" />
                        {total.toLocaleString('fa-IR')} {itemsLabel}
                    </div>
                    <h1 className="text-3xl font-extrabold leading-tight text-neutral-black sm:text-4xl lg:text-[2.5rem]">
                        {title}
                    </h1>
                    {hasDescription && (
                        <details className="group mt-5 max-w-3xl rounded-2xl border border-secondary/80 bg-surface-muted/40 open:bg-neutral-white open:shadow-sm">
                            <summary className="cursor-pointer list-none px-4 py-3 text-sm font-bold text-primary marker:content-none [&::-webkit-details-marker]:hidden">
                                <span className="group-open:hidden">مشاهده توضیحات دسته</span>
                                <span className="hidden group-open:inline">بستن توضیحات</span>
                            </summary>
                            <div className="border-t border-secondary/60 px-4 py-4 text-neutral-black/80">
                                <SiteRichHtml html={description} />
                            </div>
                        </details>
                    )}
                </div>

                <div className="relative aspect-[16/10] w-full shrink-0 overflow-hidden rounded-2xl bg-primary/10 lg:aspect-auto lg:h-52 lg:w-80 xl:h-60 xl:w-96">
                    {imageUrl ? (
                        <img
                            src={imageUrl}
                            alt={title}
                            className="size-full object-cover"
                            fetchPriority="high"
                            decoding="async"
                        />
                    ) : (
                        <div className="flex size-full items-center justify-center bg-gradient-to-br from-primary/20 via-primary/5 to-transparent">
                            <span className="text-5xl font-black text-primary/25">{title.charAt(0)}</span>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
