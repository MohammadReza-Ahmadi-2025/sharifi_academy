import '../css/app.css';

import { InertiaFlashToasts } from '@/Components/InertiaFlashToasts';
import { ToastProvider } from '@/Components/shared/contexts/toast-context';
import { Toaster } from '@/Components/shared/ui/toaster';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME ?? 'Sharifi Academy';

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
    setup({ el, App, props }) {
        createRoot(el).render(
            <ToastProvider>
                <App {...props} />
                <Toaster />
                <InertiaFlashToasts />
            </ToastProvider>,
        );
    },
    progress: {
        color: '#4f46e5',
    },
});
