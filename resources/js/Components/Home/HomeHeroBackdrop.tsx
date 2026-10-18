type Props = {
    /** برای سکشن‌های کوتاه‌تر (مثل استوری) */
    compact?: boolean;
};

export function HomeHeroBackdrop({ compact = false }: Props) {
    return (
        <>
            <div
                className={`pointer-events-none absolute inset-0 ${compact ? 'opacity-[0.28]' : 'opacity-[0.35]'}`}
                style={{
                    backgroundImage:
                        'linear-gradient(to left, rgb(var(--color-secondary-rgb) / 0.5) 1px, transparent 1px), linear-gradient(to bottom, rgb(var(--color-secondary-rgb) / 0.5) 1px, transparent 1px)',
                    backgroundSize: '48px 48px',
                }}
                aria-hidden
            />
        </>
    );
}
