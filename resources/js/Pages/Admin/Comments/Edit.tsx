import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    comment: {
        id: number;
        display_name: string;
        body: string;
        status: number;
        commentable_type_label: string;
        commentable_title: string | null;
    };
    statusOptions: { value: number; label: string }[];
};

export default function CommentsEdit({ comment, statusOptions }: Props) {
    const statusSelectOptions = mapToSelectOptions(
        statusOptions.map((o) => ({ value: String(o.value), label: o.label })),
    );

    const form = useForm({
        display_name: comment.display_name,
        body: comment.body,
        status: comment.status,
    });

    return (
        <AdminLayout>
            <Head title="ویرایش نظر" />
            <PageHeader title="ویرایش نظر" backHref="/admin/comments" />
            <p className="mt-4 text-sm text-slate-500">
                {comment.commentable_type_label}
                {comment.commentable_title ? ` — ${comment.commentable_title}` : ''}
            </p>
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => ({
                        ...data,
                        status: Number(data.status),
                    }));
                    form.put(`/admin/comments/${comment.id}`);
                }}>
                <Card className="space-y-4 p-6">
                    <FormField label="نام نمایشی" error={form.errors.display_name}>
                        <AdminInput
                            value={form.data.display_name}
                            disabled={form.processing}
                            onChange={(e) => form.setData('display_name', e.target.value)}
                        />
                    </FormField>
                    <FormField label="وضعیت" required error={form.errors.status}>
                        <Select
                            isDisabled={form.processing}
                            options={statusSelectOptions}
                            value={findSelectOption(statusSelectOptions, String(form.data.status))}
                            onChange={(option) => form.setData('status', Number(option?.value ?? 0))}
                        />
                    </FormField>
                    <FormField label="متن" required error={form.errors.body}>
                        <Textarea
                            rows={6}
                            value={form.data.body}
                            disabled={form.processing}
                            onChange={(e) => form.setData('body', e.target.value)}
                        />
                    </FormField>
                </Card>
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
