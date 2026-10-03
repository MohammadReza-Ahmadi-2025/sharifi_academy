import type { HomeCatalogItem, HomeSection, HomeSectionTab } from '@/Components/Home/types';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    section: HomeSection;
    tabs: HomeSectionTab[];
    hrefPrefix: string;
    empty: string;
    showPrice?: boolean;
    sectionId?: string;
};

export function HomeTabbedCatalogSection({
    section,
    tabs,
    hrefPrefix,
    empty,
    showPrice = true,
    sectionId,
}: Props) {
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const items = activeTab?.items ?? [];
    const viewAll = activeTab?.view_all_url;

    return (
        <section id={sectionId} className="mx-auto max-w-6xl scroll-mt-24 px-4 py-10">
            <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 className="text-2xl font-extrabold text-neutral-black">{section.title}</h2>
                </div>
                {viewAll && (
                    <Link href={viewAll} className="text-sm font-bold text-primary hover:underline">
                        مشاهده همه
                    </Link>
                )}
            </div>

            {tabs.length > 1 && (
                <div className="mb-6 flex flex-wrap gap-2">
                    {tabs.map((tab) => (
                        <button
                            key={tab.id}
                            type="button"
                            onClick={() => setActiveTabId(tab.id)}
                            className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${
                                tab.id === activeTabId
                                    ? 'bg-primary text-secondary'
                                    : 'border border-secondary bg-neutral-white text-neutral-black/80'
                            }`}
                        >
                            {tab.title === 'default' ? 'همه' : tab.title}
                        </button>
                    ))}
                </div>
            )}

            {items.length === 0 ? (
                <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-10 text-center text-sm text-neutral-black/70">
                    {empty}
                </p>
            ) : (
                <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    {items.map((item) => (
                        <CatalogCard key={item.id} item={item} hrefPrefix={hrefPrefix} showPrice={showPrice} />
                    ))}
                </div>
            )}
        </section>
    );
}

function CatalogCard({
    item,
    hrefPrefix,
    showPrice,
}: {
    item: HomeCatalogItem;
    hrefPrefix: string;
    showPrice: boolean;
}) {
    const href = item.slug ? `${hrefPrefix}${item.slug}` : hrefPrefix;

    return (
        <Link
            href={href}
            className="group overflow-hidden rounded-2xl border border-secondary bg-neutral-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
        >
            <div className="aspect-4/3 overflow-hidden bg-primary">
                <img
                    src={item.image_url ?? '/banner.png'}
                    alt={item.title}
                    className="size-full object-cover transition duration-300 group-hover:scale-105"
                />
            </div>
            <div className="p-4">
                <h3 className="font-bold text-neutral-black">{item.title}</h3>
                {item.description && (
                    <p className="mt-2 line-clamp-2 text-sm text-neutral-black/70">{item.description}</p>
                )}
                {showPrice && typeof item.price === 'number' && (
                    <p className="mt-4 text-sm font-extrabold text-primary">{item.price.toLocaleString('fa-IR')} ریال</p>
                )}
            </div>
        </Link>
    );
}
