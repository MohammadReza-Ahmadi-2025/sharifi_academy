import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronLeft, LayoutGrid } from 'lucide-react';
import { useCallback, useState } from 'react';

type MenuBrand = { id: number; title: string; slug: string; url: string };
type MenuChild = { id: number; title: string; slug: string; url: string; children?: MenuChild[] };
type MenuCategory = {
    id: number;
    title: string;
    slug: string;
    url: string;
    image_url?: string | null;
    children: MenuChild[];
    brands: MenuBrand[];
};

export function SiteProductCategoryBar() {
    const { menuCategories } = usePage<PageProps>().props;
    const categories = (menuCategories ?? []) as MenuCategory[];

    const [open, setOpen] = useState(false);
    const [activeId, setActiveId] = useState<number | null>(categories[0]?.id ?? null);

    const close = useCallback(() => setOpen(false), []);

    if (categories.length === 0) {
        return null;
    }

    const active = categories.find((c) => c.id === activeId) ?? categories[0];

    return (
        <div
            className="relative hidden border-b border-secondary bg-neutral-white text-neutral-black lg:block"
            onMouseLeave={() => setOpen(false)}
        >
            <div className="mx-auto flex max-w-6xl items-center gap-6 px-4 py-2 text-sm">
                <button
                    type="button"
                    className="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 font-semibold text-primary hover:bg-primary/5"
                    onMouseEnter={() => setOpen(true)}
                    onFocus={() => setOpen(true)}
                    aria-expanded={open}
                >
                    <LayoutGrid className="size-4" />
                    دسته‌بندی کتاب‌ها
                </button>
                <div className="flex min-w-0 flex-1 items-center gap-4 overflow-x-auto text-neutral-black/80">
                    {categories.slice(0, 6).map((category) => (
                        <Link
                            key={category.id}
                            href={category.url}
                            className="shrink-0 whitespace-nowrap hover:text-primary"
                            onMouseEnter={() => {
                                setActiveId(category.id);
                                setOpen(true);
                            }}
                        >
                            {category.title}
                        </Link>
                    ))}
                </div>
            </div>

            {open && active && (
                <div
                    className="absolute inset-x-0 top-full z-40 border-t border-secondary bg-neutral-white shadow-xl"
                    onMouseEnter={() => setOpen(true)}
                >
                    <div className="mx-auto grid max-w-6xl gap-0 lg:grid-cols-[260px_minmax(0,1fr)]">
                        <ul className="border-l border-secondary bg-surface-muted/60 py-2">
                            {categories.map((category) => (
                                <li key={category.id}>
                                    <button
                                        type="button"
                                        className={`flex w-full items-center justify-between gap-2 px-4 py-2.5 text-right text-sm transition ${
                                            category.id === active.id
                                                ? 'bg-neutral-white font-bold text-primary shadow-sm'
                                                : 'text-neutral-black/80 hover:bg-neutral-white'
                                        }`}
                                        onMouseEnter={() => setActiveId(category.id)}
                                    >
                                        <span className="flex min-w-0 items-center gap-2">
                                            {category.image_url ? (
                                                <img
                                                    src={category.image_url}
                                                    alt=""
                                                    className="size-8 rounded-lg border border-secondary object-contain bg-white"
                                                />
                                            ) : null}
                                            <span className="truncate">{category.title}</span>
                                        </span>
                                        <ChevronLeft className="size-4 shrink-0 opacity-60" />
                                    </button>
                                </li>
                            ))}
                        </ul>
                        <div className="grid gap-6 p-6 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p className="mb-2 text-xs font-bold text-primary/80">زیردسته‌ها</p>
                                <ul className="space-y-1 text-sm">
                                    {active.children.length === 0 ? (
                                        <li>
                                            <Link href={active.url} className="hover:text-primary" onClick={close}>
                                                همه در «{active.title}»
                                            </Link>
                                        </li>
                                    ) : (
                                        active.children.map((child) => (
                                            <li key={child.id}>
                                                <Link href={child.url} className="hover:text-primary" onClick={close}>
                                                    {child.title}
                                                </Link>
                                                {child.children && child.children.length > 0 && (
                                                    <ul className="mt-1 space-y-0.5 pr-3 text-xs text-neutral-black/60">
                                                        {child.children.map((grand) => (
                                                            <li key={grand.id}>
                                                                <Link href={grand.url} className="hover:text-primary" onClick={close}>
                                                                    {grand.title}
                                                                </Link>
                                                            </li>
                                                        ))}
                                                    </ul>
                                                )}
                                            </li>
                                        ))
                                    )}
                                </ul>
                            </div>
                            {active.brands.length > 0 && (
                                <div>
                                    <p className="mb-2 text-xs font-bold text-primary/80">برندها</p>
                                    <ul className="flex flex-wrap gap-2">
                                        {active.brands.map((brand) => (
                                            <li key={brand.id}>
                                                <Link
                                                    href={brand.url}
                                                    className="inline-block rounded-full border border-secondary px-3 py-1 text-xs hover:border-primary hover:text-primary"
                                                    onClick={close}
                                                >
                                                    {brand.title}
                                                </Link>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                            <div className="flex items-end md:col-span-2 lg:col-span-1">
                                <Link
                                    href={active.url}
                                    className="inline-flex items-center gap-1 text-sm font-semibold text-primary"
                                    onClick={close}
                                >
                                    مشاهده همه در {active.title}
                                    <ChevronLeft className="size-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
