import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    course: {
        id: number;
        title: string;
        slug: string;
        code: string | null;
        status_label: string;
        level_label: string | null;
        classes_count: number;
        short_description?: string | null;
        description?: string | null;
        duration_minutes?: number | null;
        display_order?: number;
        is_featured: boolean;
        category_labels: string[];
        published_at?: string | null;
        created_at?: string | null;
        updated_at?: string | null;
        creator_label?: string | null;
    };
    abilities: { update: boolean; delete: boolean; classes: boolean };
};

function MetaRow({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <span className="text-xs font-medium text-slate-500 dark:text-slate-400">{label}</span>
            <span className="text-sm text-slate-900 dark:text-slate-100 sm:text-left">{children}</span>
        </div>
    );
}

export default function CoursesShow({ course, abilities }: Props) {
    const handleDelete = () => {
        if (!window.confirm(`دوره «${course.title}» حذف شود؟`)) {
            return;
        }
        router.delete(`/admin/courses/${course.id}`);
    };

    return (
        <AdminLayout>
            <Head title={course.title} />
            <PageHeader
                title={course.title}
                description={course.short_description ?? undefined}
                backHref="/admin/courses"
                actions={
                    <div className="flex flex-wrap gap-2">
                        {abilities.classes && (
                            <AdminButton href={`/admin/courses/${course.id}/classes`}>
                                مدیریت کلاس‌ها ({course.classes_count.toLocaleString('fa-IR')})
                            </AdminButton>
                        )}
                        {abilities.update && (
                            <AdminButton href={`/admin/courses/${course.id}/edit`} variant="secondary">
                                ویرایش
                            </AdminButton>
                        )}
                        {abilities.delete && (
                            <AdminButton type="button" variant="outline" onClick={handleDelete}>
                                <Trash2 className="ml-1 h-4 w-4 text-rose-600" />
                                حذف
                            </AdminButton>
                        )}
                    </div>
                }
            />

            <div className="mt-6 grid gap-4 lg:grid-cols-3">
                <Card className="space-y-4 p-6 lg:col-span-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="info">{course.status_label}</Badge>
                        {course.level_label ? <Badge variant="secondary">{course.level_label}</Badge> : null}
                        {course.is_featured ? <Badge variant="warning">ویژه</Badge> : null}
                    </div>

                    {course.description ? (
                        <SiteRichHtml
                            html={course.description}
                            className="text-sm dark:text-slate-300 [&_p]:text-slate-700"
                        />
                    ) : (
                        <p className="text-sm text-slate-500">توضیحات تکمیلی ثبت نشده.</p>
                    )}
                </Card>

                <Card className="space-y-4 p-6">
                    <h2 className="text-sm font-semibold text-slate-900 dark:text-slate-100">شناسه و نمایش</h2>
                    <div className="space-y-3">
                        <MetaRow label="اسلاگ">
                            <span dir="ltr" className="font-mono">{course.slug}</span>
                        </MetaRow>
                        <MetaRow label="کد">{course.code ?? '—'}</MetaRow>
                        <MetaRow label="مدت">
                            {course.duration_minutes != null
                                ? `${course.duration_minutes.toLocaleString('fa-IR')} دقیقه`
                                : '—'}
                        </MetaRow>
                        <MetaRow label="دسته‌ها">
                            {course.category_labels.length > 0 ? course.category_labels.join('، ') : '—'}
                        </MetaRow>
                    </div>
                </Card>

                <Card className="space-y-4 p-6 lg:col-span-3">
                    <h2 className="text-sm font-semibold text-slate-900 dark:text-slate-100">سوابق</h2>
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <MetaRow label="تاریخ انتشار">
                            {formatPersianDateTime(course.published_at, 'long')}
                        </MetaRow>
                        <MetaRow label="ایجاد">
                            {formatPersianDateTime(course.created_at, 'table')}
                        </MetaRow>
                        <MetaRow label="آخرین به‌روزرسانی">
                            {formatPersianDateTime(course.updated_at, 'table')}
                        </MetaRow>
                        <MetaRow label="ایجادکننده">{course.creator_label ?? '—'}</MetaRow>
                    </div>
                </Card>
            </div>
        </AdminLayout>
    );
}
