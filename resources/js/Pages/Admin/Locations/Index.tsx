import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { FormEvent, useState } from 'react';

export default function LocationsIndex({ provinces, filters, abilities }: { provinces: { data: { id: number; title: string; cities_count: number }[]; current_page: number; last_page: number; per_page: number; total: number }; filters: { search: string; per_page: number }; abilities: { update: boolean } }) {
    const [search, setSearch] = useState(filters.search);
    const apply = (overrides: Record<string, string | number | undefined> = {}) => router.get('/admin/locations', { search: search || undefined, per_page: filters.per_page, ...overrides }, { preserveState: true, replace: true });
    return (
        <AdminLayout>
            <Head title="استان و شهر" />
            <PageHeader title="استان‌ها" />
            <AdminListFilters search={search} onSearchChange={setSearch} onSubmit={(e: FormEvent) => { e.preventDefault(); apply({ page: 1 }); }} onClear={() => { setSearch(''); router.get('/admin/locations'); }} searchPlaceholder="جستجو نام استان…" />
            <Card className="mt-4 overflow-hidden">
                <Table><TableHeader><TableRow><TableHead>استان</TableHead><TableHead>تعداد شهر</TableHead><TableHead className="w-20" /></TableRow></TableHeader>
                    <TableBody>{provinces.data.length === 0 ? <TableEmptyState colSpan={3} message="استانی نیست." /> : provinces.data.map((row) => (
                        <TableRow key={row.id}><TableCell className="font-medium">{row.title}</TableCell><TableCell>{row.cities_count}</TableCell><TableCell>{abilities.update && <AdminButton variant="ghost" size="sm" href={`/admin/locations/${row.id}/edit`}><Pencil className="size-4" /></AdminButton>}</TableCell></TableRow>
                    ))}</TableBody></Table>
                <SimpleTablePagination className="border-t" currentPage={provinces.current_page} lastPage={provinces.last_page} total={provinces.total} perPage={provinces.per_page} from={null} to={null} itemLabel="استان" onPageChange={(page) => apply({ page })} onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })} />
            </Card>
        </AdminLayout>
    );
}
