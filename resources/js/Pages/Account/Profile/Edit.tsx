import { SiteFormField } from '@/Components/Account/SiteFormField';
import { SiteInput } from '@/Components/Account/SiteInput';
import { UserAvatarField } from '@/Components/Admin/Users/UserAvatarField';
import { withAvatarPayload } from '@/Components/Admin/Users/user-form-submit';
import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { ProfileExtendedFields } from '@/Components/Profile/ProfileExtendedFields';
import { useFileUploadState } from '@/Components/shared/FileUploader';
import AccountLayout from '@/Layouts/AccountLayout';
import { extendedToFormDefaults, type LocationOptions, type ProfileExtendedData } from '@/types/profile-extended';
import { Head, useForm, usePage } from '@inertiajs/react';
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
    createdAt: string | null;
    roleLabels: string[];
    extended: ProfileExtendedData;
};

type Props = {
    profile: ProfileData;
    locations: LocationOptions;
};

function VerificationPill({ verified, label }: { verified: boolean; label: string }) {
    return (
        <span
            className={`mt-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-medium ${
                verified ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
            }`}
        >
            {verified ? `${label} تأیید شده` : `${label} تأیید نشده`}
        </span>
    );
}

function SiteCard({ title, children, className = '' }: { title?: string; children: React.ReactNode; className?: string }) {
    return (
        <section className={`rounded-2xl border border-secondary bg-surface p-5 shadow-sm md:p-6 ${className}`}>
            {title ? <h2 className="text-lg font-semibold text-text">{title}</h2> : null}
            {children}
        </section>
    );
}

export default function AccountProfileEdit({ profile, locations }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };
    const [avatarFiles, setAvatarFiles] = useFileUploadState();
    const [removeAvatar, setRemoveAvatar] = useState(false);

    const displayName = [profile.name, profile.family].filter(Boolean).join(' ') || 'کاربر';

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
        infoForm.put('/profile', { preserveScroll: true, forceFormData: true });
    };

    return (
        <AccountLayout title="پروفایل من" description="مدیریت اطلاعات شخصی و آواتار">
            <Head title="پروفایل من" />

            {flash?.success ? (
                <p className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    {flash.success}
                </p>
            ) : null}

            <SiteCard className="!p-0 overflow-hidden">
                <div className="bg-primary px-6 py-8 text-on-primary md:px-8">
                    <div className="flex flex-col items-center gap-4 sm:flex-row sm:items-end">
                        {profile.avatar?.url ? (
                            <img
                                src={profile.avatar.url}
                                alt={profile.avatar.alt ?? displayName}
                                className="h-24 w-24 rounded-full object-cover ring-4 ring-white/30"
                            />
                        ) : (
                            <div className="flex h-24 w-24 items-center justify-center rounded-full bg-white/20 text-2xl font-bold">
                                {displayName.charAt(0)}
                            </div>
                        )}
                        <div className="text-center sm:text-right">
                            <p className="text-xl font-bold">{displayName}</p>
                            {profile.extended.headline ? (
                                <p className="mt-1 text-sm opacity-95">{profile.extended.headline}</p>
                            ) : null}
                            <p className="mt-1 text-sm opacity-90">
                                {profile.roleLabels.length > 0 ? profile.roleLabels.join(' · ') : 'عضو آکادمی'}
                            </p>
                            <p className="mt-2 text-xs opacity-80">
                                عضو از {formatPersianDateTime(profile.createdAt, 'long')}
                            </p>
                        </div>
                    </div>
                </div>
            </SiteCard>

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

                <SiteCard title="اطلاعات شخصی">
                    <p className="mt-1 text-sm text-neutral-600">
                        با تغییر موبایل یا ایمیل، وضعیت تأیید تا ورود مجدد به‌روز می‌شود.
                    </p>
                    <div className="mt-5 space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <SiteFormField label="نام" htmlFor="name" required error={infoForm.errors.name}>
                                <SiteInput
                                    id="name"
                                    value={infoForm.data.name}
                                    onChange={(e) => infoForm.setData('name', e.target.value)}
                                    disabled={infoForm.processing}
                                />
                            </SiteFormField>
                            <SiteFormField label="نام خانوادگی" htmlFor="family" error={infoForm.errors.family}>
                                <SiteInput
                                    id="family"
                                    value={infoForm.data.family}
                                    onChange={(e) => infoForm.setData('family', e.target.value)}
                                    disabled={infoForm.processing}
                                />
                            </SiteFormField>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <SiteFormField
                                label="موبایل"
                                htmlFor="mobile"
                                required
                                error={infoForm.errors.mobile}
                                hint="با 09 شروع شود"
                            >
                                <SiteInput
                                    id="mobile"
                                    type="tel"
                                    dir="ltr"
                                    className="text-left font-mono"
                                    value={infoForm.data.mobile}
                                    onChange={(e) => infoForm.setData('mobile', e.target.value)}
                                    disabled={infoForm.processing}
                                />
                                <VerificationPill verified={profile.mobileVerified} label="موبایل" />
                            </SiteFormField>
                            <SiteFormField label="ایمیل" htmlFor="email" error={infoForm.errors.email}>
                                <SiteInput
                                    id="email"
                                    type="email"
                                    dir="ltr"
                                    className="text-left"
                                    value={infoForm.data.email}
                                    onChange={(e) => infoForm.setData('email', e.target.value)}
                                    disabled={infoForm.processing}
                                />
                                <VerificationPill verified={profile.emailVerified} label="ایمیل" />
                            </SiteFormField>
                        </div>

                        <SiteFormField label="جنسیت" htmlFor="gender" error={infoForm.errors.gender}>
                            <select
                                id="gender"
                                value={infoForm.data.gender}
                                onChange={(e) => infoForm.setData('gender', e.target.value)}
                                disabled={infoForm.processing}
                                className="flex h-11 w-full rounded-xl border border-secondary bg-surface px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            >
                                <option value="">انتخاب کنید</option>
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                            </select>
                        </SiteFormField>
                    </div>
                </SiteCard>

                <SiteCard title="رزومه و اطلاعات تکمیلی">
                    <p className="mt-1 text-sm text-neutral-600">
                        این بخش در پروفایل عمومی شما (در صورت فعال‌سازی نمایش عمومی) قابل مشاهده است.
                    </p>
                    <div className="mt-5">
                        <ProfileExtendedFields
                            variant="site"
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
                </SiteCard>

                <button
                    type="submit"
                    disabled={infoForm.processing}
                    className="rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-on-primary transition hover:bg-primary-hover disabled:opacity-60"
                >
                    ذخیره اطلاعات
                </button>
            </form>
        </AccountLayout>
    );
}
