import { Badge } from '@/Components/Admin/shared/ui/badge';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { Pencil, Plus, Trash2 } from 'lucide-react';

type Props = {
    routePrefix: string;
    course: { id: number; title: string };
    courseClass: { id: number; name: string; online_platform_label?: string };
    sessions: {
        id: number;
        session_number: number;
        title: string | null;
        status_label: string;
        join_configured: boolean;
        join_label: string | null;
    }[];
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function SessionsIndex({ routePrefix, course, courseClass, sessions, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const base = `${routePrefix}/courses/${course.id}/classes/${courseClass.id}/sessions`;
    return (
        <AdminLayout>
            <Head title="جلسات" />
            <PageHeader
                title={`جلسات — ${courseClass.name}`}
                description={
                    courseClass.online_platform_label
                        ? `پلتفرم آنلاین: ${courseClass.online_platform_label}`
                        : undefined
                }
                backHref={`${routePrefix}/courses/${course.id}/classes/${courseClass.id}/edit`}
                actions={
                    abilities.create ? (
                        <AdminButton href={`${base}/create`}>
                            <Plus className="ml-1 h-4 w-4" /> جلسه جدید
                        </AdminButton>
                    ) : null
                }
            />
            <Table className="mt-6">
                <TableHeader>
                    <TableRow>
                        <TableHead>#</TableHead>
                        <TableHead>عنوان</TableHead>
                        <TableHead>وضعیت</TableHead>
                        <TableHead>اتصال آنلاین</TableHead>
                        <TableHead />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {sessions.map((s) => (
                        <TableRow key={s.id}>
                            <TableCell>{s.session_number}</TableCell>
                            <TableCell>{s.title ?? '—'}</TableCell>
                            <TableCell>{s.status_label}</TableCell>
                            <TableCell>
                                {s.join_configured ? (
                                    <Badge variant="success">{s.join_label ?? 'آماده'}</Badge>
                                ) : (
                                    <Badge variant="secondary">تنظیم نشده</Badge>
                                )}
                            </TableCell>
                            <TableCell className="flex justify-end gap-2">
                                {abilities.update && (
                                    <Link href={`${base}/${s.id}/edit`} className="inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-slate-100">
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                )}
                                {abilities.delete && <AdminButton variant="ghost" size="sm" onClick={() => confirmToast('حذف؟', () => router.delete(`${base}/${s.id}`))}><Trash2 className="size-4 text-red-600" /></AdminButton>}
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </AdminLayout>
    );
}
