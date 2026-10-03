import { BannerFormFields, type BannerFormData } from '@/Components/Admin/Home/BannerFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { createExistingFileUploadItem, type ExistingFilePayload } from '@/Components/shared/file-upload';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    banner: BannerFormData & { id: number; image: ExistingFilePayload | null };
    locationOptions: Record<string, string>;
};

export default function HomeBannersEdit({ banner, locationOptions }: Props) {
    const [image, setImage] = useGalleryUploadState(
        banner.image ? [createExistingFileUploadItem(banner.image)] : [],
    );

    const form = useForm<BannerFormData>({
        title: banner.title,
        subtitle: banner.subtitle,
        button_text: banner.button_text,
        button_link: banner.button_link,
        location: banner.location,
        starts_at: banner.starts_at,
        ends_at: banner.ends_at,
    });

    return (
        <AdminLayout>
            <Head title={`ویرایش بنر #${banner.id}`} />
            <PageHeader title={`ویرایش بنر`} backHref="/admin/home-banners" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form
                        .transform((data) => withGalleryPayload(data, image, { field: 'image' }))
                        .put(`/admin/home-banners/${banner.id}`, { forceFormData: true, preserveScroll: true });
                }}
                className="mt-6 space-y-6"
            >
                <BannerFormFields
                    data={form.data}
                    errors={form.errors}
                    locationOptions={locationOptions}
                    processing={form.processing}
                    imageItems={image}
                    onImageChange={setImage}
                    onChange={(patch) => form.setData({ ...form.data, ...patch })}
                    errorImage={form.errors.image as string | undefined}
                />
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
