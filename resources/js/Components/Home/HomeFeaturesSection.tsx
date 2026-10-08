import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';
import { useState } from 'react';

type FeatureRow = {
    label: string;
    text: string;
    image_url?: string | null;
};

type Props = {
    section: HomeSection;
    sectionId?: string;
};

function featuresFromTab(tab: HomeSectionTab): FeatureRow[] {
    const raw = tab.features;
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw
        .map((row) => {
            if (!row || typeof row !== 'object') {
                return null;
            }
            const label = typeof (row as FeatureRow).label === 'string' ? (row as FeatureRow).label : '';
            const text = typeof (row as FeatureRow).text === 'string' ? (row as FeatureRow).text : '';
            const image_url =
                typeof (row as FeatureRow).image_url === 'string' ? (row as FeatureRow).image_url : '';
            if (!label && !image_url) {
                return null;
            }

            return { label, text, image_url: image_url || null };
        })
        .filter((row): row is FeatureRow => row !== null)
        .slice(0, 3);
}

function FeatureGrid({ features }: { features: FeatureRow[] }) {
    if (features.length === 0) {
        return (
            <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-10 text-center text-sm text-neutral-black/70">
                محتوایی برای این تب تعریف نشده است.
            </p>
        );
    }

    return (
        <div className="grid gap-4 md:grid-cols-3">
            {features.map((feature, index) => (
                <div
                    key={`${feature.label}-${index}`}
                    className="overflow-hidden rounded-2xl border border-secondary bg-neutral-white shadow-sm"
                >
                    {feature.image_url ? (
                        <div className="aspect-4/3 overflow-hidden bg-primary/10">
                            <img src={feature.image_url} alt={feature.label || ''} className="size-full object-cover" />
                        </div>
                    ) : null}
                    <div className="p-5">
                        {feature.label && <h3 className="font-bold text-neutral-black">{feature.label}</h3>}
                        {feature.text && <p className="mt-1 text-sm text-neutral-black/70">{feature.text}</p>}
                    </div>
                </div>
            ))}
        </div>
    );
}

export function HomeFeaturesSection({ section, sectionId }: Props) {
    const tabs = section.tabs ?? [];
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const features = activeTab ? featuresFromTab(activeTab) : [];

    return (
        <section id={sectionId} className="mx-auto max-w-6xl scroll-mt-24 px-4 py-12">
            <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                {section.title && <h2 className="text-2xl font-extrabold text-neutral-black">{section.title}</h2>}
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

            <FeatureGrid features={features} />
        </section>
    );
}
