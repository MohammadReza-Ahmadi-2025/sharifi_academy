import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Check, Eye, X } from 'lucide-react';

type CourseRow = {
    id: number;
    title: string;
    slug: string;
    status_label: string;
    creator_name: string | null;
};

type ResumeRow = {
    id: number;
    full_name: string;
    mobile: string;
    resume_items_count: number;
    submitted_at: string | null;
    review_url: string;
};

type Props = {
    pendingCourses: CourseRow[];
    pendingResumes: ResumeRow[];
    counts: { courses: number; resumes: number };
    abilities: { moderate: boolean };
};

export default function InstructorModerationIndex({ pendingCourses, pendingResumes, counts, abilities }: Props) {
    const approveCourse = (id: number) => {
        router.post(`/admin/instructor-moderation/courses/${id}/approve`, {}, { preserveScroll: true });
    };

    const rejectCourse = (id: number) => {
        router.post(`/admin/instructor-moderation/courses/${id}/reject`, {}, { preserveScroll: true });
    };

    const approveResume = (id: number) => {
        router.post(`/admin/instructor-moderation/resumes/${id}/approve`, {}, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <Head title="تایید اساتید" />
            <PageHeader
                title="تایید محتوای اساتید"
                description="دوره‌های در انتظار انتشار و رزومه‌های ارسال‌شده برای بررسی"
            />

            <div className="mb-6 flex flex-wrap gap-3">
                <Badge variant="secondary">دوره: {counts.courses.toLocaleString('fa-IR')}</Badge>
                <Badge variant="secondary">رزومه: {counts.resumes.toLocaleString('fa-IR')}</Badge>
            </div>

            <Card className="mb-8 p-4">
                <h2 className="mb-4 text-lg font-semibold">دوره‌های در انتظار تایید</h2>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>استاد</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-[200px]">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {pendingCourses.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={4} className="text-center text-slate-500">
                                    موردی در صف نیست.
                                </TableCell>
                            </TableRow>
                        ) : (
                            pendingCourses.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <Link href={`/admin/courses/${row.id}/edit`} className="font-medium text-primary hover:underline">
                                            {row.title}
                                        </Link>
                                    </TableCell>
                                    <TableCell>{row.creator_name ?? '—'}</TableCell>
                                    <TableCell>{row.status_label}</TableCell>
                                    <TableCell>
                                        {abilities.moderate ? (
                                            <div className="flex flex-wrap gap-2">
                                                <AdminButton type="button" size="sm" onClick={() => approveCourse(row.id)}>
                                                    <Check className="size-4" />
                                                    تایید انتشار
                                                </AdminButton>
                                                <AdminButton type="button" size="sm" variant="outline" onClick={() => rejectCourse(row.id)}>
                                                    <X className="size-4" />
                                                    رد
                                                </AdminButton>
                                            </div>
                                        ) : null}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </Card>

            <Card className="p-4">
                <h2 className="mb-4 text-lg font-semibold">رزومه‌های در انتظار تایید</h2>
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>استاد</TableHead>
                            <TableHead>موبایل</TableHead>
                            <TableHead>تعداد موارد</TableHead>
                            <TableHead className="w-[240px]">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {pendingResumes.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={4} className="text-center text-slate-500">
                                    موردی در صف نیست.
                                </TableCell>
                            </TableRow>
                        ) : (
                            pendingResumes.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="font-medium">{row.full_name}</TableCell>
                                    <TableCell dir="ltr" className="text-left">{row.mobile}</TableCell>
                                    <TableCell>{row.resume_items_count.toLocaleString('fa-IR')}</TableCell>
                                    <TableCell>
                                        <div className="flex flex-wrap gap-2">
                                            <Link href={row.review_url}>
                                                <AdminButton type="button" size="sm" variant="outline">
                                                    <Eye className="size-4" />
                                                    بررسی
                                                </AdminButton>
                                            </Link>
                                            {abilities.moderate ? (
                                                <AdminButton type="button" size="sm" onClick={() => approveResume(row.id)}>
                                                    <Check className="size-4" />
                                                    تایید
                                                </AdminButton>
                                            ) : null}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </Card>
        </AdminLayout>
    );
}
