import {
    HomeSectionFormFields,
    sectionToFormData,
    type HomeSectionFormData,
} from '@/Components/Admin/Home/HomeSectionFormFields';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { AdminFormErrors } from '@/Components/Admin/shared/ui/admin-form-errors';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { prepareHomeSectionFormPayload } from '@/Components/Admin/Home/home-section-form-payload';
import {
    homeSectionFormFromServer,
    homeSectionServerFingerprint,
} from '@/Components/Admin/Home/home-section-form-sync';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useRef } from 'react';

type Props = {
    section: HomeSectionFormData & { id: number };
    options: {
        content_types: Record<string, string>;
        source_types: Record<string, string>;
        section_layouts: Record<string, string>;
        banner_layouts: Record<string, string>;
    };
    lookups: {
        product_categories: { id: number; title: string }[];
        course_categories: { id: number; title: string }[];
        brands: { id: number; title: string }[];
    };
};

export default function HomeSectionsEdit({ section, options, lookups }: Props) {
    const form = useForm<HomeSectionFormData>(sectionToFormData(section));
    const serverFingerprint = useMemo(() => homeSectionServerFingerprint(section), [section]);
    const appliedFingerprint = useRef(serverFingerprint);

    useEffect(() => {
        if (appliedFingerprint.current === serverFingerprint) {
            return;
        }
        appliedFingerprint.current = serverFingerprint;
        form.setData(homeSectionFormFromServer(section));
        form.clearErrors();
    }, [serverFingerprint, section]);

    return (
        <AdminLayout>
            <Head title={`ویرایش سکشن: ${section.title}`} />
            <PageHeader title={section.title} description="تنظیم تب‌ها و منبع داده" backHref="/admin/home-sections" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    const payload = prepareHomeSectionFormPayload(form.data);
                    form.transform(() => payload as HomeSectionFormData);
                    form.put(`/admin/home-sections/${section.id}`, {
                        forceFormData: true,
                        preserveScroll: true,
                        onError: () => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        },
                    });
                }}
            >
                {form.recentlySuccessful ? (
                    <p
                        className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-100"
                        role="status"
                    >
                        تغییرات ذخیره شد.
                    </p>
                ) : null}
                <AdminFormErrors errors={form.errors} />
                <HomeSectionFormFields
                    data={form.data}
                    errors={form.errors}
                    options={options}
                    lookups={lookups}
                    processing={form.processing}
                    onChange={(patch) => form.setData((prev) => ({ ...prev, ...patch }))}
                    onTabsChange={(tabs) => form.setData('tabs', tabs)}
                />
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره تغییرات</AdminButton>
                    <AdminButton type="button" variant="outline" href="/admin/home-sections">بازگشت</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
