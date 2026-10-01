import { UserRolesPicker, type RoleOption } from '@/Components/Admin/Users/UserRolesPicker';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';

export type UserFormData = {
    name: string;
    family: string;
    mobile: string;
    email: string;
    gender: string;
    status: number;
    password: string;
    roles: string[];
};

type Props = {
    data: UserFormData;
    errors: Partial<Record<keyof UserFormData | 'roles.0', string>>;
    roleOptions: RoleOption[];
    processing: boolean;
    onChange: <K extends keyof UserFormData>(key: K, value: UserFormData[K]) => void;
    showPassword?: boolean;
    passwordRequired?: boolean;
};

export function UserFormFields({
    data,
    errors,
    roleOptions,
    processing,
    onChange,
    showPassword = true,
    passwordRequired = false,
}: Props) {
    return (
        <div className="space-y-6">
            <Card className="space-y-4">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اطلاعات کاربر</h2>
                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField label="نام" htmlFor="name" required error={errors.name}>
                        <AdminInput
                            id="name"
                            value={data.name}
                            onChange={(e) => onChange('name', e.target.value)}
                            disabled={processing}
                        />
                    </FormField>
                    <FormField label="نام خانوادگی" htmlFor="family" error={errors.family}>
                        <AdminInput
                            id="family"
                            value={data.family}
                            onChange={(e) => onChange('family', e.target.value)}
                            disabled={processing}
                        />
                    </FormField>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField label="موبایل" htmlFor="mobile" required error={errors.mobile}>
                        <AdminInput
                            id="mobile"
                            type="tel"
                            dir="ltr"
                            className="text-left font-mono"
                            value={data.mobile}
                            onChange={(e) => onChange('mobile', e.target.value)}
                            disabled={processing}
                        />
                    </FormField>
                    <FormField label="ایمیل" htmlFor="email" error={errors.email}>
                        <AdminInput
                            id="email"
                            type="email"
                            dir="ltr"
                            className="text-left"
                            value={data.email}
                            onChange={(e) => onChange('email', e.target.value)}
                            disabled={processing}
                        />
                    </FormField>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField label="جنسیت" htmlFor="gender" error={errors.gender}>
                        <select
                            id="gender"
                            value={data.gender}
                            onChange={(e) => onChange('gender', e.target.value)}
                            disabled={processing}
                            className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        >
                            <option value="">انتخاب کنید</option>
                            <option value="male">مرد</option>
                            <option value="female">زن</option>
                        </select>
                    </FormField>
                    <FormField label="وضعیت" htmlFor="status" required error={errors.status as string}>
                        <select
                            id="status"
                            value={String(data.status)}
                            onChange={(e) => onChange('status', Number(e.target.value))}
                            disabled={processing}
                            className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                        >
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                    </FormField>
                </div>
            </Card>

            <Card className="space-y-4">
                <UserRolesPicker
                    roleOptions={roleOptions}
                    selected={data.roles}
                    onChange={(roles) => onChange('roles', roles)}
                    error={errors.roles}
                    disabled={processing}
                />
            </Card>

            {showPassword ? (
                <Card className="space-y-4">
                    <FormField
                        label={passwordRequired ? 'رمز عبور' : 'رمز عبور جدید (اختیاری)'}
                        htmlFor="password"
                        required={passwordRequired}
                        error={errors.password}
                        hint={passwordRequired ? undefined : 'در صورت خالی بودن تغییر نمی‌کند.'}
                    >
                        <AdminInput
                            id="password"
                            type="password"
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => onChange('password', e.target.value)}
                            disabled={processing}
                        />
                    </FormField>
                </Card>
            ) : null}
        </div>
    );
}
