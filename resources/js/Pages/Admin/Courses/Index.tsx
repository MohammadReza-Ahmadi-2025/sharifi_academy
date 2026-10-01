import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Course = {
    id: number;
    title: string;
    price: number;
    is_published: boolean;
    instructor_id: number | null;
    instructor?: { name: string } | null;
};

type Props = {
    courses: { data: Course[] };
    instructors: { id: number; name: string }[];
};

export default function Index({ courses, instructors }: Props) {
    const form = useForm({
        title: '',
        description: '',
        price: 0,
        instructor_id: '' as string | number,
        is_published: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/admin/courses', { onSuccess: () => form.reset() });
    };

    return (
        <PanelLayout
            title="مدیریت دوره‌ها"
            homeHref="/admin"
            links={[
                { href: '/admin', label: 'داشبورد' },
                { href: '/admin/courses', label: 'دوره‌ها' },
                { href: '/admin/books', label: 'کتاب‌ها' },
                { href: '/admin/orders', label: 'سفارش‌ها' },
            ]}
        >
            <Head title="دوره‌ها" />
            <form onSubmit={submit} className="mb-8 grid gap-3 rounded-lg border p-4 md:grid-cols-2">
                <Input placeholder="عنوان" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                <Input
                    type="number"
                    placeholder="قیمت (ریال)"
                    value={form.data.price}
                    onChange={(e) => form.setData('price', Number(e.target.value))}
                />
                <select
                    className="rounded-lg border px-3 py-2 text-sm"
                    value={form.data.instructor_id}
                    onChange={(e) => form.setData('instructor_id', e.target.value)}
                >
                    <option value="">انتخاب استاد</option>
                    {instructors.map((i) => (
                        <option key={i.id} value={i.id}>
                            {i.name}
                        </option>
                    ))}
                </select>
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={form.data.is_published}
                        onChange={(e) => form.setData('is_published', e.target.checked)}
                    />
                    منتشر شده
                </label>
                <Button type="submit" disabled={form.processing}>
                    افزودن دوره
                </Button>
            </form>
            <ul className="space-y-2 text-sm">
                {courses.data.map((course) => (
                    <li key={course.id} className="flex items-center justify-between rounded-lg border px-3 py-2">
                        <span>
                            {course.title} — {course.instructor?.name ?? 'بدون استاد'}
                        </span>
                        <Button
                            variant="ghost"
                            type="button"
                            onClick={() => router.delete(`/admin/courses/${course.id}`)}
                        >
                            حذف
                        </Button>
                    </li>
                ))}
            </ul>
        </PanelLayout>
    );
}
