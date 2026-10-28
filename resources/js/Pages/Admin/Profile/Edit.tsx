import { UserAvatarField } from '@/Components/Admin/Users/UserAvatarField';
import { withAvatarPayload } from '@/Components/Admin/Users/user-form-submit';
import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { ProfileExtendedFields } from '@/Components/Profile/ProfileExtendedFields';
import { useFileUploadState } from '@/Components/shared/FileUploader';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { extendedToFormDefaults, type LocationOptions, type ProfileExtendedData } from '@/types/profile-extended';
import { Head, useForm, usePage } from '@inertiajs/react';
import { AdminFormActions } from '@/Components/Admin/shared/layout/admin-form-page';
import { useState } from 'react';

type ProfileData = {
    name: string;
    family: string | null;
    email: string | null;
    mobile: string;
    gender: 'male' | 'female' | null;
    avatar: { url: string; alt?: string | null; title?: string | null } | null;
    emailVerified: boolean;
    mobileVerified: boolean;
    lastLoginAt: string | null;
    lastLoginIp: string | null;
    createdAt: string | null;
    roles: string[];
    roleLabels: string[];
    extended: ProfileExtendedData;
};

type Props = {
    profile: ProfileData;
    locations: LocationOptions;
};

function VerificationBadge({ verified, label }: { verified: boolean; label: string }) {
    return (
        <Badge variant={verified ? 'success' : 'warning'} className="mt-1">
            {verified ? `${label} تأیید شده` : `${label} تأیید نشده`}
        </Badge>
    );
}

export default function ProfileEdit({ profile, locations }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };
    const [avatarFiles, setAvatarFiles] = useFileUploadState();
    const [removeAvatar, setRemoveAvatar] = useState(false);

    const infoForm = useForm({
        name: profile.name ?? '',
        family: profile.family ?? '',
        mobile: profile.mobile ?? '',
        email: profile.email ?? '',
        gender: profile.gender ?? '',
        ...extendedToFormDefaults(profile.extended),
    });

    const submitInfo = (event: React.FormEvent) => {
        event.preventDefault();
        infoForm.transform((data) => withAvatarPayload(data, avatarFiles, { removeAvatar }));
        infoForm.put('/admin/profile', { preserveScroll: true, forceFormData: true });
    };

    const displayName = [profile.name, profile.family].filter(Boolean).join(' ') || '—';

    return (
        <AdminLayout>
            <Head title="پروفایل من" />

            <PageHeader title="پروفایل من" description="اطلاعات حساب و موبایل" backHref="/admin" />

            {flash?.success ? (
                <p
                    className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                    role="status"
                >
                    {flash.success}
                </p>
            ) : null}

            <div className="mt-6 grid gap-6 lg:grid-cols-3">
                <Card className="lg:col-span-1">
                    <div className="flex flex-col items-center border-b border-slate-100 pb-4 dark:border-slate-800">
                        {profile.avatar?.url ? (
                            <img
                                src={profile.avatar.url}
                                alt={profile.avatar.alt ?? displayName}
                                className="h-20 w-20 rounded-full object-cover ring-2 ring-slate-200 dark:ring-slate-700"
                            />
                        ) : (
                            <div className="flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-lg font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                {displayName.charAt(0)}
                            </div>
                        )}
                        <p className="mt-3 text-sm font-semibold text-slate-900 dark:text-slate-100">{displayName}</p>
                    </div>
                    <h2 className="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">خلاصه حساب</h2>
                    <dl className="mt-4 space-y-3 text-sm">
                        <div>
                            <dt className="text-slate-500 dark:text-slate-400">نقش‌ها</dt>
                            <dd className="mt-0.5 text-slate-900 dark:text-slate-100">
                                {profile.roleLabels.length > 0 ? profile.roleLabels.join('، ') : '—'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-slate-500 dark:text-slate-400">عضویت</dt>
                            <dd className="mt-0.5 text-slate-900 dark:text-slate-100">
                                {formatPersianDateTime(profile.createdAt, 'long')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-slate-500 dark:text-slate-400">آخرین ورود</dt>
                            <dd className="mt-0.5 text-slate-900 dark:text-slate-100">
                                {formatPersianDateTime(profile.lastLoginAt, 'table')}
                            </dd>
                        </div>
                        {profile.lastLoginIp ? (
                            <div>
                                <dt className="text-slate-500 dark:text-slate-400">آی‌پی آخرین ورود</dt>
                                <dd className="mt-0.5 font-mono text-slate-900 dark:text-slate-100" dir="ltr">
                                    {profile.lastLoginIp}
                                </dd>
                            </div>
                        ) : null}
                    </dl>
                </Card>

                <div className="space-y-6 lg:col-span-2">
                    <form onSubmit={submitInfo} className="space-y-6">
                    <UserAvatarField
                        files={avatarFiles}
                        onFilesChange={setAvatarFiles}
                        existing={profile.avatar}
                        removeExisting={removeAvatar}
                        onRemoveExistingChange={setRemoveAvatar}
                        disabled={infoForm.processing}
                        error={infoForm.errors.avatar}
                        showAlt
                    />

                    <Card>
                        <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اطلاعات شخصی</h2>
                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            با تغییر موبایل یا ایمیل، وضعیت تأیید تا ورود مجدد یا تأیید دوباره، به‌روز می‌شود.
                        </p>
                        <div className="mt-4 space-y-4">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField label="نام" htmlFor="name" required error={infoForm.errors.name}>
                                    <AdminInput
                                        id="name"
                                        value={infoForm.data.name}
                                        onChange={(e) => infoForm.setData('name', e.target.value)}
                                        disabled={infoForm.processing}
                                    />
                                </FormField>
                                <FormField label="نام خانوادگی" htmlFor="family" error={infoForm.errors.family}>
                                    <AdminInput
                                        id="family"
                                        value={infoForm.data.family}
                                        onChange={(e) => infoForm.setData('family', e.target.value)}
                                        disabled={infoForm.processing}
                                    />
                                </FormField>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <FormField
                                    label="شماره موبایل"
                                    htmlFor="mobile"
                                    required
                                    error={infoForm.errors.mobile}
                                    hint="فقط اعداد؛ با 09 شروع شود"
                                >
                                    <AdminInput
                                        id="mobile"
                                        type="tel"
                                        inputMode="numeric"
                                        autoComplete="tel"
                                        dir="ltr"
                                        className="text-left font-mono"
                                        placeholder="09123456789"
                                        value={infoForm.data.mobile}
                                        onChange={(e) => infoForm.setData('mobile', e.target.value)}
                                        disabled={infoForm.processing}
                                    />
                                    <VerificationBadge verified={profile.mobileVerified} label="موبایل" />
                                </FormField>

                                <FormField label="ایمیل" htmlFor="email" error={infoForm.errors.email}>
                                    <AdminInput
                                        id="email"
                                        type="email"
                                        dir="ltr"
                                        className="text-left"
                                        autoComplete="email"
                                        value={infoForm.data.email}
                                        onChange={(e) => infoForm.setData('email', e.target.value)}
                                        disabled={infoForm.processing}
                                    />
                                    <VerificationBadge verified={profile.emailVerified} label="ایمیل" />
                                </FormField>
                            </div>

                            <FormField label="جنسیت" htmlFor="gender" error={infoForm.errors.gender}>
                                <select
                                    id="gender"
                                    value={infoForm.data.gender}
                                    onChange={(e) => infoForm.setData('gender', e.target.value)}
                                    disabled={infoForm.processing}
                                    className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="male">مرد</option>
                                    <option value="female">زن</option>
                                </select>
                            </FormField>
                        </div>
                    </Card>

                    <Card>
                        <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">رزومه و اطلاعات تکمیلی</h2>
                        <div className="mt-4">
                            <ProfileExtendedFields
                                variant="admin"
                                data={{
                                    headline: infoForm.data.headline,
                                    bio: infoForm.data.bio,
                                    birth_date: infoForm.data.birth_date,
                                    national_code: infoForm.data.national_code,
                                    province_city_id: infoForm.data.province_city_id,
                                    is_public: infoForm.data.is_public,
                                }}
                                errors={infoForm.errors}
                                locations={locations}
                                initialProvinceId={profile.extended.provinceId}
                                processing={infoForm.processing}
                                isVerified={profile.extended.isVerified}
                                onChange={(key, value) => infoForm.setData(key, value)}
                            />
                        </div>
                    </Card>

                    <AdminFormActions>
                        <AdminButton type="submit" disabled={infoForm.processing}>ذخیره اطلاعات</AdminButton>
                    </AdminFormActions>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
