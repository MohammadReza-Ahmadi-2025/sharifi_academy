import { SiteMobileCourseCategoriesSheet } from '@/Components/Site/SiteMobileCourseCategoriesSheet';
import { resolveMobileTabBarIcon } from '@/Components/Site/SiteMobileTabBarIcons';
import {
    collectCourseCategorySlugs,
    indexOfActiveMobileTab,
    mobileTabsFromSiteMenus,
} from '@/lib/mobile-tab-bar';
import { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { type CSSProperties, useEffect, useMemo, useRef, useState } from 'react';
import './SiteMobileTabBar.css';

export function SiteMobileTabBar() {
    const { url, siteMenus, courseCategoryNav, mobileTabBar } = usePage<PageProps>().props;
    const courseCategories = Array.isArray(courseCategoryNav) ? courseCategoryNav : [];
    const tabs = useMemo(
        () =>
            mobileTabsFromSiteMenus(siteMenus?.mobile, {
                canCreatePost: Boolean(mobileTabBar?.can_create_post),
                createPostUrl: mobileTabBar?.create_post_url ?? null,
                needsEnrollment: Boolean(mobileTabBar?.needs_enrollment),
            }),
        [
            siteMenus?.mobile,
            mobileTabBar?.can_create_post,
            mobileTabBar?.create_post_url,
            mobileTabBar?.needs_enrollment,
        ],
    );
    const courseSlugs = useMemo(() => collectCourseCategorySlugs(courseCategories), [courseCategories]);

    const urlActiveIndex = useMemo(
        () => indexOfActiveMobileTab(tabs, url, courseSlugs),
        [tabs, url, courseSlugs],
    );

    const [activeIndex, setActiveIndex] = useState(urlActiveIndex);
    const [courseCategoriesOpen, setCourseCategoriesOpen] = useState(false);
    const [stretchClass, setStretchClass] = useState('');
    const prevIndexRef = useRef(urlActiveIndex);

    useEffect(() => {
        const previous = prevIndexRef.current;
        if (previous === urlActiveIndex) {
            setActiveIndex(urlActiveIndex);
            return;
        }

        const direction = urlActiveIndex - previous > 0 ? 'active-right' : 'active-left';
        const magnitude = Math.min(4, Math.max(1, Math.abs(urlActiveIndex - previous)));
        setStretchClass(`active--${magnitude} ${direction}`);
        setActiveIndex(urlActiveIndex);
        prevIndexRef.current = urlActiveIndex;

        const timer = window.setTimeout(() => setStretchClass(''), 220);
        return () => window.clearTimeout(timer);
    }, [urlActiveIndex]);

    const activateTab = (index: number) => {
        if (index === activeIndex) {
            return;
        }

        const direction = index - activeIndex > 0 ? 'active-right' : 'active-left';
        const magnitude = Math.min(4, Math.max(1, Math.abs(index - activeIndex)));
        setStretchClass(`active--${magnitude} ${direction}`);
        setActiveIndex(index);
        prevIndexRef.current = index;

        window.setTimeout(() => setStretchClass(''), 220);
    };

    const onCourseCategoriesTab = (index: number) => {
        if (index === activeIndex && courseCategoriesOpen) {
            setCourseCategoriesOpen(false);
            return;
        }

        activateTab(index);

        if (courseCategories.length === 0) {
            router.reload({ only: ['courseCategoryNav'] });
        }

        setCourseCategoriesOpen(true);
    };

    if (tabs.length === 0) {
        return null;
    }

    return (
        <>
            <div
                className="doksav-tab-bar-host lg:hidden"
                data-create-active={
                    tabs[activeIndex]?.action === 'create-post' || tabs[activeIndex]?.emphasized
                        ? 'true'
                        : 'false'
                }
                style={
                    {
                        '--tab-count': tabs.length,
                        '--active-index': activeIndex,
                    } as CSSProperties
                }
            >
                <nav className="bottom-bar" aria-label="منوی پایین">
                    <div className="bottom-bar__list">
                        <div
                            className={`bottom-bar__active-indicator ${stretchClass}`.trim()}
                            aria-hidden
                        />

                        {tabs.map((tab, index) => {
                            const Icon = resolveMobileTabBarIcon(tab.icon, index);
                            const isActive =
                                index === activeIndex
                                || (tab.action === 'course-categories' && courseCategoriesOpen);
                            const isCreate = tab.action === 'create-post' || tab.emphasized;
                            const className = [
                                'bottom-bar__link',
                                isActive ? 'selected' : '',
                                isCreate ? 'bottom-bar__link--create' : '',
                            ]
                                .filter(Boolean)
                                .join(' ');

                            const body = (
                                <>
                                    <span className="bottom-bar__icon">
                                        {isCreate ? (
                                            <span className="bottom-bar__icon-shell">
                                                <Icon />
                                            </span>
                                        ) : (
                                            <Icon />
                                        )}
                                    </span>
                                    <span className="bottom-bar__label">{tab.label}</span>
                                </>
                            );

                            if (tab.action === 'course-categories') {
                                return (
                                    <button
                                        key={tab.id}
                                        type="button"
                                        className={className}
                                        aria-expanded={courseCategoriesOpen}
                                        aria-haspopup="dialog"
                                        onClick={() => onCourseCategoriesTab(index)}
                                    >
                                        {body}
                                    </button>
                                );
                            }

                            const linkProps = tab.targetBlank
                                ? { target: '_blank', rel: 'noopener noreferrer' }
                                : {};

                            return (
                                <Link
                                    key={tab.id}
                                    href={tab.href}
                                    {...linkProps}
                                    className={className}
                                    onClick={() => {
                                        setCourseCategoriesOpen(false);
                                        activateTab(index);
                                    }}
                                >
                                    {body}
                                </Link>
                            );
                        })}
                    </div>
                </nav>
            </div>

            <SiteMobileCourseCategoriesSheet
                open={courseCategoriesOpen}
                onClose={() => setCourseCategoriesOpen(false)}
                categories={courseCategories}
            />
        </>
    );
}
