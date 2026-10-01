import PanelLayout from '@/Layouts/PanelLayout';
import { Head } from '@inertiajs/react';

type Props = {
    course: { id: number; title: string };
    enrollments: { id: number; user: { name: string; email: string } }[];
};

export default function Roster({ course, enrollments }: Props) {
    return (
        <PanelLayout
            title={`ثبت‌نام‌شدگان — ${course.title}`}
            homeHref="/instructor"
            links={[
                { href: '/instructor', label: 'دوره‌ها' },
                { href: `/instructor/courses/${course.id}`, label: 'جلسات' },
                { href: `/instructor/courses/${course.id}/roster`, label: 'ثبت‌نام‌شدگان' },
            ]}
        >
            <Head title="لیست دانشجویان" />
            <ul className="space-y-2 text-sm">
                {enrollments.map((row) => (
                    <li key={row.id} className="rounded-lg border px-3 py-2">
                        {row.user.name} — {row.user.email}
                    </li>
                ))}
            </ul>
        </PanelLayout>
    );
}
