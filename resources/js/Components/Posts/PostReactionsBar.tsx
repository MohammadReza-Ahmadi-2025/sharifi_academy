import type { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

export type ReactionTypeOption = {
    value: string;
    label: string;
    emoji: string;
};

export type ReactionCount = {
    type: string;
    label: string;
    emoji: string;
    count: number;
};

export type PostReactionsPayload = {
    total: number;
    viewer_type: string | null;
    counts: ReactionCount[];
    types: ReactionTypeOption[];
};

type Props = {
    submitUrl: string;
    reactions: PostReactionsPayload;
    compact?: boolean;
    /** Inertia partial reload keys after reacting (e.g. ['posts'] on feed). */
    only?: string[];
};

export function PostReactionsBar({ submitUrl, reactions, compact = false, only }: Props) {
    const { auth } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);
    const [pending, setPending] = useState(false);
    const rootRef = useRef<HTMLDivElement>(null);

    const active = reactions.types.find((type) => type.value === reactions.viewer_type) ?? null;
    const primary = active ?? reactions.types[0];

    useEffect(() => {
        if (!open) {
            return;
        }

        const onPointerDown = (event: MouseEvent) => {
            if (!rootRef.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', onPointerDown);
        return () => document.removeEventListener('mousedown', onPointerDown);
    }, [open]);

    const submit = (type: string) => {
        if (!auth.user || pending) {
            return;
        }

        setPending(true);
        setOpen(false);
        router.post(
            submitUrl,
            { type },
            {
                preserveScroll: true,
                ...(only?.length ? { only } : {}),
                onFinish: () => setPending(false),
            },
        );
    };

    return (
        <div
            ref={rootRef}
            className={`flex flex-wrap items-center gap-2 ${compact ? '' : 'border-t border-secondary/40 pt-3'}`}
        >
            {reactions.counts.length > 0 ? (
                <div className="flex items-center gap-1.5 text-xs text-neutral-black/55">
                    <span className="inline-flex items-center gap-0.5" aria-hidden>
                        {reactions.counts.slice(0, 4).map((item) => (
                            <span key={item.type} className="text-sm leading-none">
                                {item.emoji}
                            </span>
                        ))}
                    </span>
                    <span>{reactions.total.toLocaleString('fa-IR')}</span>
                </div>
            ) : null}

            {auth.user ? (
                <div className="relative">
                    <button
                        type="button"
                        disabled={pending}
                        aria-expanded={open}
                        aria-haspopup="menu"
                        className={`inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-semibold transition disabled:opacity-60 ${
                            active
                                ? 'border-primary/40 bg-primary/10 text-primary'
                                : 'border-secondary/70 bg-white text-neutral-black/70 hover:border-primary/30 hover:text-primary'
                        }`}
                        onClick={() => setOpen((value) => !value)}
                    >
                        <span aria-hidden>{primary?.emoji ?? '👍'}</span>
                        {active?.label ?? 'واکنش'}
                    </button>

                    {open ? (
                        <div
                            role="menu"
                            className="absolute bottom-full z-20 mb-2 flex flex-wrap gap-1 rounded-2xl border border-secondary/50 bg-white p-2 shadow-lg"
                        >
                            {reactions.types.map((type) => (
                                <button
                                    key={type.value}
                                    type="button"
                                    role="menuitem"
                                    title={type.label}
                                    aria-label={type.label}
                                    className={`rounded-xl px-2 py-1.5 text-lg transition hover:bg-surface-muted ${
                                        reactions.viewer_type === type.value ? 'bg-primary/10 ring-1 ring-primary/30' : ''
                                    }`}
                                    onClick={() => submit(type.value)}
                                >
                                    {type.emoji}
                                </button>
                            ))}
                        </div>
                    ) : null}
                </div>
            ) : (
                <p className="text-xs text-neutral-black/55">
                    برای واکنش{' '}
                    <Link href="/login" className="font-bold text-primary hover:underline">
                        وارد شوید
                    </Link>
                </p>
            )}
        </div>
    );
}
