import { SiteMobileCourseCategoriesSheet } from '@/Components/Site/SiteMobileCourseCategoriesSheet';
import { resolveMobileTabBarIcon } from '@/Components/Site/SiteMobileTabBarIcons';
import {
    collectCourseCategorySlugs,
    indexOfActiveMobileTab,
    mobileTabsFromSiteMenus,
} from '@/lib/mobile-tab-bar';
import { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react';
import './SiteMobileTabBar.css';

function setLineWidth(text: HTMLElement, item: HTMLElement) {
    item.style.setProperty('--lineWidth', `${text.offsetWidth}px`);
}

export function SiteMobileTabBar() {
    const { url, siteMenus, courseCategoryNav } = usePage<PageProps>().props;
    const courseCategories = Array.isArray(courseCategoryNav) ? courseCategoryNav : [];
    const tabs = useMemo(() => mobileTabsFromSiteMenus(siteMenus?.mobile), [siteMenus?.mobile]);
    const courseSlugs = useMemo(() => collectCourseCategorySlugs(courseCategories), [courseCategories]);

    const urlActiveIndex = useMemo(
        () => indexOfActiveMobileTab(tabs, url, courseSlugs),
        [tabs, url, courseSlugs],
    );
    const [activeIndex, setActiveIndex] = useState(urlActiveIndex);
    const [textActiveIndex, setTextActiveIndex] = useState(urlActiveIndex);
    const [courseCategoriesOpen, setCourseCategoriesOpen] = useState(false);

    const itemRefs = useRef<(HTMLAnchorElement | HTMLButtonElement | null)[]>([]);
    const textRefs = useRef<(HTMLElement | null)[]>([]);

    useEffect(() => {
        setActiveIndex(urlActiveIndex);
        setTextActiveIndex(urlActiveIndex);
    }, [urlActiveIndex]);

    const syncAllLineWidths = useCallback(() => {
        tabs.forEach((_, index) => {
            const text = textRefs.current[index];
            const button = itemRefs.current[index];
            if (text && button) {
                setLineWidth(text, button);
            }
        });
    }, [tabs]);

    useLayoutEffect(() => {
        syncAllLineWidths();
    }, [syncAllLineWidths, tabs]);

    useEffect(() => {
        const onResize = () => syncAllLineWidths();
        window.addEventListener('resize', onResize);

        return () => window.removeEventListener('resize', onResize);
    }, [syncAllLineWidths]);

    const handleTransition = (item: HTMLElement, index: number) => {
        const onTransitionEnd = (event: TransitionEvent) => {
            if (event.propertyName !== 'flex-grow' || !item.classList.contains('active')) {
                return;
            }

            setTextActiveIndex(index);
            item.removeEventListener('transitionend', onTransitionEnd);
        };

        item.addEventListener('transitionend', onTransitionEnd);
    };

    const activateTab = (index: number) => {
        const button = itemRefs.current[index];
        const text = textRefs.current[index];

        if (!button || !text) {
            return;
        }

        if (index === activeIndex) {
            return;
        }

        setTextActiveIndex(null);
        setActiveIndex(index);
        handleTransition(button, index);
    };

    const onCourseCategoriesTab = (index: number) => {
        if (index === activeIndex && courseCategoriesOpen) {
            setCourseCategoriesOpen(false);
            return;
        }

        const button = itemRefs.current[index];
        if (!button) {
            return;
        }

        if (index !== activeIndex) {
            setTextActiveIndex(null);
            setActiveIndex(index);
            handleTransition(button, index);
        }

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
            <div className="virm90-tab-bar-host flex justify-center lg:hidden">
                <menu className="menu">
                    {tabs.map((tab, index) => {
                        const Icon = resolveMobileTabBarIcon(tab.icon, index);
                        const isActive = index === activeIndex || (tab.action === 'course-categories' && courseCategoriesOpen);
                        const isTextActive = index === textActiveIndex;

                        const label = (
                            <>
                                <div className="menu__icon">
                                    <Icon />
                                </div>
                                <strong
                                    ref={(el) => {
                                        textRefs.current[index] = el;
                                    }}
                                    className={`menu__text${isTextActive ? ' active' : ''}`}
                                >
                                    {tab.label}
                                </strong>
                            </>
                        );

                        if (tab.action === 'course-categories') {
                            return (
                                <button
                                    key={tab.id}
                                    ref={(el) => {
                                        itemRefs.current[index] = el;
                                    }}
                                    type="button"
                                    className={`menu__item${isActive ? ' active' : ''}`}
                                    aria-expanded={courseCategoriesOpen}
                                    aria-haspopup="dialog"
                                    onClick={() => onCourseCategoriesTab(index)}
                                >
                                    {label}
                                </button>
                            );
                        }

                        const linkProps = tab.targetBlank
                            ? { target: '_blank', rel: 'noopener noreferrer' }
                            : {};

                        return (
                            <Link
                                key={tab.id}
                                ref={(el) => {
                                    itemRefs.current[index] = el;
                                }}
                                href={tab.href}
                                {...linkProps}
                                className={`menu__item${isActive ? ' active' : ''}`}
                                onClick={() => {
                                    setCourseCategoriesOpen(false);
                                    activateTab(index);
                                }}
                            >
                                {label}
                            </Link>
                        );
                    })}
                </menu>
            </div>

            <SiteMobileCourseCategoriesSheet
                open={courseCategoriesOpen}
                onClose={() => setCourseCategoriesOpen(false)}
                categories={courseCategories}
            />
        </>
    );
}
