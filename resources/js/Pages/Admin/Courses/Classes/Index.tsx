import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { Pencil, Plus, Trash2 } from 'lucide-react';

type Props = {
    course: { id: number; title: string };
    classes: { id: number; name: string; code: string; status_label: string; sessions_count: number; enrollments_count: number }[];
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function CourseClassesIndex({ course, classes, abilities }: Props) {
    const confirmToast = useConfirmToast();
    return (
        <AdminLayout>
            <Head title={`کلاس‌های ${course.title}`} />
            <PageHeader
                title={`کلاس‌های ${course.title}`}
                backHref={`/admin/courses/${course.id}`}
                actions={
                    abilities.create ? (
                        <AdminButton href={`/admin/courses/${course.id}/classes/create`}>
                            <Plus className="ml-1 h-4 w-4" /> کلاس جدید
                        </AdminButton>
                    ) : null
                }
            />
            <Card className="mt-6 overflow-hidden p-0">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>نام</TableHead>
                        <TableHead>کد</TableHead>
                        <TableHead>وضعیت</TableHead>
                        <TableHead>جلسات</TableHead>
                        <TableHead>ثبت‌نام</TableHead>
                        <TableHead className="w-24 text-left">عملیات</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {classes.length === 0 ? (
                        <TableEmptyState
                            colSpan={6}
                            title="کلاسی برای این دوره ثبت نشده"
                            description="با «کلاس جدید» اولین گروه آموزشی را اضافه کنید."
                        />
                    ) : classes.map((row) => (
                        <TableRow key={row.id}>
                            <TableCell>{row.name}</TableCell>
                            <TableCell>{row.code}</TableCell>
                            <TableCell>{row.status_label}</TableCell>
                            <TableCell>{row.sessions_count}</TableCell>
                            <TableCell>{row.enrollments_count}</TableCell>
                            <TableCell className="flex justify-end gap-2">
                                {abilities.update && (
                                    <Link
                                        href={`/admin/courses/${course.id}/classes/${row.id}/edit`}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100"
                                        title="ویرایش"
                                    >
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                )}
                                {abilities.delete && (
                                    <AdminButton variant="ghost" size="sm" onClick={() => confirmToast('حذف؟', () => router.delete(`/admin/courses/${course.id}/classes/${row.id}`))}>
                                        <Trash2 className="size-4 text-red-600" />
                                    </AdminButton>
                                )}
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
            </Card>
        </AdminLayout>
    );
}
