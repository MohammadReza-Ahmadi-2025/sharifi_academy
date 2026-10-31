type Recording = {
    id: number;
    name: string;
    playback_url: string;
    download_url: string | null;
};

type SessionLike = {
    join_url?: string | null;
    adobe_connect_url?: string | null;
    join_label?: string | null;
    join_hint?: string | null;
    adobe_login_hint?: string | null;
    recordings?: Recording[];
};

export function SessionActions({ session }: { session: SessionLike }) {
    const joinUrl = session.join_url ?? session.adobe_connect_url ?? null;
    const recordings = session.recordings ?? [];

    return (
        <span className="flex flex-wrap items-center gap-2">
            {joinUrl ? (
                <a
                    href={joinUrl}
                    target="_blank"
                    rel="noreferrer"
                    className="rounded-xl bg-primary px-3.5 py-2 text-sm font-semibold text-on-primary transition hover:bg-primary-hover"
                >
                    {session.join_label ?? 'ورود به کلاس'}
                </a>
            ) : session.join_hint ? (
                <span className="max-w-xs text-xs leading-relaxed text-amber-800">{session.join_hint}</span>
            ) : null}
            {session.adobe_login_hint ? (
                <span className="text-xs text-slate-500">{session.adobe_login_hint}</span>
            ) : null}
            {recordings.map((recording) => (
                <span key={recording.id} className="flex gap-1">
                    <a
                        href={recording.playback_url}
                        target="_blank"
                        rel="noreferrer"
                        className="rounded-xl border border-primary/25 px-3 py-2 text-sm font-medium text-primary transition hover:bg-primary/5"
                    >
                        {recording.name}
                    </a>
                    {recording.download_url ? (
                        <a
                            href={recording.download_url}
                            target="_blank"
                            rel="noreferrer"
                            className="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50"
                        >
                            دانلود MP4
                        </a>
                    ) : null}
                </span>
            ))}
        </span>
    );
}

export function enrollmentStatusClass(status: string): string {
    switch (status) {
        case 'confirmed':
            return 'bg-emerald-50 text-emerald-800 ring-emerald-600/15';
        case 'cancelled':
            return 'bg-slate-100 text-slate-600 ring-slate-500/10';
        default:
            return 'bg-amber-50 text-amber-900 ring-amber-600/15';
    }
}

export function attendanceStatusClass(status: string): string {
    switch (status) {
        case 'present':
            return 'bg-emerald-50 text-emerald-800 ring-emerald-600/15';
        case 'late':
            return 'bg-sky-50 text-sky-800 ring-sky-600/15';
        case 'excused':
            return 'bg-violet-50 text-violet-800 ring-violet-600/15';
        default:
            return 'bg-rose-50 text-rose-800 ring-rose-600/15';
    }
}
