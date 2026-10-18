import { instructorPanelLinks } from '@/lib/instructor-panel-links';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

type CourseRow = {
    id: number;
    title: string;
    status_label: string;
    classes_count: number;
};

type Props = {
    courses: CourseRow[];
    canCreate: boolean;
};

export default function InstructorCoursesIndex({ courses, canCreate }: Props) {
    return (
        <PanelLayout title="دوره‌های من" homeHref="/instructor" links={instructorPanelLinks}>
            <Head title="دوره‌های من" />
            {canCreate && (
                <div className="mb-4 flex justify-end">
                    <Link
                        href="/instructor/courses/create"
                        className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        <Plus className="size-4" aria-hidden />
                        دوره جدید
                    </Link>
                </div>
            )}
            <ul className="divide-y rounded-xl border border-slate-200">
                {courses.map((course) => (
                    <li key={course.id} className="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                        <div>
                            <p className="font-medium">{course.title}</p>
                            <p className="text-xs text-slate-500">{course.status_label}</p>
                        </div>
                        <div className="flex gap-2">
                            <Link href={`/instructor/courses/${course.id}/edit`} className="text-indigo-600 hover:underline">
                                ویرایش
                            </Link>
                            <Link href={`/instructor/courses/${course.id}/classes`} className="text-indigo-600 hover:underline">
                                کلاس‌ها
                            </Link>
                        </div>
                    </li>
                ))}
            </ul>
        </PanelLayout>
    );
}
