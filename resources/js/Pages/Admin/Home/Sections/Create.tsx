import {
    emptyHomeSectionForm,
    HomeSectionFormFields,
    type HomeSectionFormData,
} from '@/Components/Admin/Home/HomeSectionFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    options: {
        content_types: Record<string, string>;
        source_types: Record<string, string>;
        section_layouts: Record<string, string>;
        banner_layouts: Record<string, string>;
    };
    lookups: {
        banners: { id: number; title: string; image_url?: string | null }[];
        product_categories: { id: number; title: string }[];
        course_categories: { id: number; title: string }[];
        brands: { id: number; title: string }[];
    };
};

export default function HomeSectionsCreate({ options, lookups }: Props) {
    const form = useForm<HomeSectionFormData>(emptyHomeSectionForm());

    return (
        <AdminLayout>
            <Head title="ایجاد سکشن صفحه اصلی" />
            <PageHeader title="ایجاد سکشن" description="سکشن جدید برای صفحه خانه" backHref="/admin/home-sections" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/home-sections');
                }}
                className="mt-6 space-y-6"
            >
                <HomeSectionFormFields
                    data={form.data}
                    errors={form.errors}
                    options={options}
                    lookups={lookups}
                    processing={form.processing}
                    onChange={(patch) => form.setData({ ...form.data, ...patch })}
                    onTabsChange={(tabs) => form.setData('tabs', tabs)}
                />
                <AdminButton type="submit" disabled={form.processing}>ذخیره سکشن</AdminButton>
            </form>
        </AdminLayout>
    );
}
