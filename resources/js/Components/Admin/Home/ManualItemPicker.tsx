import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { cn } from '@/lib/utils';
import { useEffect, useRef, useState } from 'react';

export type ManualItem = { id: number; title: string; slug?: string | null };

const MIN_QUERY_LENGTH = 3;

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
    const [fetchError, setFetchError] = useState<string | null>(null);
    const [panelOpen, setPanelOpen] = useState(false);
    const searchGeneration = useRef(0);
    const rootRef = useRef<HTMLDivElement>(null);

    const trimmedQuery = query.trim();
    const canSearch = trimmedQuery.length >= MIN_QUERY_LENGTH;

    useEffect(() => {
        if (!canSearch) {
            setResults([]);
            setFetchError(null);
            setLoading(false);
            setPanelOpen(false);

            return;
        }

        setPanelOpen(true);
        const generation = ++searchGeneration.current;

        const timer = window.setTimeout(async () => {
            setLoading(true);
            setFetchError(null);
            try {
                const params = new URLSearchParams({ type: contentType, q: trimmedQuery });
                const res = await fetch(`/admin/home-sections/search-items?${params}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (generation !== searchGeneration.current) {
                    return;
                }
                if (!res.ok) {
                    setResults([]);
                    setFetchError(
                        res.status === 403
                            ? 'دسترسی جستجو ندارید.'
                            : 'جستجو ناموفق بود؛ صفحه را رفرش کنید و دوباره تلاش کنید.',
                    );

                    return;
                }

                const payload = (await res.json()) as ManualItem[];
                if (generation !== searchGeneration.current) {
                    return;
                }

                setResults(Array.isArray(payload) ? payload : []);
            } catch {
                if (generation !== searchGeneration.current) {
                    return;
                }
                setResults([]);
                setFetchError('خطا در ارتباط با سرور.');
            } finally {
                if (generation === searchGeneration.current) {
                    setLoading(false);
                }
            }
        }, 300);

        return () => {
            window.clearTimeout(timer);
        };
    }, [canSearch, contentType, trimmedQuery]);

    const selectedIds = new Set(items.map((row) => row.id));
    const visibleResults = results.filter((row) => !selectedIds.has(row.id));

    useEffect(() => {
        const onPointerDown = (event: MouseEvent) => {
            if (!rootRef.current?.contains(event.target as Node)) {
                setPanelOpen(false);
            }
        };

        document.addEventListener('mousedown', onPointerDown);

        return () => document.removeEventListener('mousedown', onPointerDown);
    }, []);

    const addItem = (item: ManualItem) => {
        if (items.some((row) => row.id === item.id)) {
            return;
        }
        onChange([...items, item]);
        setQuery('');
        setResults([]);
        setPanelOpen(false);
    };

    const removeItem = (id: number) => {
        onChange(items.filter((row) => row.id !== id));
    };

    const showSuggestPanel = panelOpen && canSearch;

    return (
        <FormField
            label="انتخاب دستی آیتم‌ها"
            error={error}
            hint={`حداقل ${MIN_QUERY_LENGTH} حرف تایپ کنید؛ پیشنهادها فقط بعد از جستجو نمایش داده می‌شوند.`}
            className="md:col-span-2"
        >
            <div ref={rootRef} className="relative">
                {showSuggestPanel && (
                    <div
                        className={cn(
                            'absolute inset-x-0 bottom-full z-50 mb-2 overflow-hidden rounded-lg border border-slate-200 bg-white text-sm shadow-lg dark:border-slate-700 dark:bg-slate-950',
                        )}
                        role="listbox"
                    >
                        {loading && (
                            <p className="px-3 py-2 text-xs text-slate-500">در حال جستجو…</p>
                        )}
                        {fetchError && <p className="px-3 py-2 text-xs text-red-600">{fetchError}</p>}
                        {!loading && !fetchError && visibleResults.length === 0 && (
                            <p className="px-3 py-2 text-xs text-slate-500">نتیجه‌ای پیدا نشد.</p>
                        )}
                        {!loading && visibleResults.length > 0 && (
                            <ul className="max-h-48 overflow-auto">
                                {visibleResults.map((row) => (
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
                    </div>
                )}

                <AdminInput
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    onFocus={() => {
                        if (canSearch) {
                            setPanelOpen(true);
                        }
                    }}
                    placeholder={`حداقل ${MIN_QUERY_LENGTH} حرف از عنوان را جستجو کنید…`}
                    disabled={disabled}
                    autoComplete="off"
                />
            </div>

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
                            <AdminButton
                                type="button"
                                variant="ghost"
                                size="sm"
                                onClick={() => removeItem(row.id)}
                                disabled={disabled}
                            >
                                حذف
                            </AdminButton>
                        </li>
                    ))}
                </ul>
            )}
        </FormField>
    );
}
