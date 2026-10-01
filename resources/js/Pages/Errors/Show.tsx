import AppLayout from '@/Layouts/AppLayout';
import { httpErrorContent } from '@/lib/httpErrorPages';
import { PageProps } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Home, LogIn, RefreshCw } from 'lucide-react';

type Props = PageProps & {
    status: number;
};

export default function Show({ status }: Props) {
    const content = httpErrorContent(status);
    const showLogin = status === 401 || status === 403;
    const showRefresh = status === 419 || status === 500 || status === 503 || status === 429;

    return (
        <AppLayout>
            <Head title={`${status} — ${content.title}`} />

            <div className="mx-auto flex w-full max-w-lg flex-1 flex-col items-center justify-center px-4 py-10 text-center">
                <p className="text-7xl font-extrabold tracking-tight text-primary/20" aria-hidden>
                    {status}
                </p>
                <h1 className="mt-2 text-2xl font-bold text-neutral-black">{content.title}</h1>
                <p className="mt-3 text-sm leading-7 text-neutral-black/60">{content.description}</p>

                <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        href="/"
                        className="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary shadow-md shadow-primary/20 transition hover:bg-primary-hover"
                    >
                        <Home className="size-4" />
                        صفحه اصلی
                    </Link>

                    {showLogin && (
                        <Link
                            href="/login"
                            className="inline-flex items-center gap-2 rounded-full border border-primary/25 bg-neutral-white px-5 py-2.5 text-sm font-semibold text-primary transition hover:bg-primary/5"
                        >
                            <LogIn className="size-4" />
                            ورود / ثبت‌نام
                        </Link>
                    )}

                    {showRefresh && (
                        <button
                            type="button"
                            onClick={() => window.location.reload()}
                            className="inline-flex items-center gap-2 rounded-full border border-secondary bg-neutral-white px-5 py-2.5 text-sm font-semibold text-neutral-black/75 transition hover:bg-secondary/60"
                        >
                            <RefreshCw className="size-4" />
                            بارگذاری مجدد
                        </button>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
