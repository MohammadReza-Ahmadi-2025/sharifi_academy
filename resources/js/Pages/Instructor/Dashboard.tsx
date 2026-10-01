import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link } from '@inertiajs/react';

type Course = { id: number; title: string; slug: string; is_published: boolean; class_sessions_count: number };

type Props = { courses: Course[] };

export default function Dashboard({ courses }: Props) {
    return (
        <PanelLayout title="پنل استاد" homeHref="/" links={[{ href: '/instructor', label: 'دوره‌های من' }]}>
            <Head title="پنل استاد" />
            <ul className="space-y-2">
                {courses.map((course) => (
                    <li key={course.id} className="flex items-center justify-between rounded-lg border px-3 py-2 text-sm">
                        <span>
                            {course.title} ({course.class_sessions_count} جلسه)
                        </span>
                        <Link href={`/instructor/courses/${course.id}`} className="text-indigo-600 hover:underline">
                            مدیریت
                        </Link>
                    </li>
                ))}
            </ul>
        </PanelLayout>
    );
}
