import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link } from '@inertiajs/react';

type Enrollment = {
    id: number;
    course: { id: number; title: string; slug: string };
    sessions: {
        id: number;
        title: string;
        starts_at: string | null;
        adobe_connect_url: string | null;
    }[];
};

type Book = { id: number; title: string; slug: string };

type Props = { enrollments: Enrollment[]; books: Book[] };

export default function Dashboard({ enrollments, books }: Props) {
    return (
        <PanelLayout
            title="پنل دانشجو"
            homeHref="/"
            links={[{ href: '/student/dashboard', label: 'دوره‌های من' }]}
        >
            <Head title="پنل دانشجو" />
            <h2 className="mb-4 text-lg font-semibold">دوره‌های فعال</h2>
            {enrollments.length === 0 ? (
                <p className="text-sm text-zinc-600">هنوز در دوره‌ای ثبت‌نام نکرده‌اید.</p>
            ) : (
                <div className="space-y-6">
                    {enrollments.map((enrollment) => (
                        <div key={enrollment.id} className="rounded-lg border p-4">
                            <h3 className="font-medium">{enrollment.course.title}</h3>
                            <ul className="mt-3 space-y-2 text-sm">
                                {enrollment.sessions.map((session) => (
                                    <li key={session.id} className="flex flex-wrap items-center justify-between gap-2 border-t pt-2">
                                        <span>
                                            {session.title}
                                            {session.starts_at && (
                                                <span className="ms-2 text-zinc-500">
                                                    {new Date(session.starts_at).toLocaleString('fa-IR')}
                                                </span>
                                            )}
                                        </span>
                                        {session.adobe_connect_url && (
                                            <a
                                                href={session.adobe_connect_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="rounded-lg bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700"
                                            >
                                                ورود به Adobe Connect
                                            </a>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>
            )}

            <h2 className="mb-4 mt-10 text-lg font-semibold">کتاب‌های خریداری‌شده</h2>
            {books.length === 0 ? (
                <p className="text-sm text-zinc-600">کتابی خریداری نشده است.</p>
            ) : (
                <ul className="space-y-2">
                    {books.map((book) => (
                        <li key={book.id} className="flex items-center justify-between rounded-lg border px-3 py-2 text-sm">
                            <span>{book.title}</span>
                            <Link
                                href={`/student/books/${book.id}/download`}
                                className="text-indigo-600 hover:underline"
                            >
                                دانلود PDF
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </PanelLayout>
    );
}
