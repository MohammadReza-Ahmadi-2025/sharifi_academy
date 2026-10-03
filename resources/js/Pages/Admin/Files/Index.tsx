import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';

export default function FilesIndex({ files, filters, typeOptions, abilities }: { files: { data: { id: number; name: string; type: string; extension: string | null; size: number; url: string; fileable_type: string | null; fileable_id: number | null }[]; current_page: number; last_page: number; per_page: number; total: number }; filters: { search: string; type: string; per_page: number }; typeOptions: { value: string; label: string }[]; abilities: { delete: boolean } }) {
    const [search, setSearch] = useState(filters.search);
    const [type, setType] = useState(filters.type);
    const apply = (overrides: Record<string, string | number | undefined> = {}) => router.get('/admin/files', { search: search || undefined, type: type || undefined, per_page: filters.per_page, ...overrides }, { preserveState: true, replace: true });
    return (
        <AdminLayout>
            <Head title="فایل‌ها" />
            <PageHeader title="فایل‌های رسانه" />
            <AdminListFilters search={search} onSearchChange={setSearch} onSubmit={(e: FormEvent) => { e.preventDefault(); apply({ page: 1 }); }} onClear={() => { setSearch(''); setType(''); router.get('/admin/files'); }} searchPlaceholder="جستجو نام یا مسیر…">
                <AdminFilterSelect value={type} onChange={(e) => setType(e.target.value)} placeholder="همه نوع‌ها" options={typeOptions} />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table><TableHeader><TableRow><TableHead>پیش‌نمایش</TableHead><TableHead>نام</TableHead><TableHead>نوع</TableHead><TableHead>متصل به</TableHead><TableHead className="w-16" /></TableRow></TableHeader>
                    <TableBody>{files.data.length === 0 ? <TableEmptyState colSpan={5} message="فایلی نیست." /> : files.data.map((row) => (
                        <TableRow key={row.id}><TableCell>{row.type.includes('image') || row.type === 'gallery' ? <img src={row.url} alt="" className="size-10 rounded object-cover" /> : '—'}</TableCell><TableCell className="max-w-xs truncate">{row.name}</TableCell><TableCell>{row.type}</TableCell><TableCell className="text-xs text-slate-500">{row.fileable_type ? `${row.fileable_type} #${row.fileable_id}` : '—'}</TableCell><TableCell>{abilities.delete && <AdminButton variant="ghost" size="sm" onClick={() => window.confirm('حذف فایل؟') && router.delete(`/admin/files/${row.id}`)}><Trash2 className="size-4 text-red-600" /></AdminButton>}</TableCell></TableRow>
                    ))}</TableBody></Table>
                <SimpleTablePagination className="border-t" currentPage={files.current_page} lastPage={files.last_page} total={files.total} perPage={files.per_page} from={null} to={null} itemLabel="فایل" onPageChange={(page) => apply({ page })} onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })} />
            </Card>
        </AdminLayout>
    );
}
