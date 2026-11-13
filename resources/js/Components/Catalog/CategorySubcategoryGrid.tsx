import { Link } from '@inertiajs/react';

export type CategorySubcategoryCard = {
    id: number;
    title: string;
    slug: string;
    items_count: number;
    image_url: string | null;
};

type Props = {
    subcategories: CategorySubcategoryCard[];
    itemsLabel: string;
};

export function CategorySubcategoryGrid({ subcategories, itemsLabel }: Props) {
    if (subcategories.length === 0) {
        return null;
    }

    return (
        <section className="border-b border-secondary/50 bg-neutral-white" aria-label="زیردسته‌ها">
            <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                <div className="-mx-4 flex gap-4 overflow-x-auto px-4 pb-2 scroll-smooth snap-x snap-mandatory scrollbar-thin sm:mx-0 sm:px-0">
                    {subcategories.map((sub) => (
                        <Link
                            key={sub.id}
                            href={`/categories/${sub.slug}`}
                            className="group w-[9.5rem] shrink-0 snap-start sm:w-40"
                        >
                            <div className="relative aspect-square overflow-hidden rounded-[1.35rem] bg-secondary/30 shadow-[0_10px_30px_-18px_rgba(0,40,90,0.45)] ring-1 ring-black/[0.04] transition duration-300 group-hover:-translate-y-1 group-hover:shadow-[0_18px_36px_-16px_rgba(0,72,158,0.35)]">
                                <img
                                    src={sub.image_url?.trim() || '/banner.png'}
                                    alt=""
                                    loading="lazy"
                                    className="size-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div
                                    className="absolute inset-0 bg-gradient-to-t from-neutral-black/70 via-transparent to-transparent opacity-90"
                                    aria-hidden
                                />
                                <span className="absolute bottom-2 start-2 end-2 truncate text-center text-[11px] font-bold text-white/95">
                                    {sub.items_count.toLocaleString('fa-IR')} {itemsLabel}
                                </span>
                            </div>
                            <p className="mt-2.5 line-clamp-2 text-center text-sm font-bold leading-snug text-neutral-black/80 transition group-hover:text-primary">
                                {sub.title}
                            </p>
                        </Link>
                    ))}
                </div>
            </div>
        </section>
    );
}
