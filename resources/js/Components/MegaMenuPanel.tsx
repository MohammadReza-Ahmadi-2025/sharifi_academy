import { NavMegaMenu } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { NavMenuLink } from './NavMenuLink';

type Props = {
    mega: NavMegaMenu;
    compact?: boolean;
    onNavigate?: () => void;
};

export function MegaMenuPanel({ mega, compact = false, onNavigate }: Props) {
    if (compact) {
        return (
            <div className="space-y-5 text-neutral-black">
                {mega.columns.map((column) => (
                    <div key={column.title}>
                        <p className="mb-2 text-[11px] font-bold tracking-wide text-primary/80">{column.title}</p>
                        <ul className="divide-y divide-secondary/80 overflow-hidden rounded-xl border border-secondary bg-neutral-white">
                            {column.links.map((link) => (
                                <li key={`${column.title}-${link.label}`}>
                                    <NavMenuLink
                                        url={link.url}
                                        label={link.label}
                                        description={link.description}
                                        className="px-3 py-2.5 hover:bg-surface-muted"
                                        onNavigate={onNavigate}
                                    />
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}

                {mega.view_all && (
                    <NavMenuLink
                        url={mega.view_all.url}
                        label={mega.view_all.label}
                        className="inline-flex items-center gap-1 text-sm font-semibold text-primary"
                        onNavigate={onNavigate}
                    />
                )}
            </div>
        );
    }

    const hasFeatured = Boolean(mega.featured);

    return (
        <div className="grid gap-0 lg:grid-cols-[minmax(240px,280px)_minmax(0,1fr)]">
            {hasFeatured && mega.featured && (
                <Link
                    href={mega.featured.url}
                    className="group relative overflow-hidden bg-primary text-on-primary"
                    onClick={onNavigate}
                >
                    {mega.featured.image && (
                        <img
                            src={mega.featured.image}
                            alt=""
                            className="absolute inset-0 size-full object-cover opacity-40 transition duration-300 group-hover:scale-105 group-hover:opacity-50"
                        />
                    )}
                    <div className="relative flex h-full min-h-44 flex-col justify-end p-5">
                        <p className="text-xs font-semibold text-secondary">پیشنهاد آکادمی</p>
                        <h3 className="mt-1 text-lg font-bold">{mega.featured.title}</h3>
                        {mega.featured.description && (
                            <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-on-primary/80">
                                {mega.featured.description}
                            </p>
                        )}
                        <span className="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-secondary">
                            بیشتر ببینید
                            <ArrowLeft className="size-4" />
                        </span>
                    </div>
                </Link>
            )}

            <div className={`flex flex-col bg-neutral-white p-6 ${hasFeatured ? '' : 'lg:col-span-2'}`}>
                <div className="grid flex-1 gap-8 sm:grid-cols-2">
                    {mega.columns.map((column) => (
                        <div key={column.title}>
                            <p className="mb-3 border-b border-secondary pb-2 text-xs font-bold tracking-wide text-primary">
                                {column.title}
                            </p>
                            <ul className="space-y-1">
                                {column.links.map((link) => (
                                    <li key={`${column.title}-${link.label}`}>
                                        <NavMenuLink
                                            url={link.url}
                                            label={link.label}
                                            description={link.description}
                                            className="rounded-lg px-2 py-2 hover:bg-surface-muted"
                                            onNavigate={onNavigate}
                                        />
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </div>

                {mega.view_all && (
                    <div className="mt-6 border-t border-secondary pt-4">
                        <NavMenuLink
                            url={mega.view_all.url}
                            label={mega.view_all.label}
                            className="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:opacity-80"
                            onNavigate={onNavigate}
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
