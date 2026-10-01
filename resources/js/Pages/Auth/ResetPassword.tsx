import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { email: string; token: string };

export default function ResetPassword({ email, token }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/reset-password');
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-zinc-50 px-4" dir="rtl">
            <Head title="تنظیم رمز جدید" />
            <form onSubmit={submit} className="w-full max-w-md space-y-4 rounded-xl border bg-white p-6">
                <h1 className="text-xl font-semibold">رمز عبور جدید</h1>
                <Input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} />
                {errors.password && <p className="text-sm text-red-600">{errors.password}</p>}
                <Input
                    type="password"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                />
                <Button type="submit" disabled={processing} className="w-full">
                    ذخیره
                </Button>
            </form>
        </div>
    );
}
