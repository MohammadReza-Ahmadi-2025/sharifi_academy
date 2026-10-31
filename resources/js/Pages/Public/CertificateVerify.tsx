import { CertificateDocument } from '@/Components/Certificates/CertificateDocument';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

type Props = {
    certificate: {
        code: string;
        title: string;
        issued_at: string | null;
        course_title: string | null;
        student_name: string;
        valid: boolean;
    };
    seo: CatalogSeo;
    structuredData: {
        credential: Record<string, unknown>;
    };
};

export default function CertificateVerify({ certificate, seo, structuredData }: Props) {
    const { app } = usePage<PageProps>().props;

    return (
        <AppLayout>
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead seo={seo} structuredData={[structuredData.credential]} />
            <div className="mx-auto max-w-5xl px-4 py-10 md:py-14">
                <div className="no-print mb-6 flex flex-wrap items-center justify-between gap-3">
                    <p className="text-sm font-semibold text-emerald-700">گواهینامه معتبر است</p>
                    <button
                        type="button"
                        onClick={() => window.print()}
                        className="rounded-xl bg-primary px-3.5 py-2 text-sm font-semibold text-on-primary"
                    >
                        چاپ / PDF
                    </button>
                </div>
                <div className="certificate-print-root">
                    <CertificateDocument
                        title={certificate.title}
                        studentName={certificate.student_name}
                        courseTitle={certificate.course_title}
                        code={certificate.code}
                        issuedAt={certificate.issued_at}
                        appName={app.name}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
