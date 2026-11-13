import { SitePublicBreadcrumb, type PublicBreadcrumbItem } from '@/Components/Site/SitePublicBreadcrumb';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';

type Props = {
    title: string;
    breadcrumb: PublicBreadcrumbItem[];
    eyebrow?: string | null;
    subheader?: string | null;
    descriptionHtml?: string | null;
    imageUrl?: string | null;
};

export function BlogHero({
    title,
    breadcrumb,
    eyebrow,
    subheader,
    descriptionHtml,
    imageUrl,
}: Props) {
    const cover = imageUrl?.trim();
    const hasDescription = Boolean(descriptionHtml?.trim());

    return (
        <header className="border-b border-secondary/45 bg-neutral-white">
            <div className="mx-auto max-w-6xl px-4 pt-8 sm:px-6 lg:px-8 lg:pt-10">
                <SitePublicBreadcrumb className="mb-5" items={breadcrumb} />

                {eyebrow ? <p className="mb-2 text-sm font-bold text-primary">{eyebrow}</p> : null}

                <h1 className="max-w-4xl text-2xl font-black leading-snug text-neutral-black sm:text-3xl lg:text-[2.15rem]">
                    {title}
                </h1>

                {subheader ? (
                    <p className="mt-3 max-w-3xl text-sm leading-relaxed text-neutral-black/60 sm:text-base">
                        {subheader}
                    </p>
                ) : null}

                {hasDescription ? (
                    <div className="mt-4 max-w-3xl text-sm leading-relaxed text-neutral-black/65 sm:text-base">
                        <SiteRichHtml html={descriptionHtml!} />
                    </div>
                ) : null}

                {cover ? (
                    <div className="mt-7 overflow-hidden rounded-2xl bg-secondary/15">
                        <img
                            src={cover}
                            alt=""
                            className="aspect-[2.2/1] w-full object-cover sm:aspect-[2.4/1]"
                            fetchPriority="high"
                            decoding="async"
                        />
                    </div>
                ) : null}
            </div>
            <div className="h-8 sm:h-10" aria-hidden />
        </header>
    );
}
