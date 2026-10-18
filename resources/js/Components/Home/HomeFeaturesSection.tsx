import { resolveHomeSectionIcon } from '@/Components/Home/home-section-icon';
import { HomeSectionShell } from '@/Components/Home/HomeSectionShell';
import { HomeSectionTabs } from '@/Components/Home/HomeSectionTabs';
import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';
import { useState } from 'react';

type FeatureRow = {
    label: string;
    text: string;
    image_url?: string | null;
    image_alt?: string | null;
    image_title?: string | null;
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
            const image_alt =
                typeof (row as FeatureRow).image_alt === 'string' ? (row as FeatureRow).image_alt : '';
            const image_title =
                typeof (row as FeatureRow).image_title === 'string' ? (row as FeatureRow).image_title : '';
            if (!image_url) {
                return null;
            }

            return {
                label,
                text,
                image_url: image_url || null,
                image_alt: image_alt || null,
                image_title: image_title || null,
            };
        })
        .filter((row): row is FeatureRow => row !== null)
        .slice(0, 3);
}

function FeatureGrid({ features }: { features: FeatureRow[] }) {
    if (features.length === 0) {
        return <p className="text-center text-sm text-neutral-black/55 md:py-6">محتوایی برای این تب تعریف نشده است.</p>;
    }

    return (
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-3 sm:gap-5">
            {features.map((feature, index) => (
                <article
                    key={`${feature.image_url}-${index}`}
                    className="min-w-0 overflow-hidden rounded-2xl border border-secondary/50 bg-neutral-white p-2 shadow-[0_2px_12px_-4px_rgba(0,72,158,0.08)]"
                >
                    <div className="aspect-[4/3] overflow-hidden rounded-xl bg-secondary/15">
                        <img
                            src={feature.image_url!}
                            alt={feature.image_alt || feature.label || ''}
                            title={feature.image_title || undefined}
                            className="size-full object-cover"
                        />
                    </div>
                    {(feature.label?.trim() || feature.text?.trim()) && (
                        <div className="px-2 pb-2 pt-3.5">
                            {feature.label?.trim() ? (
                                <h3 className="font-bold text-neutral-black">{feature.label}</h3>
                            ) : null}
                            {feature.text?.trim() ? (
                                <p className="mt-1.5 text-sm leading-relaxed text-neutral-black/60">{feature.text}</p>
                            ) : null}
                        </div>
                    )}
                </article>
            ))}
        </div>
    );
}

export function HomeFeaturesSection({ section, sectionId }: Props) {
    const SectionIcon = resolveHomeSectionIcon(section);
    const tabs = section.tabs ?? [];
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const features = activeTab ? featuresFromTab(activeTab) : [];

    return (
        <HomeSectionShell id={sectionId} title={section.title} icon={SectionIcon}>
            <HomeSectionTabs tabs={tabs} activeId={activeTabId} onChange={setActiveTabId} />
            <FeatureGrid features={features} />
        </HomeSectionShell>
    );
}
