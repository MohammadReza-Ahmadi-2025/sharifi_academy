import type { HomeCatalogItem } from '@/Components/Home/types';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    section: { title: string };
    tabs: { id: number; title: string; items: HomeCatalogItem[] }[];
    sectionId?: string;
};

export function HomeBannerCarousel({ section, tabs, sectionId }: Props) {
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const banners = activeTab?.items ?? [];
    const [slide, setSlide] = useState(0);

    if (!activeTab) {
        return null;
    }

    if (banners.length === 0) {
        return (
            <section id={sectionId} className="mx-auto max-w-6xl scroll-mt-24 px-4 py-8">
                <h2 className="mb-4 text-2xl font-extrabold text-neutral-black">{section.title}</h2>
                <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-10 text-center text-sm text-neutral-black/70">
                    بنری برای نمایش ثبت نشده است.
                </p>
            </section>
        );
    }

    const current = banners[slide % banners.length];

    return (
        <section id={sectionId} className="mx-auto max-w-6xl scroll-mt-24 px-4 py-8">
            {tabs.length > 1 && (
                <div className="mb-4 flex flex-wrap gap-2">
                    {tabs.map((tab) => (
                        <button
                            key={tab.id}
                            type="button"
                            onClick={() => {
                                setActiveTabId(tab.id);
                                setSlide(0);
                            }}
                            className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${
                                tab.id === activeTabId
                                    ? 'bg-primary text-secondary'
                                    : 'border border-secondary bg-neutral-white text-neutral-black/80'
                            }`}
                        >
                            {tab.title === 'default' ? section.title : tab.title}
                        </button>
                    ))}
                </div>
            )}

            <div className="relative overflow-hidden rounded-2xl border border-secondary bg-neutral-white shadow-lg">
                <div className="relative aspect-[2/1] max-h-[420px] w-full bg-primary">
                    {current.image_url ? (
                        <img src={current.image_url} alt={current.title ?? ''} className="size-full object-cover" />
                    ) : (
                        <div className="flex size-full items-center justify-center text-secondary/60">بدون تصویر</div>
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-neutral-black/70 via-neutral-black/20 to-transparent" />
                    <div className="absolute bottom-0 right-0 left-0 p-6 text-neutral-white">
                        {current.title && <h2 className="text-2xl font-extrabold">{current.title}</h2>}
                        {(current.subtitle || current.description) && (
                            <p className="mt-2 max-w-xl text-sm text-neutral-white/90">{current.subtitle ?? current.description}</p>
                        )}
                        {current.button_link && (
                            <Link
                                href={current.button_link}
                                className="mt-4 inline-flex rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-secondary"
                            >
                                {current.button_text ?? 'مشاهده'}
                            </Link>
                        )}
                    </div>
                </div>
                {banners.length > 1 && (
                    <div className="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                        {banners.map((_, i) => (
                            <button
                                key={i}
                                type="button"
                                aria-label={`اسلاید ${i + 1}`}
                                onClick={() => setSlide(i)}
                                className={`size-2 rounded-full ${i === slide % banners.length ? 'bg-primary' : 'bg-neutral-white/70'}`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </section>
    );
}
