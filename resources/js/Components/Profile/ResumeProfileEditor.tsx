import { UserAvatarField } from '@/Components/Admin/Users/UserAvatarField';
import { withAvatarPayload } from '@/Components/Admin/Users/user-form-submit';
import { ProfileExtendedFields } from '@/Components/Profile/ProfileExtendedFields';
import { useFileUploadState } from '@/Components/shared/FileUploader';
import { extendedToFormDefaults, type LocationOptions, type ProfileExtendedData } from '@/types/profile-extended';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

export type ResumeEditableProfile = {
    name: string;
    family: string | null;
    email: string | null;
    mobile: string;
    gender: 'male' | 'female' | null;
    avatar: { url: string; alt?: string | null; title?: string | null } | null;
    mobileVerified: boolean;
    emailVerified: boolean;
    extended: ProfileExtendedData;
};

type Props = {
    profile: ResumeEditableProfile;
    locations: LocationOptions;
};

export function ResumeProfileEditor({ profile, locations }: Props) {
    const [avatarFiles, setAvatarFiles] = useFileUploadState();
    const [removeAvatar, setRemoveAvatar] = useState(false);
    const [open, setOpen] = useState(false);

    const form = useForm({
        name: profile.name ?? '',
        family: profile.family ?? '',
        mobile: profile.mobile ?? '',
        email: profile.email ?? '',
        gender: profile.gender ?? '',
        ...extendedToFormDefaults(profile.extended),
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.transform((data) => withAvatarPayload(data, avatarFiles, { removeAvatar }));
        form.put('/profile', {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                setOpen(false);
                setAvatarFiles([]);
                setRemoveAvatar(false);
            },
        });
    };

    return (
        <section className="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-base font-semibold text-slate-900">پروفایل من</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        نام، آواتار، تیتر و بیو که در رزومه نمایش داده می‌شود.
                    </p>
                </div>
                <button
                    type="button"
                    onClick={() => setOpen((value) => !value)}
                    className="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                >
                    {open ? 'بستن فرم' : 'ویرایش پروفایل'}
                </button>
            </div>

            {!open ? (
                <div className="mt-4 flex flex-wrap items-center gap-4">
                    {profile.avatar?.url ? (
                        <img
                            src={profile.avatar.url}
                            alt={profile.avatar.alt ?? profile.name}
                            className="size-16 rounded-full object-cover ring-2 ring-slate-100"
                        />
                    ) : (
                        <div className="flex size-16 items-center justify-center rounded-full bg-slate-100 text-lg font-bold text-slate-500">
                            {(profile.name || '؟').slice(0, 1)}
                        </div>
                    )}
                    <div className="min-w-0 text-sm">
                        <p className="font-semibold text-slate-900">
                            {[profile.name, profile.family].filter(Boolean).join(' ')}
                        </p>
                        {profile.extended.headline ? (
                            <p className="mt-0.5 text-slate-600">{profile.extended.headline}</p>
                        ) : (
                            <p className="mt-0.5 text-slate-400">تیتر حرفه‌ای ثبت نشده</p>
                        )}
                    </div>
                </div>
            ) : (
                <form onSubmit={submit} className="mt-5 space-y-5">
                    <UserAvatarField
                        files={avatarFiles}
                        onFilesChange={setAvatarFiles}
                        existing={profile.avatar}
                        removeExisting={removeAvatar}
                        onRemoveExistingChange={setRemoveAvatar}
                        disabled={form.processing}
                        error={form.errors.avatar}
                        showAlt
                    />

                    <div className="grid gap-4 sm:grid-cols-2">
                        <label className="block text-sm">
                            <span className="mb-1 block text-slate-600">نام *</span>
                            <input
                                required
                                className="h-10 w-full rounded-lg border border-slate-200 px-3"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                disabled={form.processing}
                            />
                            {form.errors.name ? <p className="mt-1 text-xs text-rose-600">{form.errors.name}</p> : null}
                        </label>
                        <label className="block text-sm">
                            <span className="mb-1 block text-slate-600">نام خانوادگی</span>
                            <input
                                className="h-10 w-full rounded-lg border border-slate-200 px-3"
                                value={form.data.family}
                                onChange={(e) => form.setData('family', e.target.value)}
                                disabled={form.processing}
                            />
                            {form.errors.family ? <p className="mt-1 text-xs text-rose-600">{form.errors.family}</p> : null}
                        </label>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <label className="block text-sm">
                            <span className="mb-1 block text-slate-600">موبایل *</span>
                            <input
                                required
                                type="tel"
                                dir="ltr"
                                className="h-10 w-full rounded-lg border border-slate-200 px-3 text-left font-mono"
                                value={form.data.mobile}
                                onChange={(e) => form.setData('mobile', e.target.value)}
                                disabled={form.processing}
                            />
                            {form.errors.mobile ? <p className="mt-1 text-xs text-rose-600">{form.errors.mobile}</p> : null}
                        </label>
                        <label className="block text-sm">
                            <span className="mb-1 block text-slate-600">ایمیل</span>
                            <input
                                type="email"
                                dir="ltr"
                                className="h-10 w-full rounded-lg border border-slate-200 px-3 text-left"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                                disabled={form.processing}
                            />
                            {form.errors.email ? <p className="mt-1 text-xs text-rose-600">{form.errors.email}</p> : null}
                        </label>
                    </div>

                    <label className="block text-sm">
                        <span className="mb-1 block text-slate-600">جنسیت</span>
                        <select
                            className="h-10 w-full rounded-lg border border-slate-200 px-3"
                            value={form.data.gender}
                            onChange={(e) => form.setData('gender', e.target.value)}
                            disabled={form.processing}
                        >
                            <option value="">انتخاب کنید</option>
                            <option value="male">مرد</option>
                            <option value="female">زن</option>
                        </select>
                    </label>

                    <ProfileExtendedFields
                        variant="site"
                        data={{
                            headline: form.data.headline,
                            bio: form.data.bio,
                            birth_date: form.data.birth_date,
                            national_code: form.data.national_code,
                            province_city_id: form.data.province_city_id,
                            is_public: form.data.is_public,
                        }}
                        errors={form.errors}
                        locations={locations}
                        initialProvinceId={profile.extended.provinceId}
                        processing={form.processing}
                        isVerified={profile.extended.isVerified}
                        onChange={(key, value) => form.setData(key, value)}
                    />

                    <div className="flex flex-wrap gap-2">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                        >
                            ذخیره پروفایل
                        </button>
                        <button
                            type="button"
                            onClick={() => setOpen(false)}
                            className="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50"
                        >
                            انصراف
                        </button>
                    </div>
                </form>
            )}
        </section>
    );
}
