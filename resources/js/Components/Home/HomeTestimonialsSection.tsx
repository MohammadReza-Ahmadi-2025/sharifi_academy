import { resolveHomeSectionIcon } from '@/Components/Home/home-section-icon';
import { clampTestimonialRating } from '@/Components/Home/HomeTestimonialStars';
import { HomeTestimonialsSwiper } from '@/Components/Home/HomeTestimonialsSwiper';
import { HomeSectionShell } from '@/Components/Home/HomeSectionShell';
import { HomeSectionTabs } from '@/Components/Home/HomeSectionTabs';
import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';
import { useState } from 'react';

export type TestimonialItem = {
    name: string;
    role: string;
    quote: string;
    rating: number;
};

function testimonialsFromTab(tab: HomeSectionTab): TestimonialItem[] {
    const raw = tab.testimonials;
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw
        .map((row) => {
            if (!row || typeof row !== 'object') {
                return null;
            }
            const name = typeof row.name === 'string' ? row.name.trim() : '';
            const quote = typeof row.quote === 'string' ? row.quote.trim() : '';
            const role = typeof row.role === 'string' ? row.role.trim() : '';
            if (!name || !quote) {
                return null;
            }

            return {
                name,
                role,
                quote,
                rating: clampTestimonialRating(row.rating),
            };
        })
        .filter((row): row is TestimonialItem => row !== null);
}

type Props = {
    section: HomeSection;
    sectionId?: string;
};

export function HomeTestimonialsSection({ section, sectionId }: Props) {
    const SectionIcon = resolveHomeSectionIcon(section);
    const tabs = section.tabs ?? [];
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const items = activeTab ? testimonialsFromTab(activeTab) : [];

    return (
        <HomeSectionShell id={sectionId} title={section.title} icon={SectionIcon}>
            {tabs.length > 1 ? (
                <HomeSectionTabs tabs={tabs} activeId={activeTabId} onChange={setActiveTabId} />
            ) : null}
            <HomeTestimonialsSwiper items={items} />
        </HomeSectionShell>
    );
}
