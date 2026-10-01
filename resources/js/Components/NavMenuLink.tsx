import { Link } from '@inertiajs/react';

type Props = {
    url: string;
    label: string;
    description?: string | null;
    className?: string;
    onNavigate?: () => void;
};

export function NavMenuLink({ url, label, description, className = '', onNavigate }: Props) {
    const isAnchor = url.startsWith('/#') || url.startsWith('#');
    const base = `block transition ${className}`;

    const content = (
        <>
            <span className="block font-medium leading-6">{label}</span>
            {description && <span className="mt-0.5 block text-xs leading-5 text-neutral-black/60">{description}</span>}
        </>
    );

    if (isAnchor) {
        return (
            <a href={url} className={base} onClick={onNavigate}>
                {content}
            </a>
        );
    }

    if (url.startsWith('http')) {
        return (
            <a href={url} className={base} target="_blank" rel="noreferrer">
                {content}
            </a>
        );
    }

    return (
        <Link href={url} className={base} onClick={onNavigate}>
            {content}
        </Link>
    );
}
