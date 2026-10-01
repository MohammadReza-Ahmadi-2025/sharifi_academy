import { FlashMessages } from '@/Components/FlashMessages';
import { SiteHeader } from '@/Components/SiteHeader';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
    fullWidth?: boolean;
}>;

export default function AppLayout({ children, fullWidth = false }: Props) {
    const { app } = usePage<PageProps>().props;

    return (
        <div className="flex min-h-screen flex-col bg-surface-muted">
            <SiteHeader />

            <main
                className={`flex flex-1 flex-col ${
                    fullWidth ? 'w-full' : 'mx-auto w-full max-w-6xl px-4 py-8'
                }`}
            >
                <div className={fullWidth ? 'mx-auto w-full max-w-6xl' : ''}>
                    <FlashMessages />
                </div>
                {children}
            </main>

            <footer className="mt-auto border-t border-secondary bg-primary text-on-primary">
                <div className="mx-auto max-w-6xl px-4 py-8 text-center text-sm opacity-90">
                    {app.name} — آموزش تخصصی مهندسی
                </div>
            </footer>
        </div>
    );
}
