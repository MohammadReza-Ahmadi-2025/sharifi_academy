import { Button } from '@/Components/ui/Button';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';

type Props = {
    course: {
        id: number;
        title: string;
        slug: string;
        price: number;
        description?: string | null;
        instructor?: { name: string } | null;
        class_sessions: { id: number; title: string; starts_at: string }[];
    };
};

export default function CourseShow({ course }: Props) {
    const { auth } = usePage<PageProps>().props;

    const checkout = () => {
        router.post('/checkout', { item_type: 'course', item_id: course.id });
    };

    return (
        <AppLayout>
            <Head title={course.title} />
            <h1 className="text-2xl font-bold">{course.title}</h1>
            {course.instructor && <p className="mt-2 text-sm text-zinc-600">استاد: {course.instructor.name}</p>}
            <p className="mt-4 text-zinc-700">{course.description}</p>
            <p className="mt-4 font-semibold text-indigo-700">{course.price.toLocaleString('fa-IR')} ریال</p>
            {auth.user ? (
                <Button className="mt-6" onClick={checkout}>
                    ثبت‌نام / پرداخت
                </Button>
            ) : (
                <p className="mt-6 text-sm text-zinc-600">برای ثبت‌نام ابتدا وارد شوید.</p>
            )}
            <h2 className="mb-3 mt-10 text-lg font-semibold">برنامه جلسات (عمومی)</h2>
            <ul className="space-y-2 text-sm">
                {course.class_sessions.map((s) => (
                    <li key={s.id} className="rounded-lg border px-3 py-2">
                        {s.title} — {new Date(s.starts_at).toLocaleString('fa-IR')}
                    </li>
                ))}
            </ul>
        </AppLayout>
    );
}
