import { HomeHeroBackdrop } from '@/Components/Home/HomeHeroBackdrop';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

type Props = {
    id?: string;
    children: ReactNode;
    /** استوری: همان بک‌گراند هیرو، ارتفاع نوار مثل قبل */
    compact?: boolean;
};

/** قاب تمام‌عرض با همان بک‌گراند هیرو (گرید + گرادیان) */
export function HomeHeroSectionShell({ id, children, compact = false }: Props) {
    return (
        <section
            id={id}
            className={cn(
                'relative overflow-hidden bg-neutral-white',
                !compact && 'border-b border-secondary',
            )}
        >
            <HomeHeroBackdrop compact={compact} />
            <div
                className={cn(
                    'relative mx-auto max-w-7xl px-4',
                    compact ? 'py-5 md:py-6' : 'py-12 md:py-16 lg:py-20',
                )}
            >
                {children}
            </div>
        </section>
    );
}
