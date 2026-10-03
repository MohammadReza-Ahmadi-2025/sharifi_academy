import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { findSelectOption } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function SkillEdit({ skill, categoryOptions }: { skill: { id: number; name: string; category_id: number | null }; categoryOptions: { value: number; label: string }[] }) {
    const form = useForm({ name: skill.name, category_id: skill.category_id ?? ('' as const) });
    return (
        <AdminLayout>
            <Head title={`ویرایش ${skill.name}`} />
            <PageHeader title={`ویرایش مهارت: ${skill.name}`} backHref="/admin/skills" />
            <form onSubmit={(e) => { e.preventDefault(); form.put(`/admin/skills/${skill.id}`); }} className="mt-6 max-w-md space-y-4">
                <FormField label="نام" error={form.errors.name} required><Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} /></FormField>
                <FormField label="دسته دوره" error={form.errors.category_id}>
                    <Select
                        isClearable
                        placeholder="بدون دسته"
                        options={categoryOptions.map((o) => ({ value: String(o.value), label: o.label }))}
                        value={findSelectOption(
                            categoryOptions.map((o) => ({ value: String(o.value), label: o.label })),
                            form.data.category_id === '' || form.data.category_id === null ? '' : String(form.data.category_id),
                        )}
                        onChange={(opt) => form.setData('category_id', opt?.value ? Number(opt.value) : '')}
                    />
                </FormField>
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
