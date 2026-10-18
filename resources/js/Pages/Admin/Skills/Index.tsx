import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent, useState } from 'react';

export default function SkillsIndex({ skills, filters, abilities }: { skills: { data: { id: number; name: string; category: string | null }[]; current_page: number; last_page: number; per_page: number; total: number }; filters: { search: string; per_page: number }; abilities: { create: boolean; update: boolean; delete: boolean } }) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const apply = (overrides: Record<string, string | number | undefined> = {}) => router.get('/admin/skills', { search: search || undefined, per_page: filters.per_page, ...overrides }, { preserveState: true, replace: true });
    return (
        <AdminLayout>
            <Head title="مهارت‌ها" />
            <PageHeader title="مهارت‌ها" actions={abilities.create ? <AdminButton href="/admin/skills/create"><Plus className="size-4" />مهارت جدید</AdminButton> : null} />
            <AdminListFilters search={search} onSearchChange={setSearch} onSubmit={(e: FormEvent) => { e.preventDefault(); apply({ page: 1 }); }} onClear={() => { setSearch(''); router.get('/admin/skills'); }} searchPlaceholder="جستجو نام…" />
            <Card className="mt-4 overflow-hidden">
                <Table><TableHeader><TableRow><TableHead>نام</TableHead><TableHead>دسته دوره</TableHead><TableHead className="w-24" /></TableRow></TableHeader>
                    <TableBody>{skills.data.length === 0 ? <TableEmptyState colSpan={3} message="مهارتی ثبت نشده." /> : skills.data.map((row) => (
                        <TableRow key={row.id}><TableCell className="font-medium">{row.name}</TableCell><TableCell>{row.category ?? '—'}</TableCell><TableCell><div className="flex gap-1">{abilities.update && <AdminButton variant="ghost" size="sm" href={`/admin/skills/${row.id}/edit`}><Pencil className="size-4" /></AdminButton>}{abilities.delete && <AdminButton variant="ghost" size="sm" onClick={() => confirmToast('حذف؟', () => router.delete(`/admin/skills/${row.id}`))}><Trash2 className="size-4 text-red-600" /></AdminButton>}</div></TableCell></TableRow>
                    ))}</TableBody></Table>
                <SimpleTablePagination className="border-t" currentPage={skills.current_page} lastPage={skills.last_page} total={skills.total} perPage={skills.per_page} from={null} to={null} itemLabel="مهارت" onPageChange={(page) => apply({ page })} onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })} />
            </Card>
        </AdminLayout>
    );
    }
