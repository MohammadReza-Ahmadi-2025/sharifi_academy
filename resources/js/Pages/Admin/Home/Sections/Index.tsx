import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { cn } from '@/lib/utils';
import { reorderById } from '@/Components/shared/reorder-list';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { GripVertical, Pencil, Plus, Trash2 } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent, useEffect, useState } from 'react';

type Row = {
    id: number;
    title: string;
    status: boolean;
    sort: number;
    content_type: string | null;
    content_type_label: string | null;
    section_template_label?: string | null;
    tabs_count: number;
};

type Props = {
    sections: Row[];
    filters: { search: string };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function HomeSectionsIndex({ sections, filters, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const [ordered, setOrdered] = useState(sections);
    const [dragId, setDragId] = useState<number | null>(null);
    const [dropId, setDropId] = useState<number | null>(null);

    useEffect(() => {
        setOrdered(sections);
    }, [sections]);

    const applyFilters = (overrides: Record<string, string | undefined> = {}) => {
        router.get(
            '/admin/home-sections',
            { search: search || undefined, ...overrides },
            { preserveState: true, replace: true },
        );
    };

    const onFilterSubmit = (e: FormEvent) => {
        e.preventDefault();
        applyFilters();
    };

    const clearFilters = () => {
        setSearch('');
        router.get('/admin/home-sections', {}, { replace: true });
    };

    const persistOrder = (next: Row[]) => {
        setOrdered(next);
        router.put(
            '/admin/home-sections/reorder',
            {
                sections: next.map((row, index) => ({
                    id: row.id,
                    sort: (index + 1) * 10,
                })),
            },
            { preserveScroll: true, preserveState: true },
        );
    };

    const handleDrop = (targetId: number) => {
        if (dragId === null || dragId === targetId) {
            setDragId(null);
            setDropId(null);
            return;
        }

        const reordered = reorderById(
            ordered.map((row) => ({ ...row, id: String(row.id) })),
            String(dragId),
            String(targetId),
        ).map((row) => ({ ...row, id: Number(row.id) }));

        persistOrder(reordered);
        setDragId(null);
        setDropId(null);
    };

    const canDrag = abilities.update && ordered.length > 1 && !filters.search;

    return (
        <AdminLayout>
            <Head title="سکشن‌های صفحه اصلی" />
            <PageHeader
                title="سکشن‌های خانه"
                description="چیدمان داینامیک صفحه اصلی — با دستگیره جابه‌جا کنید"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/home-sections/create">
                            <Plus className="size-4" />
                            سکشن جدید
                        </AdminButton>
                    ) : null
                }
            />

            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={onFilterSubmit}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان سکشن…"
            />

            {canDrag && (
                <p className="mt-4 text-sm text-slate-500">برای تغییر ترتیب، ردیف‌ها را با دستگیره بکشید و رها کنید.</p>
            )}

            <Card className="mt-4 overflow-hidden p-2">
                {ordered.length === 0 ? (
                    <p className="px-4 py-10 text-center text-sm text-slate-500">سکشنی ثبت نشده است.</p>
                ) : (
                    <ul className="divide-y divide-slate-100 dark:divide-slate-800">
                        {ordered.map((row) => (
                            <li
                                key={row.id}
                                draggable={canDrag}
                                onDragStart={() => setDragId(row.id)}
                                onDragEnd={() => {
                                    setDragId(null);
                                    setDropId(null);
                                }}
                                onDragOver={(e) => {
                                    if (!canDrag || dragId === null) {
                                        return;
                                    }
                                    e.preventDefault();
                                    setDropId(row.id);
                                }}
                                onDrop={(e) => {
                                    e.preventDefault();
                                    handleDrop(row.id);
                                }}
                                className={cn(
                                    'flex flex-wrap items-center gap-3 px-3 py-3 sm:flex-nowrap',
                                    dragId === row.id && 'opacity-50',
                                    dropId === row.id && dragId !== row.id && 'bg-slate-50 dark:bg-slate-900/40',
                                )}
                            >
                                {canDrag ? (
                                    <span
                                        className="inline-flex cursor-grab text-slate-400 active:cursor-grabbing"
                                        title="جابه‌جایی"
                                    >
                                        <GripVertical className="size-5" />
                                    </span>
                                ) : (
                                    <span className="w-5" />
                                )}
                                <div className="min-w-0 flex-1">
                                    <p className="font-medium text-slate-900 dark:text-slate-100">{row.title}</p>
                                    <p className="text-xs text-slate-500">
                                        {row.section_template_label ?? row.content_type_label ?? '—'} · {row.tabs_count} تب
                                    </p>
                                </div>
                                <Badge variant={row.status ? 'success' : 'secondary'}>
                                    {row.status ? 'فعال' : 'غیرفعال'}
                                </Badge>
                                <div className="flex gap-1">
                                    {abilities.update && (
                                        <AdminButton variant="ghost" size="sm" href={`/admin/home-sections/${row.id}/edit`}>
                                            <Pencil className="size-4" />
                                        </AdminButton>
                                    )}
                                    {abilities.delete && (
                                        <AdminButton
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => {
                                                confirmToast('این سکشن حذف شود؟', () => {
        router.delete(`/admin/home-sections/${row.id}`);
    });;
                                            }}
                                        >
                                            <Trash2 className="size-4 text-red-600" />
                                        </AdminButton>
                                    )}
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </Card>
        </AdminLayout>
    );
}
