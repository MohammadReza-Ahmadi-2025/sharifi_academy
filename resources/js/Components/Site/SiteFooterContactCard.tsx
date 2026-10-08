import { siteSocialsFromPage, type SiteSocialLink } from '@/Components/Site/SiteFooterSocials';
import { SocialTypeIcon } from '@/Components/Site/social-type-icons';
import { siteSettingsFromPage } from '@/lib/site-settings';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { MapPin } from 'lucide-react';

function SocialIconButton({ item }: { item: SiteSocialLink }) {
    return (
        <a
            href={item.url}
            title={item.label}
            className="group flex size-10 items-center justify-center rounded-xl border border-white/20 bg-white/10 text-on-primary shadow-sm transition hover:-translate-y-0.5 hover:border-white/40 hover:bg-white/20 hover:shadow-md"
            {...(item.target_blank ? { target: '_blank', rel: 'noopener noreferrer' } : {})}
        >
            <SocialTypeIcon type={item.type} size={18} className="opacity-95 group-hover:opacity-100" />
            <span className="sr-only">{item.label}</span>
        </a>
    );
}

export function SiteFooterContactCard() {
    const pageProps = usePage<PageProps>().props;
    const settings = siteSettingsFromPage(pageProps);
    const socials = siteSocialsFromPage(pageProps);

    const address = settings.address?.trim() ?? '';
    const phone = settings.contact_phone?.trim() ?? '';
    const email = settings.contact_email?.trim() ?? '';

    const hasContact = Boolean(address || phone || email);
    const hasSocials = socials.length > 0;

    if (!hasContact && !hasSocials) {
        return null;
    }

    return (
        <aside className="relative w-full shrink-0 lg:max-w-sm">
            {/* برچسب بالای باکس */}
            <div
                className="absolute -top-3 right-6 z-10 rounded-md border border-white/30 bg-white px-3 py-1 text-[11px] font-bold tracking-wide text-primary shadow-md"
                style={{ transform: 'rotate(-2deg)' }}
            >
                تماس با ما
            </div>

            <div
                className="relative overflow-hidden rounded-2xl border border-white/25 bg-gradient-to-br from-white/15 via-white/8 to-white/5 pt-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.15),0_12px_40px_rgba(0,0,0,0.15)] backdrop-blur-sm"
            >
                {/* نوار کناری شبیه برچسب */}
                <div
                    className="pointer-events-none absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b from-white/50 via-white/25 to-white/10"
                    aria-hidden
                />
                <div
                    className="pointer-events-none absolute -left-8 top-1/2 h-24 w-24 -translate-y-1/2 rotate-12 rounded-full bg-white/5 blur-2xl"
                    aria-hidden
                />

                <div className="space-y-4 p-5 pl-6">
                    {hasContact && (
                        <ul className="space-y-3 text-sm leading-relaxed opacity-95">
                            {address && (
                                <li className="flex gap-3">
                                    <span
                                        className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-white/10"
                                    >
                                        <MapPin className="size-4" strokeWidth={2} />
                                    </span>
                                    <p className="min-w-0 flex-1 pt-0.5">{address}</p>
                                </li>
                            )}
                            {phone && (
                                <li className="flex gap-3">
                                    <span className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-white/10">
                                        <SocialTypeIcon type="phone" size={16} />
                                    </span>
                                    <a href={`tel:${phone}`} className="pt-1 hover:underline" dir="ltr">
                                        {phone}
                                    </a>
                                </li>
                            )}
                            {email && (
                                <li className="flex gap-3">
                                    <span className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg border border-white/15 bg-white/10">
                                        <SocialTypeIcon type="email" size={16} />
                                    </span>
                                    <a href={`mailto:${email}`} className="break-all pt-1 hover:underline" dir="ltr">
                                        {email}
                                    </a>
                                </li>
                            )}
                        </ul>
                    )}

                    {hasSocials && (
                        <div className={hasContact ? 'border-t border-white/15 pt-4' : ''}>
                            <p className="mb-3 text-xs font-semibold uppercase tracking-wider opacity-75">
                                شبکه‌های اجتماعی
                            </p>
                            <div className="flex flex-wrap gap-2">
                                {socials.map((item) => (
                                    <SocialIconButton key={item.id} item={item} />
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* گوشه تزئینی برچسب */}
                <div
                    className="pointer-events-none absolute bottom-0 right-0 size-16 bg-gradient-to-tl from-white/10 to-transparent"
                    aria-hidden
                />
            </div>
        </aside>
    );
}
