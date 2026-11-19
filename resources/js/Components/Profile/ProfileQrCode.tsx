import { cn } from '@/lib/utils';
import { Download, Share2 } from 'lucide-react';
import QRCode from 'qrcode';
import { useCallback, useEffect, useState } from 'react';

type Props = {
    url: string;
    fileName?: string;
    shareTitle?: string;
    compact?: boolean;
};

async function shareProfileUrl(url: string, title: string): Promise<'shared' | 'copied' | 'cancelled'> {
    if (typeof navigator !== 'undefined' && typeof navigator.share === 'function') {
        try {
            await navigator.share({ title, url, text: title });
            return 'shared';
        } catch (error) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return 'cancelled';
            }
        }
    }

    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(url);
        return 'copied';
    }

    throw new Error('Share unavailable');
}

export function ProfileQrCode({
    url,
    fileName = 'profile-qr.png',
    shareTitle = 'پروفایل',
    compact = false,
}: Props) {
    const [dataUrl, setDataUrl] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const [hint, setHint] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;

        void QRCode.toDataURL(url, {
            width: compact ? 200 : 280,
            margin: 2,
            errorCorrectionLevel: 'M',
            color: {
                dark: '#0f172a',
                light: '#ffffff',
            },
        }).then((value) => {
            if (!cancelled) {
                setDataUrl(value);
            }
        });

        return () => {
            cancelled = true;
        };
    }, [compact, url]);

    const onShare = useCallback(async () => {
        if (!url || busy) {
            return;
        }

        setBusy(true);
        setHint(null);
        try {
            const result = await shareProfileUrl(url, shareTitle);
            if (result === 'copied') {
                setHint('لینک کپی شد');
            }
        } catch {
            setHint('اشتراک‌گذاری ممکن نشد');
        } finally {
            setBusy(false);
        }
    }, [busy, shareTitle, url]);

    const onDownload = useCallback(() => {
        if (!dataUrl) {
            return;
        }

        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = fileName;
        link.rel = 'noopener';
        document.body.appendChild(link);
        link.click();
        link.remove();
    }, [dataUrl, fileName]);

    return (
        <div className={cn('flex flex-col items-center', compact ? 'gap-2' : 'gap-3')}>
            <button
                type="button"
                onClick={() => void onShare()}
                disabled={!dataUrl || busy}
                className={cn(
                    'group relative bg-white shadow-sm ring-1 ring-secondary/50 transition hover:ring-primary/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-60',
                    compact ? 'rounded-xl p-2' : 'rounded-2xl p-3',
                )}
                aria-label="اشتراک‌گذاری پروفایل با QR"
                title="برای اشتراک‌گذاری کلیک کنید"
            >
                {dataUrl ? (
                    <img
                        src={dataUrl}
                        alt="کد QR پروفایل"
                        className={compact ? 'size-24' : 'size-36 sm:size-40'}
                        width={compact ? 96 : 160}
                        height={compact ? 96 : 160}
                    />
                ) : (
                    <div
                        className={cn('flex items-center justify-center', compact ? 'size-24' : 'size-36 sm:size-40')}
                        aria-hidden
                    >
                        <div className="size-8 animate-pulse rounded-md bg-neutral-black/10" />
                    </div>
                )}
                <span
                    className={cn(
                        'pointer-events-none absolute inset-x-2 bottom-2 flex items-center justify-center gap-1 rounded-full bg-neutral-black/70 px-2 py-1 text-[10px] font-semibold text-white opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100',
                        compact && 'inset-x-1.5 bottom-1.5',
                    )}
                >
                    <Share2 className="size-3" aria-hidden />
                    اشتراک
                </span>
            </button>

            <button
                type="button"
                onClick={onDownload}
                disabled={!dataUrl}
                className="inline-flex items-center gap-1.5 text-xs font-semibold text-primary transition hover:text-primary/80 disabled:opacity-50 sm:text-sm"
            >
                <Download className="size-3.5 sm:size-4" aria-hidden />
                دانلود QR کد
            </button>

            {hint ? <p className="text-xs text-neutral-black/50">{hint}</p> : null}
        </div>
    );
}
