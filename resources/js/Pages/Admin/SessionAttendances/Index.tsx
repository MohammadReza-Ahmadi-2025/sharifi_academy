import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Eye } from 'lucide-react';
import { FormEvent, useState } from 'react';

export default function SessionAttendancesIndex({ sessions, filters }: { sessions: { data: { id: number; title: string; session_number: number; starts_at: string | null; course_title: string | null; class_title: string | null; attendances_count: number }[]; current_page: number; last_page: number; per_page: number; total: number }; filters: { search: string; per_page: number } }) {
    const [search, setSearch] = useState(filters.search);
    const apply = (overrides: Record<string, string | number | undefined> = {}) => router.get('/admin/session-attendances', { search: search || undefined, per_page: filters.per_page, ...overrides }, { preserveState: true, replace: true });
    return (
        <AdminLayout>
            <Head title="حضور و غیاب" />
            <PageHeader title="حضور و غیاب جلسات" />
            <AdminListFilters search={search} onSearchChange={setSearch} onSubmit={(e: FormEvent) => { e.preventDefault(); apply({ page: 1 }); }} onClear={() => { setSearch(''); router.get('/admin/session-attendances'); }} searchPlaceholder="جستجو دوره یا جلسه…" />
            <Card className="mt-4 overflow-hidden">
                <Table><TableHeader><TableRow><TableHead>جلسه</TableHead><TableHead>دوره / کلاس</TableHead><TableHead>ثبت‌شده</TableHead><TableHead className="w-16" /></TableRow></TableHeader>
                    <TableBody>{sessions.data.length === 0 ? <TableEmptyState colSpan={4} message="جلسه‌ای نیست." /> : sessions.data.map((row) => (
                        <TableRow key={row.id}><TableCell><div className="font-medium">{row.title}</div><div className="text-xs text-slate-500">جلسه {row.session_number}</div></TableCell><TableCell><div>{row.course_title}</div><div className="text-xs text-slate-500">{row.class_title}</div></TableCell><TableCell>{row.attendances_count}</TableCell><TableCell><AdminButton variant="ghost" size="sm" href={`/admin/session-attendances/${row.id}`}><Eye className="size-4" /></AdminButton></TableCell></TableRow>
                    ))}</TableBody></Table>
                <SimpleTablePagination className="border-t" currentPage={sessions.current_page} lastPage={sessions.last_page} total={sessions.total} perPage={sessions.per_page} from={null} to={null} itemLabel="جلسه" onPageChange={(page) => apply({ page })} onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })} />
            </Card>
        </AdminLayout>
    );
}
