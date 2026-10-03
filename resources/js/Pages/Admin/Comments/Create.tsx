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

type Props = {
    statusOptions: { value: number; label: string }[];
};

export default function CommentsCreate({ statusOptions }: Props) {
    const statusSelectOptions = mapToSelectOptions(
        statusOptions.map((o) => ({ value: String(o.value), label: o.label })),
    );
    const typeOptions = mapToSelectOptions([
        { value: 'product', label: 'محصول' },
        { value: 'post', label: 'مقاله' },
    ]);

    const form = useForm({
        commentable_type: 'product',
        commentable_id: '',
        display_name: '',
        body: '',
        rating: '' as string | number,
        status: 1,
    });

    return (
        <AdminLayout>
            <Head title="ثبت نظر" />
            <PageHeader title="ثبت نظر دستی" backHref="/admin/comments" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => ({
                        ...data,
                        commentable_id: Number(data.commentable_id),
                        rating: data.rating === '' ? null : Number(data.rating),
                        status: Number(data.status),
                    })).post('/admin/comments');
                }}
                className="mt-6 space-y-6"
            >
                <Card className="space-y-4 p-6">
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="نوع منبع" required error={form.errors.commentable_type}>
                            <Select
                                isDisabled={form.processing}
                                options={typeOptions}
                                value={findSelectOption(typeOptions, form.data.commentable_type)}
                                onChange={(option) => form.setData('commentable_type', option?.value ?? 'product')}
                            />
                        </FormField>
                        <FormField label="شناسه منبع" required error={form.errors.commentable_id}>
                            <AdminInput
                                dir="ltr"
                                type="number"
                                min={1}
                                value={form.data.commentable_id}
                                disabled={form.processing}
                                onChange={(e) => form.setData('commentable_id', e.target.value)}
                            />
                        </FormField>
                        <FormField label="نام نمایشی" required error={form.errors.display_name}>
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
                                onChange={(option) => form.setData('status', Number(option?.value ?? 1))}
                            />
                        </FormField>
                        {form.data.commentable_type === 'product' && (
                            <FormField label="امتیاز (۱–۵)" error={form.errors.rating}>
                                <AdminInput
                                    dir="ltr"
                                    type="number"
                                    min={1}
                                    max={5}
                                    value={form.data.rating}
                                    disabled={form.processing}
                                    onChange={(e) => form.setData('rating', e.target.value)}
                                />
                            </FormField>
                        )}
                    </div>
                    <FormField label="متن نظر" required error={form.errors.body}>
                        <Textarea
                            rows={5}
                            value={form.data.body}
                            disabled={form.processing}
                            onChange={(e) => form.setData('body', e.target.value)}
                        />
                    </FormField>
                </Card>
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
