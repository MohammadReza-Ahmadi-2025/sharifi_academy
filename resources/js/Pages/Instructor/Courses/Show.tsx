import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Session = {
    id: number;
    title: string;
    starts_at: string;
    ends_at: string | null;
    adobe_connect_url: string | null;
};

type Props = {
    course: { id: number; title: string; class_sessions: Session[] };
};

export default function Show({ course }: Props) {
    const form = useForm({
        title: '',
        starts_at: '',
        ends_at: '',
        adobe_connect_url: '',
        notes: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(`/instructor/courses/${course.id}/sessions`, { onSuccess: () => form.reset() });
    };

    return (
        <PanelLayout
            title={course.title}
            homeHref="/instructor"
            links={[
                { href: '/instructor', label: 'دوره‌ها' },
                { href: `/instructor/courses/${course.id}`, label: 'جلسات' },
                { href: `/instructor/courses/${course.id}/roster`, label: 'ثبت‌نام‌شدگان' },
            ]}
        >
            <Head title={course.title} />
            <form onSubmit={submit} className="mb-6 grid gap-3 rounded-lg border p-4 md:grid-cols-2">
                <Input placeholder="عنوان جلسه" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                <PersianDatePicker
                    includeTime
                    value={form.data.starts_at}
                    placeholder="زمان شروع جلسه"
                    onChange={(value) => form.setData('starts_at', value)}
                />
                <Input
                    className="md:col-span-2"
                    placeholder="لینک Adobe Connect"
                    value={form.data.adobe_connect_url}
                    onChange={(e) => form.setData('adobe_connect_url', e.target.value)}
                />
                <Button type="submit">افزودن جلسه</Button>
            </form>
            <ul className="space-y-2 text-sm">
                {course.class_sessions.map((session) => (
                    <li key={session.id} className="flex items-center justify-between rounded-lg border px-3 py-2">
                        <span>{session.title}</span>
                        <Button variant="ghost" type="button" onClick={() => router.delete(`/instructor/sessions/${session.id}`)}>
                            حذف
                        </Button>
                    </li>
                ))}
            </ul>
            <Link href={`/instructor/courses/${course.id}/roster`} className="mt-6 inline-block text-indigo-600">
                مشاهده لیست دانشجویان
            </Link>
        </PanelLayout>
    );
}
