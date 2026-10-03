import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import {
    Briefcase,
    CircleHelp,
    GraduationCap,
    Newspaper,
    Package,
    type LucideIcon,
} from 'lucide-react';

export type CategoryTypeOption = {
    value: string;
    label: string;
    icon: string;
};

const iconMap: Record<string, LucideIcon> = {
    package: Package,
    newspaper: Newspaper,
    'graduation-cap': GraduationCap,
    'help-circle': CircleHelp,
    briefcase: Briefcase,
};

type Props = {
    options: CategoryTypeOption[];
    activeType: string;
    basePath: string;
    query?: Record<string, string | number | undefined>;
};

export function CategoryTypeFilter({ options, activeType, basePath, query = {} }: Props) {
    if (options.length <= 1) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-2">
            {options.map((option) => {
                const Icon = iconMap[option.icon] ?? Package;
                const isActive = option.value === activeType;

                return (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() =>
                            router.get(
                                basePath,
                                { ...query, type: option.value, page: undefined },
                                { preserveState: true },
                            )
                        }
                        className={cn(
                            'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition-colors',
                            isActive
                                ? 'border-slate-900 bg-slate-900 text-white dark:border-slate-100 dark:bg-slate-100 dark:text-slate-900'
                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300',
                        )}
                    >
                        <Icon className="size-4" />
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}
