import { cn } from '@/lib/utils';

export type RoleOption = {
    name: string;
    label: string;
};

type Props = {
    roleOptions: RoleOption[];
    selected: string[];
    onChange: (roles: string[]) => void;
    error?: string;
    disabled?: boolean;
};

export function UserRolesPicker({ roleOptions, selected, onChange, error, disabled }: Props) {
    const toggle = (name: string) => {
        if (disabled) {
            return;
        }

        if (selected.includes(name)) {
            onChange(selected.filter((role) => role !== name));
        } else {
            onChange([...selected, name]);
        }
    };

    return (
        <div className="space-y-2">
            <p className="text-sm font-medium text-slate-700 dark:text-slate-300">نقش‌ها</p>
            <div className="flex flex-wrap gap-2">
                {roleOptions.map((role) => {
                    const isSelected = selected.includes(role.name);

                    return (
                        <button
                            key={role.name}
                            type="button"
                            disabled={disabled}
                            onClick={() => toggle(role.name)}
                            className={cn(
                                'rounded-lg border px-3 py-1.5 text-sm transition-colors',
                                isSelected
                                    ? 'border-slate-900 bg-slate-900 text-white dark:border-slate-100 dark:bg-slate-100 dark:text-slate-900'
                                    : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300',
                                disabled && 'cursor-not-allowed opacity-60',
                            )}
                        >
                            {role.label}
                        </button>
                    );
                })}
            </div>
            {error ? <p className="text-xs text-rose-500 dark:text-rose-400">{error}</p> : null}
            <p className="text-xs text-slate-500 dark:text-slate-400">حداقل یک نقش انتخاب کنید.</p>
        </div>
    );
}
