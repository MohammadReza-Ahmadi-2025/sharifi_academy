type Props = {
    title: string;
    studentName: string;
    courseTitle: string | null;
    code: string;
    issuedAt: string | null;
    verifyUrl?: string | null;
    appName?: string;
};

export function CertificateDocument({
    title,
    studentName,
    courseTitle,
    code,
    issuedAt,
    verifyUrl,
    appName = 'Sharifi Academy',
}: Props) {
    const issuedLabel = issuedAt ? new Date(issuedAt).toLocaleDateString('fa-IR') : null;

    return (
        <article className="certificate-sheet relative mx-auto max-w-4xl rounded-[28px] px-8 py-12 text-center md:px-16 md:py-16">
            <p className="text-[11px] font-semibold tracking-[0.28em] text-[rgb(11_31_58)] uppercase">
                {appName}
            </p>
            <div className="certificate-ornament mt-4" />
            <p className="mt-6 text-sm font-medium text-[rgb(176_137_45)]">گواهینامه پایان دوره</p>
            <h1 className="mt-3 text-2xl font-extrabold tracking-tight text-[rgb(11_31_58)] md:text-4xl">
                {title}
            </h1>
            <p className="mt-8 text-sm text-slate-600">این گواهینامه به</p>
            <p className="mt-2 text-xl font-bold text-slate-900 md:text-3xl">{studentName}</p>
            <p className="mx-auto mt-5 max-w-2xl text-sm leading-relaxed text-slate-600 md:text-base">
                بابت گذراندن موفقیت‌آمیز دوره
                {courseTitle ? ` «${courseTitle}» ` : ' '}
                اعطا می‌گردد.
            </p>
            <div className="certificate-ornament mt-8" />
            <div className="mt-8 flex flex-wrap items-end justify-center gap-10 text-xs text-slate-500">
                <div>
                    <p className="font-semibold text-slate-700">تاریخ صدور</p>
                    <p className="mt-1">{issuedLabel ?? '—'}</p>
                </div>
                <div>
                    <p className="font-semibold text-slate-700">کد اعتبارسنجی</p>
                    <p className="mt-1 font-mono tracking-wide" dir="ltr">
                        {code}
                    </p>
                </div>
            </div>
            {verifyUrl ? (
                <p className="mt-6 text-[11px] text-slate-400" dir="ltr">
                    {verifyUrl}
                </p>
            ) : null}
        </article>
    );
}
