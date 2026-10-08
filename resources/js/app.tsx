import '../css/app.css';

import { InertiaFlashToasts } from '@/Components/InertiaFlashToasts';
import { ToastProvider } from '@/Components/shared/contexts/toast-context';
import { Toaster } from '@/Components/shared/ui/toaster';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const fallbackAppName = import.meta.env.VITE_APP_NAME ?? 'Sharifi Academy';
let documentAppName = fallbackAppName;

createInertiaApp({
    title: (title) => (title ? `${title} · ${documentAppName}` : documentAppName),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({ el, App, props }) {
        const initialName = (props.initialPage?.props as { app?: { name?: string } })?.app?.name;
        if (initialName) {
            documentAppName = initialName;
        }

        createRoot(el).render(
            <ToastProvider>
                <App
                    {...props}
                    children={({ Component, props: pageProps, key }) => (
                        <>
                            <Component key={key} {...pageProps} />
                            <Toaster />
                            <InertiaFlashToasts />
                        </>
                    )}
                />
            </ToastProvider>,
        );
    },
    progress: {
        color: '#4f46e5',
    },
});
