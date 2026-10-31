import { CertificateDocument } from '@/Components/Certificates/CertificateDocument';
import StudentLayout from '@/Layouts/StudentLayout';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

type Props = {
    certificate: {
        id: number;
        code: string;
        title: string;
        issued_at: string | null;
        course_title: string | null;
        student_name: string;
        verify_url: string;
    };
};

export default function StudentCertificateShow({ certificate }: Props) {
    const { app } = usePage<PageProps>().props;

    return (
        <StudentLayout title={certificate.title} description="گواهینامه پایان دوره" contentVariant="plain">
            <Head title={certificate.title} />

            <div className="no-print mb-5 flex flex-wrap items-center justify-between gap-3">
                <Link
                    href="/student/certificates"
                    className="text-sm font-semibold text-primary hover:text-primary-hover"
                >
                    ← گواهینامه‌ها
                </Link>
                <div className="flex flex-wrap gap-2">
                    <a
                        href={certificate.verify_url}
                        target="_blank"
                        rel="noreferrer"
                        className="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700"
                    >
                        لینک اعتبارسنجی
                    </a>
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="rounded-xl bg-primary px-3.5 py-2 text-sm font-semibold text-on-primary"
                    >
                        چاپ / PDF
                    </button>
                </div>
            </div>

            <div className="certificate-print-root">
                <CertificateDocument
                    title={certificate.title}
                    studentName={certificate.student_name}
                    courseTitle={certificate.course_title}
                    code={certificate.code}
                    issuedAt={certificate.issued_at}
                    verifyUrl={certificate.verify_url}
                    appName={app.name}
                />
            </div>
        </StudentLayout>
    );
}
