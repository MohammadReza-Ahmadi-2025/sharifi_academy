import { Link } from '@inertiajs/react';

export type ProductCatalogCard = {
    id: number;
    title: string;
    slug: string;
    description?: string | null;
    brand_title?: string | null;
    price?: number | null;
    compare_price?: number | null;
    image_url?: string | null;
};

type Props = {
    products: ProductCatalogCard[];
    variant?: 'default' | 'category';
};

export function ProductCatalogGrid({ products, variant = 'default' }: Props) {
    if (products.length === 0) {
        return (
            <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-16 text-center text-sm text-neutral-black/65">
                در این دسته هنوز محصولی منتشر نشده است.
            </p>
        );
    }

    const gridClass =
        variant === 'category'
            ? 'grid gap-6 sm:grid-cols-2 xl:grid-cols-3'
            : 'grid gap-5 sm:grid-cols-2 lg:grid-cols-3';

    const cardClass =
        variant === 'category'
            ? 'group flex flex-col overflow-hidden rounded-2xl border border-secondary/70 bg-neutral-white shadow-sm ring-1 ring-black/[0.03] transition hover:-translate-y-1 hover:border-primary/30 hover:shadow-lg'
            : 'group flex flex-col overflow-hidden rounded-xl border border-secondary/80 bg-neutral-white shadow-md transition hover:-translate-y-0.5 hover:border-primary/25 hover:shadow-lg';

    return (
        <div className={gridClass}>
            {products.map((product) => (
                <Link
                    key={product.id}
                    href={`/books/${product.slug}`}
                    className={cardClass}
                >
                    <div
                        className={`overflow-hidden bg-primary/10 ${
                            variant === 'category' ? 'aspect-[4/3]' : 'aspect-[5/4]'
                        }`}
                    >
                        <img
                            src={product.image_url ?? '/banner.png'}
                            alt={product.title}
                            loading={variant === 'category' ? 'lazy' : undefined}
                            decoding="async"
                            className="size-full object-cover transition duration-500 group-hover:scale-[1.03]"
                        />
                    </div>
                    <div className={`flex flex-1 flex-col ${variant === 'category' ? 'p-5' : 'p-4'}`}>
                        {product.brand_title && (
                            <span
                                className={`mb-2 font-semibold text-neutral-black/55 ${
                                    variant === 'category' ? 'text-xs' : 'text-[11px]'
                                }`}
                            >
                                {product.brand_title}
                            </span>
                        )}
                        <h3
                            className={`line-clamp-2 font-extrabold text-neutral-black ${
                                variant === 'category' ? 'text-base sm:text-lg' : ''
                            }`}
                        >
                            {product.title}
                        </h3>
                        {product.description && (
                            <p
                                className={`mt-2 line-clamp-2 text-neutral-black/65 ${
                                    variant === 'category' ? 'text-sm sm:text-[15px]' : 'text-sm'
                                }`}
                            >
                                {product.description}
                            </p>
                        )}
                        {typeof product.price === 'number' && product.price > 0 && (
                            <p
                                className={`mt-auto pt-3 font-extrabold text-primary ${
                                    variant === 'category' ? 'text-base' : 'text-sm'
                                }`}
                            >
                                {product.price.toLocaleString('fa-IR')} ریال
                            </p>
                        )}
                    </div>
                </Link>
            ))}
        </div>
    );
}
