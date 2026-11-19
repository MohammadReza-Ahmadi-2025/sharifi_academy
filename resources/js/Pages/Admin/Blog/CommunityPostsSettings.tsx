import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Settings = {
    posts_meta_title: string;
    posts_meta_description: string;
    posts_meta_keywords: string;
    posts_header_title: string;
    posts_subheader_title: string;
};

type Props = {
    settings: Settings;
};

export default function CommunityPostsSettings({ settings }: Props) {
    const form = useForm({ ...settings });

    return (
        <AdminLayout>
            <Head title="تنظیمات سئوی پست‌ها" />
            <PageHeader
                title="تنظیمات سئوی پست‌ها"
                description="متا و عنوان صفحه فید پست‌های کاربری (/posts)"
                backHref="/admin/posts"
            />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put('/admin/posts/feed-settings');
                }}
            >
                <Card className="w-full space-y-4 p-6">
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">نمایش صفحه</h2>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="عنوان هدر" error={form.errors.posts_header_title}>
                            <AdminInput
                                value={form.data.posts_header_title}
                                disabled={form.processing}
                                onChange={(e) => form.setData('posts_header_title', e.target.value)}
                            />
                        </FormField>
                        <FormField
                            label="زیرعنوان"
                            className="md:col-span-2"
                            error={form.errors.posts_subheader_title}
                        >
                            <Textarea
                                plain
                                rows={2}
                                value={form.data.posts_subheader_title}
                                disabled={form.processing}
                                onChange={(e) => form.setData('posts_subheader_title', e.target.value)}
                            />
                        </FormField>
                    </div>
                </Card>

                <Card className="w-full space-y-4 p-6">
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">متا سئو</h2>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="عنوان متا (title)" error={form.errors.posts_meta_title}>
                            <AdminInput
                                value={form.data.posts_meta_title}
                                disabled={form.processing}
                                onChange={(e) => form.setData('posts_meta_title', e.target.value)}
                            />
                        </FormField>
                        <FormField
                            label="کلمات کلیدی"
                            error={form.errors.posts_meta_keywords}
                            hint="با ویرگول جدا کنید"
                        >
                            <AdminInput
                                value={form.data.posts_meta_keywords}
                                disabled={form.processing}
                                onChange={(e) => form.setData('posts_meta_keywords', e.target.value)}
                            />
                        </FormField>
                        <FormField
                            label="توضیحات متا (description)"
                            className="md:col-span-2"
                            error={form.errors.posts_meta_description}
                        >
                            <Textarea
                                plain
                                rows={3}
                                value={form.data.posts_meta_description}
                                disabled={form.processing}
                                onChange={(e) => form.setData('posts_meta_description', e.target.value)}
                            />
                        </FormField>
                    </div>
                </Card>

                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>
                        ذخیره تنظیمات
                    </AdminButton>
                    <AdminButton type="button" variant="outline" href="/admin/posts">
                        انصراف
                    </AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
