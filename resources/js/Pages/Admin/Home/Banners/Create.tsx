import { BannerFormFields, type BannerFormData } from '@/Components/Admin/Home/BannerFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    locationOptions: Record<string, string>;
};

export default function HomeBannersCreate({ locationOptions }: Props) {
    const [image, setImage] = useGalleryUploadState();
    const form = useForm<BannerFormData>({
        title: '',
        subtitle: '',
        button_text: '',
        button_link: '',
        location: 'section',
        starts_at: '',
        ends_at: '',
    });

    return (
        <AdminLayout>
            <Head title="ایجاد بنر" />
            <PageHeader title="ایجاد بنر" backHref="/admin/home-banners" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form
                        .transform((data) => withGalleryPayload(data, image, { field: 'image' }))
                        .post('/admin/home-banners', { forceFormData: true });
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
