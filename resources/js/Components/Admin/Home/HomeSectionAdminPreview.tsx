import type { HomeSectionFormData } from '@/Components/Admin/Home/HomeSectionFormFields';
import {
    defaultHeroSettings,
    sectionTemplateId,
} from '@/Components/Admin/Home/home-section-templates';

type Props = {
    data: HomeSectionFormData;
};

function previewImageFromBannerRow(banner: { image?: { previewUrl?: string }[] }) {
    return banner.image?.[0]?.previewUrl ?? null;
}

export function HomeSectionAdminPreview({ data }: Props) {
    const template = sectionTemplateId(data.settings as Record<string, unknown>, data.tabs);
    const hero = { ...defaultHeroSettings(), ...(data.settings.hero ?? {}) };
    const bannerTab = data.tabs.find((t) => t.content_type === 'banner');
    const bannerRows = bannerTab?.banners ?? [];

    return (
        <div className="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
            <div className="border-b border-slate-100 px-3 py-2 text-xs font-medium text-slate-500 dark:border-slate-800">
                پیش‌نمایش خروجی
            </div>
            <div className="p-3" dir="rtl">
                {template === 'hero_split' && (
                    <div className="grid gap-3 md:grid-cols-2">
                        <div className="space-y-2">
                            {hero.badge && (
                                <span className="inline-block rounded-full bg-primary/10 px-2 py-0.5 text-[10px] text-primary">
                                    {hero.badge}
                                </span>
                            )}
                            <p className="text-sm font-bold leading-snug text-slate-900 dark:text-slate-100">
                                {hero.title || 'عنوان هیرو'}
                            </p>
                            {hero.subtitle && <p className="text-[11px] text-slate-600 dark:text-slate-400">{hero.subtitle}</p>}
                            <ul className="flex flex-wrap gap-1">
                                {(hero.highlights ?? []).slice(0, 3).map((h, i) => (
                                    <li
                                        key={i}
                                        className="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {h.label || 'ویژگی'}
                                    </li>
                                ))}
                            </ul>
                            <div className="flex flex-wrap gap-1">
                                <span className="rounded-md bg-primary px-2 py-1 text-[10px] font-bold text-white">
                                    {hero.cta_primary}
                                </span>
                                <span className="rounded-md border border-slate-200 px-2 py-1 text-[10px] text-primary">
                                    {hero.cta_secondary}
                                </span>
                            </div>
                        </div>
                        <div className="relative aspect-4/3 overflow-hidden rounded-lg bg-primary/10">
                            {previewImageFromBannerRow(bannerRows[0] ?? {}) ? (
                                <img
                                    src={previewImageFromBannerRow(bannerRows[0] ?? {})!}
                                    alt=""
                                    className="size-full object-cover"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-[10px] text-slate-400">اسلاید بنر</div>
                            )}
                            {hero.show_live_badge && (
                                <span className="absolute top-1 left-1 rounded bg-white px-1.5 py-0.5 text-[9px] font-bold text-primary shadow">
                                    Live
                                </span>
                            )}
                        </div>
                    </div>
                )}

                {template === 'features' && (
                    <div className="grid grid-cols-3 gap-2">
                        {(data.tabs[0]?.settings?.features ?? []).slice(0, 3).map((f, i) => (
                            <div key={i} className="overflow-hidden rounded-lg border border-slate-100 dark:border-slate-800">
                                {f.image_url ? (
                                    <img src={f.image_url} alt="" className="aspect-4/3 w-full object-cover" />
                                ) : (
                                    <div className="aspect-4/3 bg-primary/10" />
                                )}
                                <div className="p-1.5">
                                    <p className="text-[10px] font-bold">{f.label || 'ستون'}</p>
                                    <p className="mt-0.5 text-[9px] text-slate-500 line-clamp-2">{f.text}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {template === 'banners' && (
                    <div className="aspect-[2/1] overflow-hidden rounded-lg bg-primary/10">
                        {previewImageFromBannerRow(bannerRows[0] ?? {}) ? (
                            <img
                                src={previewImageFromBannerRow(bannerRows[0] ?? {})!}
                                alt=""
                                className="size-full object-cover"
                            />
                        ) : (
                            <div className="flex size-full items-center justify-center text-[10px] text-slate-400">بنر</div>
                        )}
                    </div>
                )}

                {template === 'catalog' && (
                    <div>
                        <p className="mb-2 text-xs font-bold">{data.title || 'عنوان سکشن'}</p>
                        <div className="flex gap-1 overflow-hidden">
                            {[0, 1, 2, 3, 4].map((i) => (
                                <div
                                    key={i}
                                    className="min-w-0 flex-1 overflow-hidden rounded border border-slate-100 dark:border-slate-800"
                                >
                                    <div className="aspect-4/3 bg-primary/15" />
                                    <div className="h-3 bg-slate-50 dark:bg-slate-900" />
                                </div>
                            ))}
                        </div>
                        <p className="mt-1 text-[10px] text-slate-500">
                            اسلایدر · {data.tabs[0]?.content_type ?? 'course'} · تا {data.tabs[0]?.limit ?? '—'} آیتم
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}
