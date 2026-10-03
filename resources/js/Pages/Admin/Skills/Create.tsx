import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { findSelectOption } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function SkillCreate({ categoryOptions }: { categoryOptions: { value: number; label: string }[] }) {
    const form = useForm<{ name: string; category_id: number | '' }>({ name: '', category_id: '' });
    return (
        <AdminLayout>
            <Head title="ایجاد مهارت" />
            <PageHeader title="ایجاد مهارت" backHref="/admin/skills" />
            <form onSubmit={(e) => { e.preventDefault(); form.post('/admin/skills'); }} className="mt-6 max-w-md space-y-4">
                <FormField label="نام" error={form.errors.name} required><Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /></FormField>
                <FormField label="دسته دوره (اختیاری)" error={form.errors.category_id}>
                    <Select
                        isClearable
                        placeholder="بدون دسته"
                        options={categoryOptions.map((o) => ({ value: String(o.value), label: o.label }))}
                        value={findSelectOption(
                            categoryOptions.map((o) => ({ value: String(o.value), label: o.label })),
                            form.data.category_id === '' ? '' : String(form.data.category_id),
                        )}
                        onChange={(opt) => form.setData('category_id', opt?.value ? Number(opt.value) : '')}
                    />
                </FormField>
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
