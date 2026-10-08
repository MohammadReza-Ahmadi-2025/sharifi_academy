import { Link } from '@inertiajs/react';

type LinkItem = { url: string | null; label: string; active: boolean };

type Paginator = {
    data: unknown[];
    current_page: number;
    last_page: number;
    total: number;
    links: LinkItem[];
};

type Props = {
    paginator: Paginator;
};

export function SitePagination({ paginator }: Props) {
    if (paginator.last_page <= 1) {
        return null;
    }

    return (
        <nav className="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="صفحه‌بندی">
            {paginator.links.map((link, index) => {
                if (!link.url) {
                    return (
                        <span
                            key={`${index}-${link.label}`}
                            className="min-w-9 rounded-lg px-3 py-2 text-center text-sm text-neutral-black/40"
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    );
                }

                return (
                    <Link
                        key={`${index}-${link.url}`}
                        href={link.url}
                        preserveScroll
                        className={`min-w-9 rounded-lg px-3 py-2 text-center text-sm font-semibold transition ${
                            link.active
                                ? 'bg-primary text-on-primary shadow-sm'
                                : 'border border-secondary bg-neutral-white text-neutral-black hover:border-primary/40'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                );
            })}
        </nav>
    );
}
