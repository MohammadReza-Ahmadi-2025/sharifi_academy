import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MoneyInput } from '@/Components/Admin/shared/ui/money-input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';

type ClassOption = { value: number; label: string; price: number };

type Props = {
    courses: { value: number; label: string }[];
    classes: ClassOption[];
    userOptions: { value: number; label: string }[];
    enrollmentStatusOptions: { value: string; label: string }[];
    selectedCourseId: number | null;
    selectedClassId: number | null;
    defaultPrice: number;
};

export default function CourseEnrollmentsCreate({
    courses,
    classes,
    userOptions,
    enrollmentStatusOptions,
    selectedCourseId,
    selectedClassId,
    defaultPrice,
}: Props) {
    const courseOptions = mapToSelectOptions(courses);
    const classOptions = mapToSelectOptions(classes.map((c) => ({ value: c.value, label: c.label })));
    const userSelectOptions = mapToSelectOptions(userOptions);
    const statusSelectOptions = mapToSelectOptions(enrollmentStatusOptions);

    const form = useForm({
        course_id: selectedCourseId ?? 0,
        course_class_id: selectedClassId ?? 0,
        user_id: userOptions[0]?.value ?? 0,
        status: enrollmentStatusOptions[0]?.value ?? 'confirmed',
        price_amount: defaultPrice,
        admin_note: '',
    });

    useEffect(() => {
        form.setData('price_amount', defaultPrice);
    }, [defaultPrice]);

    const onCourseChange = (courseId: number) => {
        router.get(
            '/admin/course-enrollments/create',
            { course_id: courseId || undefined },
            { preserveState: true, replace: true },
        );
    };

    const onClassChange = (classId: number) => {
        const courseId = form.data.course_id;
        router.get(
            '/admin/course-enrollments/create',
            {
                course_id: courseId || undefined,
                course_class_id: classId || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/admin/course-enrollments');
    };

    return (
        <AdminLayout>
            <Head title="ثبت‌نام در دوره" />

            <PageHeader title="ثبت‌نام در دوره" backHref="/admin/course-enrollments" />

            <AdminFormPage onSubmit={submit}>
                <Card className="space-y-4 p-6">
                    <p className="text-sm text-slate-600 dark:text-slate-400">
                        فقط کاربرانی در لیست «کاربر» نمایش داده می‌شوند که نام، نام خانوادگی و شهر در پروفایل داشته باشند.
                    </p>

                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="دوره" required error={form.errors.course_id}>
                            <Select
                                isDisabled={form.processing}
                                options={courseOptions}
                                value={findSelectOption(courseOptions, form.data.course_id)}
                                onChange={(option) => {
                                    const id = Number(option?.value ?? 0);
                                    form.setData('course_id', id);
                                    form.setData('course_class_id', 0);
                                    onCourseChange(id);
                                }}
                            />
                        </FormField>
                        <FormField label="کلاس" required error={form.errors.course_class_id}>
                            <Select
                                isDisabled={form.processing || !form.data.course_id}
                                options={classOptions}
                                value={findSelectOption(classOptions, form.data.course_class_id)}
                                onChange={(option) => {
                                    const id = Number(option?.value ?? 0);
                                    form.setData('course_class_id', id);
                                    onClassChange(id);
                                }}
                            />
                        </FormField>
                        <FormField label="کاربر" required error={form.errors.user_id}>
                            <Select
                                isDisabled={form.processing || userSelectOptions.length === 0}
                                options={userSelectOptions}
                                placeholder={
                                    userSelectOptions.length === 0
                                        ? 'کاربر واجد شرایطی نیست'
                                        : 'انتخاب کاربر…'
                                }
                                value={findSelectOption(userSelectOptions, form.data.user_id)}
                                onChange={(option) => form.setData('user_id', Number(option?.value ?? 0))}
                            />
                        </FormField>
                        <FormField label="وضعیت" required error={form.errors.status}>
                            <Select
                                isDisabled={form.processing}
                                options={statusSelectOptions}
                                value={findSelectOption(statusSelectOptions, form.data.status)}
                                onChange={(option) => form.setData('status', option?.value ?? '')}
                            />
                        </FormField>
                        <FormField label="مبلغ (ریال)" error={form.errors.price_amount}>
                            <MoneyInput
                                disabled={form.processing}
                                value={String(form.data.price_amount ?? '')}
                                onChange={(value) => form.setData('price_amount', value ? Number(value) : 0)}
                            />
                        </FormField>
                        <FormField label="یادداشت" className="md:col-span-2" error={form.errors.admin_note}>
                            <Textarea
                                rows={2}
                                disabled={form.processing}
                                value={form.data.admin_note}
                                onChange={(e) => form.setData('admin_note', e.target.value)}
                            />
                        </FormField>
                    </div>
                </Card>

                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing || !form.data.user_id}>
                        ثبت‌نام
                    </AdminButton>
                    <AdminButton type="button" variant="outline" href="/admin/course-enrollments">
                        انصراف
                    </AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
