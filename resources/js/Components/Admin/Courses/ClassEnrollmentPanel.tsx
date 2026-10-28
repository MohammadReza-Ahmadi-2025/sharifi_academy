import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { formatNumberInput } from '@/Components/Admin/shared/lib/format-number';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MoneyInput } from '@/Components/Admin/shared/ui/money-input';
import { Select } from '@/Components/Admin/shared/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableEmptyState,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import { router, useForm } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent } from 'react';

type EnrollmentRow = {
    id: number;
    user_label: string;
    status: string;
    status_label: string;
    price_amount: number;
};

type Props = {
    courseId: number;
    classId: number;
    enrollments: EnrollmentRow[];
    userOptions: { value: number; label: string }[];
    enrollmentStatusOptions: { value: string; label: string }[];
    defaultPrice: number;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
};

export function ClassEnrollmentPanel({
    courseId,
    classId,
    enrollments,
    userOptions,
    enrollmentStatusOptions,
    defaultPrice,
    canCreate,
    canUpdate,
    canDelete,
}: Props) {
    const confirmToast = useConfirmToast();
    if (!canCreate && enrollments.length === 0) {
        return null;
    }

    const baseUrl = `/admin/courses/${courseId}/classes/${classId}/enrollments`;
    const userSelectOptions = mapToSelectOptions(userOptions);
    const statusSelectOptions = mapToSelectOptions(enrollmentStatusOptions);

    const form = useForm({
        user_id: userOptions[0]?.value ?? 0,
        status: 'confirmed',
        price_amount: defaultPrice,
        admin_note: '',
    });

    const submitEnrollment = (event: FormEvent) => {
        event.preventDefault();
        form.post(baseUrl, { preserveScroll: true });
    };

    const toggleStatus = (row: EnrollmentRow) => {
        const nextStatus = row.status === 'confirmed' ? 'cancelled' : 'confirmed';
        router.put(`${baseUrl}/${row.id}`, {
            status: nextStatus,
            price_amount: row.price_amount,
        }, { preserveScroll: true });
    };

    const removeEnrollment = (row: EnrollmentRow) => {
        confirmToast(`ثبت‌نام «${row.user_label}» حذف شود؟`, () => {
        router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
    });
    };

    return (
        <Card className="mt-8 space-y-6 p-6">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">ثبت‌نام‌ها</h2>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    ثبت‌نام دستی هر کاربر فعال.{' '}
                    <a href="/admin/course-enrollments/create" className="text-indigo-600 hover:underline">
                        ثبت‌نام از بخش مرکزی
                    </a>
                </p>
            </div>

            {canCreate ? (
                <form onSubmit={submitEnrollment} className="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                    <div className="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                        <UserPlus className="h-4 w-4" />
                        ثبت‌نام جدید
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="کاربر" required error={form.errors.user_id}>
                            <Select
                                isDisabled={form.processing || userSelectOptions.length === 0}
                                options={userSelectOptions}
                                placeholder={
                                    userSelectOptions.length === 0 ? 'کاربر فعالی نیست' : undefined
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
                        <FormField label="یادداشت ادمین" className="md:col-span-2" error={form.errors.admin_note}>
                            <Textarea
                                rows={2}
                                disabled={form.processing}
                                value={form.data.admin_note}
                                onChange={(e) => form.setData('admin_note', e.target.value)}
                            />
                        </FormField>
                    </div>
                    <AdminButton type="submit" size="sm" disabled={form.processing}>
                        ثبت‌نام
                    </AdminButton>
                </form>
            ) : null}

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>کاربر</TableHead>
                        <TableHead>وضعیت</TableHead>
                        <TableHead>مبلغ</TableHead>
                        <TableHead className="w-40 text-left">عملیات</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {enrollments.length === 0 ? (
                        <TableEmptyState colSpan={4} title="ثبت‌نامی ثبت نشده" description="از فرم بالا اولین ثبت‌نام را اضافه کنید." />
                    ) : (
                        enrollments.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="font-medium text-slate-900 dark:text-slate-100">{row.user_label}</TableCell>
                                <TableCell>
                                    <Badge variant={row.status === 'confirmed' ? 'success' : 'secondary'}>{row.status_label}</Badge>
                                </TableCell>
                                <TableCell dir="ltr" className="text-left font-mono text-sm">
                                    {formatNumberInput(row.price_amount)}
                                </TableCell>
                                <TableCell>
                                    <div className="flex justify-end gap-2">
                                        {canUpdate ? (
                                            <AdminButton type="button" variant="outline" size="sm" onClick={() => toggleStatus(row)}>
                                                {row.status === 'confirmed' ? 'لغو' : 'تأیید'}
                                            </AdminButton>
                                        ) : null}
                                        {canDelete ? (
                                            <AdminButton type="button" variant="ghost" size="sm" onClick={() => removeEnrollment(row)}>
                                                حذف
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
    );
}
