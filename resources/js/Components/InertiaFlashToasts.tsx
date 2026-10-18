import { useToast } from '@/Components/shared/contexts/toast-context';
import type { PageProps } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef } from 'react';

type HttpExceptionResponse = {
    status: number;
    data: string | Record<string, unknown>;
};

type FlashPayload = {
    success?: string | null;
    error?: string | null;
};

type InertiaPage = {
    flash?: Record<string, unknown>;
    props?: { flash?: FlashPayload; app?: { debug?: boolean } };
};

function readAppDebug(page?: InertiaPage): boolean {
    return page?.props?.app?.debug === true;
}

function messageFromHttpException(response: HttpExceptionResponse, debug: boolean): string {
    const { data, status } = response;

    if (typeof data === 'object' && data !== null) {
        const message = typeof data.message === 'string' ? data.message : null;

        if (message) {
            if (debug) {
                const file = typeof data.file === 'string' ? data.file : null;
                const line = typeof data.line === 'number' ? data.line : null;

                if (file && line) {
                    return `${message} (${file}:${line})`;
                }
            }

            return message;
        }
    }

    if (typeof data === 'string' && data.length > 0 && debug) {
        const match = data.match(/<title>([^<]+)<\/title>/i);
        const title = match?.[1]?.replace(/\s*\|\s*.*$/, '').trim();

        if (title) {
            return title;
        }
    }

    return status >= 500
        ? `خطای سرور (${status}). لطفاً دوباره تلاش کنید.`
        : `خطا در درخواست (${status}).`;
}

function normalizeFlash(input: unknown): FlashPayload | null {
    if (!input || typeof input !== 'object') {
        return null;
    }

    const record = input as Record<string, unknown>;

    return {
        success: typeof record.success === 'string' && record.success !== '' ? record.success : null,
        error: typeof record.error === 'string' && record.error !== '' ? record.error : null,
    };
}

function flashFromPage(page?: InertiaPage): FlashPayload | null {
    if (!page) {
        return null;
    }

    const fromRoot = normalizeFlash(page.flash);
    const fromProps = normalizeFlash(page.props?.flash);

    if (!fromRoot && !fromProps) {
        return null;
    }

    return {
        success: fromRoot?.success ?? fromProps?.success ?? null,
        error: fromRoot?.error ?? fromProps?.error ?? null,
    };
}

function flashSignature(flash: FlashPayload | null): string {
    if (!flash) {
        return '';
    }

    return `${flash.success ?? ''}|${flash.error ?? ''}`;
}

export function InertiaFlashToasts() {
    const toast = useToast();
    const { flash: sharedFlash } = usePage<PageProps>().props;
    const lastSignatureRef = useRef('');

    const showFlash = useCallback(
        (flash: FlashPayload | null | undefined) => {
            if (!flash) {
                return;
            }

            const signature = flashSignature(flash);
            if (signature === '') {
                lastSignatureRef.current = '';

                return;
            }

            if (signature === lastSignatureRef.current) {
                return;
            }

            lastSignatureRef.current = signature;

            if (flash.success) {
                toast.success(flash.success);
            }

            if (flash.error) {
                toast.error(flash.error);
            }
        },
        [toast],
    );

    useEffect(() => {
        showFlash(normalizeFlash(sharedFlash));
    }, [sharedFlash?.success, sharedFlash?.error, showFlash]);

    useEffect(() => {
        const onFlash = (event: Event) => {
            const detail = (event as CustomEvent<{ flash?: Record<string, unknown> }>).detail;
            showFlash(normalizeFlash(detail?.flash));
        };

        const onSuccess = (event: Event) => {
            const page = (event as CustomEvent<{ page?: InertiaPage }>).detail?.page;
            showFlash(flashFromPage(page));
        };

        const onNavigate = (event: Event) => {
            const page = (event as CustomEvent<{ page?: InertiaPage }>).detail?.page;
            showFlash(flashFromPage(page));
        };

        const onValidationError = (event: Event) => {
            const custom = event as CustomEvent<{ errors: Record<string, string | string[]> }>;
            const values = Object.values(custom.detail?.errors ?? {}).flat();
            const first = values.find((value) => typeof value === 'string' && value.length > 0);

            if (first) {
                toast.error(String(first));
            } else {
                toast.error('لطفاً خطاهای فرم را بررسی کنید.');
            }
        };

        const onHttpException = (event: Event) => {
            const custom = event as CustomEvent<{ response: HttpExceptionResponse; page?: InertiaPage }>;
            const response = custom.detail?.response;
            const debug = readAppDebug(custom.detail?.page);

            if (!response) {
                toast.error('خطای سرور. لطفاً دوباره تلاش کنید.');

                return;
            }

            if (debug && response.status >= 500) {
                return;
            }

            toast.error(messageFromHttpException(response, debug));
        };

        const onNetworkError = () => {
            toast.error('خطا در ارتباط با سرور. اتصال اینترنت را بررسی کنید.');
        };

        /** هر درخواست Inertia (مثلاً ذخیرهٔ دوباره) اجازهٔ نمایش همان متن توست را می‌دهد */
        const resetFlashDedup = () => {
            lastSignatureRef.current = '';
        };

        const removeFlash = router.on('flash', (event) => {
            showFlash(normalizeFlash(event.detail.flash));
        });

        const removeSuccess = router.on('success', (event) => {
            showFlash(flashFromPage(event.detail.page as InertiaPage));
        });

        const removeStart = router.on('start', resetFlashDedup);

        document.addEventListener('inertia:start', resetFlashDedup);
        document.addEventListener('inertia:flash', onFlash);
        document.addEventListener('inertia:success', onSuccess);
        document.addEventListener('inertia:navigate', onNavigate);
        document.addEventListener('inertia:error', onValidationError);
        document.addEventListener('inertia:httpException', onHttpException);
        document.addEventListener('inertia:networkError', onNetworkError);

        return () => {
            removeFlash();
            removeSuccess();
            removeStart();
            document.removeEventListener('inertia:start', resetFlashDedup);
            document.removeEventListener('inertia:flash', onFlash);
            document.removeEventListener('inertia:success', onSuccess);
            document.removeEventListener('inertia:navigate', onNavigate);
            document.removeEventListener('inertia:error', onValidationError);
            document.removeEventListener('inertia:httpException', onHttpException);
            document.removeEventListener('inertia:networkError', onNetworkError);
        };
    }, [showFlash, toast]);

    return null;
}
