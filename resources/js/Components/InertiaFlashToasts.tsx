import { useToast } from '@/Components/shared/contexts/toast-context';
import { useEffect, useRef } from 'react';

type HttpExceptionResponse = {
    status: number;
    data: string | Record<string, unknown>;
};

function readAppDebug(): boolean {
    const script = document.querySelector<HTMLScriptElement>('script[data-page][type="application/json"]');

    if (!script?.textContent) {
        return false;
    }

    try {
        const page = JSON.parse(script.textContent) as {
            props?: { app?: { debug?: boolean } };
        };

        return page.props?.app?.debug === true;
    } catch {
        return false;
    }
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

type FlashPayload = {
    success?: string | null;
    error?: string | null;
};

function readInitialFlash(): FlashPayload | null {
    const script = document.querySelector<HTMLScriptElement>('script[data-page][type="application/json"]');

    if (!script?.textContent) {
        return null;
    }

    try {
        const page = JSON.parse(script.textContent) as {
            props?: { flash?: FlashPayload };
        };

        return page.props?.flash ?? null;
    } catch {
        return null;
    }
}

export function InertiaFlashToasts() {
    const toast = useToast();
    const lastFlashRef = useRef({ success: null as string | null, error: null as string | null });

    const showFlash = (flash: FlashPayload | null | undefined) => {
        if (!flash) {
            return;
        }

        if (flash.success && flash.success !== lastFlashRef.current.success) {
            toast.success(flash.success);
            lastFlashRef.current.success = flash.success;
        }

        if (flash.error && flash.error !== lastFlashRef.current.error) {
            toast.error(flash.error);
            lastFlashRef.current.error = flash.error;
        }

        if (!flash.success) {
            lastFlashRef.current.success = null;
        }

        if (!flash.error) {
            lastFlashRef.current.error = null;
        }
    };

    useEffect(() => {
        showFlash(readInitialFlash());
    }, [toast]);

    useEffect(() => {
        const onFlash = (event: Event) => {
            const detail = (event as CustomEvent<{ flash?: FlashPayload }>).detail;
            showFlash(detail?.flash);
        };

        const onSuccess = (event: Event) => {
            const page = (event as CustomEvent<{ page?: { flash?: FlashPayload } }>).detail?.page;
            showFlash(page?.flash);
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
            const custom = event as CustomEvent<{ response: HttpExceptionResponse }>;
            const response = custom.detail?.response;

            if (!response) {
                toast.error('خطای سرور. لطفاً دوباره تلاش کنید.');

                return;
            }

            // در debug، Laravel/Ignition خودش خطا را نشان می‌دهد.
            if (readAppDebug() && response.status >= 500) {
                return;
            }

            toast.error(messageFromHttpException(response, readAppDebug()));
        };

        const onNetworkError = () => {
            toast.error('خطا در ارتباط با سرور. اتصال اینترنت را بررسی کنید.');
        };

        document.addEventListener('inertia:flash', onFlash);
        document.addEventListener('inertia:success', onSuccess);
        document.addEventListener('inertia:error', onValidationError);
        document.addEventListener('inertia:httpException', onHttpException);
        document.addEventListener('inertia:networkError', onNetworkError);

        return () => {
            document.removeEventListener('inertia:flash', onFlash);
            document.removeEventListener('inertia:success', onSuccess);
            document.removeEventListener('inertia:error', onValidationError);
            document.removeEventListener('inertia:httpException', onHttpException);
            document.removeEventListener('inertia:networkError', onNetworkError);
        };
    }, [toast]);

    return null;
}
