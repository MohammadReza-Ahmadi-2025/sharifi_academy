import { cn } from '@/lib/utils';

type Tab = { id: number; title: string };

type Props = {
    tabs: Tab[];
    activeId: number;
    onChange: (id: number) => void;
    resolveLabel?: (tab: Tab) => string;
    className?: string;
};

export function HomeSectionTabs({ tabs, activeId, onChange, resolveLabel, className }: Props) {
    if (tabs.length <= 1) {
        return null;
    }

    return (
        <div
            className={cn(
                'mb-6 inline-flex max-w-full flex-wrap gap-1.5 rounded-2xl border border-secondary/45 bg-neutral-white/80 p-1.5 shadow-[0_1px_8px_-2px_rgba(0,0,0,0.06)] backdrop-blur-sm md:mb-7',
                className,
            )}
            role="tablist"
        >
            {tabs.map((tab) => {
                const active = tab.id === activeId;
                const label = resolveLabel ? resolveLabel(tab) : tab.title === 'default' ? 'همه' : tab.title;

                return (
                    <button
                        key={tab.id}
                        type="button"
                        role="tab"
                        aria-selected={active}
                        onClick={() => onChange(tab.id)}
                        className={cn(
                            'rounded-xl px-4 py-2 text-sm font-medium transition duration-200',
                            active
                                ? 'bg-gradient-to-b from-primary to-primary-hover text-on-primary shadow-sm shadow-primary/20'
                                : 'text-neutral-black/55 hover:bg-primary/[0.06] hover:text-primary',
                        )}
                    >
                        {label}
                    </button>
                );
            })}
        </div>
    );
}
