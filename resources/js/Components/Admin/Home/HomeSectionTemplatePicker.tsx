import {
    HOME_SECTION_TEMPLATES,
    type HomeSectionTemplateId,
} from '@/Components/Admin/Home/home-section-templates';
import { cn } from '@/lib/utils';

type Props = {
    value: HomeSectionTemplateId;
    disabled?: boolean;
    onChange: (id: HomeSectionTemplateId) => void;
};

function TemplateWireframe({ id }: { id: HomeSectionTemplateId }) {
    if (id === 'hero_split') {
        return (
            <div className="flex h-20 gap-1.5 rounded-md border border-slate-200 bg-slate-50 p-1.5 dark:border-slate-600 dark:bg-slate-900">
                <div className="flex flex-1 flex-col gap-1">
                    <div className="h-1.5 w-8 rounded bg-primary/70" />
                    <div className="h-2 w-full rounded bg-slate-300 dark:bg-slate-600" />
                    <div className="h-1.5 w-3/4 rounded bg-slate-200 dark:bg-slate-700" />
                    <div className="mt-auto flex gap-0.5">
                        <div className="h-2 w-6 rounded bg-primary/40" />
                        <div className="h-2 w-6 rounded bg-slate-200 dark:bg-slate-700" />
                    </div>
                </div>
                <div className="w-[42%] rounded bg-primary/15 ring-1 ring-primary/25" />
            </div>
        );
    }

    if (id === 'features') {
        return (
            <div className="grid h-20 grid-cols-3 gap-1 rounded-md border border-slate-200 bg-slate-50 p-1.5 dark:border-slate-600 dark:bg-slate-900">
                {[0, 1, 2].map((i) => (
                    <div key={i} className="overflow-hidden rounded bg-white shadow-sm dark:bg-slate-800">
                        <div className="h-2/3 bg-primary/20" />
                        <div className="h-1/3 p-0.5">
                            <div className="h-1 w-2/3 rounded bg-primary/50" />
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    if (id === 'testimonials') {
        return (
            <div className="grid h-20 grid-cols-2 gap-1 rounded-md border border-slate-200 bg-slate-50 p-1.5 dark:border-slate-600 dark:bg-slate-900">
                {[0, 1, 2, 3].map((i) => (
                    <div key={i} className="rounded bg-white p-1 shadow-sm dark:bg-slate-800">
                        <div className="h-1 w-2/3 rounded bg-primary/40" />
                        <div className="mt-1 h-2 rounded bg-slate-200 dark:bg-slate-700" />
                    </div>
                ))}
            </div>
        );
    }

    if (id === 'instructors') {
        return (
            <div className="flex h-20 gap-1 rounded-md border border-slate-200 bg-slate-50 p-1.5 dark:border-slate-600 dark:bg-slate-900">
                {[0, 1, 2, 3].map((i) => (
                    <div key={i} className="flex min-w-0 flex-1 flex-col items-center rounded bg-white p-1 shadow-sm dark:bg-slate-800">
                        <div className="size-6 rounded-full bg-primary/25" />
                        <div className="mt-1 h-1 w-full rounded bg-slate-200 dark:bg-slate-700" />
                    </div>
                ))}
            </div>
        );
    }

    if (id === 'banners') {
        return (
            <div className="flex h-20 items-center justify-center rounded-md border border-slate-200 bg-primary/10 dark:border-slate-600">
                <div className="h-12 w-[88%] rounded bg-primary/25 ring-1 ring-primary/30" />
            </div>
        );
    }

    return (
        <div className="flex h-20 gap-0.5 rounded-md border border-slate-200 bg-slate-50 p-1.5 dark:border-slate-600 dark:bg-slate-900">
            {[0, 1, 2, 3, 4].map((i) => (
                <div key={i} className="min-w-0 flex-1 overflow-hidden rounded bg-white shadow-sm dark:bg-slate-800">
                    <div className="h-2/3 bg-primary/15" />
                    <div className="h-1/3 p-0.5">
                        <div className="h-1 w-full rounded bg-slate-200 dark:bg-slate-600" />
                    </div>
                </div>
            ))}
        </div>
    );
}

export function HomeSectionTemplatePicker({ value, disabled, onChange }: Props) {
    return (
        <div className="grid gap-3 sm:grid-cols-2">
            {HOME_SECTION_TEMPLATES.map((template) => {
                const selected = value === template.id;

                return (
                    <button
                        key={template.id}
                        type="button"
                        disabled={disabled}
                        onClick={() => onChange(template.id)}
                        className={cn(
                            'rounded-xl border p-3 text-right transition',
                            selected
                                ? 'border-slate-900 bg-slate-50 ring-2 ring-slate-900/20 dark:border-slate-200 dark:bg-slate-900 dark:ring-slate-200/20'
                                : 'border-slate-200 bg-white hover:border-slate-400 dark:border-slate-700 dark:bg-slate-950',
                        )}
                    >
                        <TemplateWireframe id={template.id} />
                        <p className="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-100">{template.label}</p>
                        <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{template.description}</p>
                    </button>
                );
            })}
        </div>
    );
}
