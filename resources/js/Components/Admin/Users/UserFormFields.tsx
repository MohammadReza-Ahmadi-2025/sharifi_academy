import { UserRolesPicker, type RoleOption } from '@/Components/Admin/Users/UserRolesPicker';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';

export type UserFormData = {
    name: string;
    family: string;
    mobile: string;
    email: string;
    gender: string;
    status: number;
    roles: string[];
};

type Props = {
    data: UserFormData;
    errors: Partial<Record<keyof UserFormData | 'roles.0', string>>;
    roleOptions: RoleOption[];
    processing: boolean;
    onChange: <K extends keyof UserFormData>(key: K, value: UserFormData[K]) => void;
};

export function UserFormFields({ data, errors, roleOptions, processing, onChange }: Props) {
    const genderOptions = mapToSelectOptions([
        { value: 'male', label: 'مرد' },
        { value: 'female', label: 'زن' },
    ]);
    const statusOptions = mapToSelectOptions([
        { value: '1', label: 'فعال' },
        { value: '0', label: 'غیرفعال' },
    ]);

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
                    <FormField label="جنسیت" error={errors.gender}>
                        <Select
                            isDisabled={processing}
                            isClearable
                            placeholder="انتخاب کنید..."
                            options={genderOptions}
                            value={findSelectOption(genderOptions, data.gender)}
                            onChange={(option) => onChange('gender', option?.value ?? '')}
                        />
                    </FormField>
                    <FormField label="وضعیت" required error={errors.status as string}>
                        <Select
                            isDisabled={processing}
                            options={statusOptions}
                            value={findSelectOption(statusOptions, data.status)}
                            onChange={(option) => onChange('status', Number(option?.value ?? 1))}
                        />
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
        </div>
    );
}
