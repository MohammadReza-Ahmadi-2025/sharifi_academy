import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors, status } = useForm({ email: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-zinc-50 px-4" dir="rtl">
            <Head title="فراموشی رمز" />
            <form onSubmit={submit} className="w-full max-w-md space-y-4 rounded-xl border bg-white p-6">
                <h1 className="text-xl font-semibold">بازیابی رمز عبور</h1>
                {status && <p className="text-sm text-emerald-700">{status}</p>}
                <Input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} />
                {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                <Button type="submit" disabled={processing} className="w-full">
                    ارسال لینک
                </Button>
                <Link href="/login" className="block text-center text-sm text-indigo-600">
                    بازگشت
                </Link>
            </form>
        </div>
    );
}
