import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { useEffect, useState } from 'react';

export type ManualItem = { id: number; title: string; slug?: string | null };

type Props = {
    contentType: string;
    items: ManualItem[];
    onChange: (items: ManualItem[]) => void;
    disabled?: boolean;
    error?: string;
};

export function ManualItemPicker({ contentType, items, onChange, disabled, error }: Props) {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState<ManualItem[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (query.trim().length < 2) {
            setResults([]);
            return;
        }

        const timer = window.setTimeout(async () => {
            setLoading(true);
            try {
                const params = new URLSearchParams({ type: contentType, q: query.trim() });
                const res = await fetch(`/admin/home-sections/search-items?${params}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                });
                if (res.ok) {
                    setResults((await res.json()) as ManualItem[]);
                }
            } finally {
                setLoading(false);
            }
        }, 300);

        return () => window.clearTimeout(timer);
    }, [contentType, query]);

    const addItem = (item: ManualItem) => {
        if (items.some((row) => row.id === item.id)) {
            return;
        }
        onChange([...items, item]);
        setQuery('');
        setResults([]);
    };

    const removeItem = (id: number) => {
        onChange(items.filter((row) => row.id !== id));
    };

    return (
        <FormField label="انتخاب دستی آیتم‌ها" error={error} className="md:col-span-2">
            <AdminInput
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="حداقل ۲ حرف از عنوان را جستجو کنید…"
                disabled={disabled}
            />
            {loading && <p className="mt-2 text-xs text-slate-500">در حال جستجو…</p>}
            {results.length > 0 && (
                <ul className="mt-2 max-h-40 overflow-auto rounded-lg border border-slate-200 bg-white text-sm dark:border-slate-700 dark:bg-slate-950">
                    {results.map((row) => (
                        <li key={row.id}>
                            <button
                                type="button"
                                className="flex w-full items-center justify-between px-3 py-2 text-right hover:bg-slate-50 dark:hover:bg-slate-900"
                                onClick={() => addItem(row)}
                                disabled={disabled}
                            >
                                <span>{row.title}</span>
                                <span className="text-xs text-slate-400">#{row.id}</span>
                            </button>
                        </li>
                    ))}
                </ul>
            )}
            {items.length > 0 && (
                <ul className="mt-3 space-y-2">
                    {items.map((row, index) => (
                        <li
                            key={row.id}
                            className="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-slate-700"
                        >
                            <span>
                                {index + 1}. {row.title}
                            </span>
                            <AdminButton type="button" variant="ghost" size="sm" onClick={() => removeItem(row.id)} disabled={disabled}>
                                حذف
                            </AdminButton>
                        </li>
                    ))}
                </ul>
            )}
        </FormField>
    );
}
