import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';
import type { LucideIcon } from 'lucide-react';
import {
    BookOpen,
    FolderTree,
    GraduationCap,
    ImageIcon,
    Layers,
    LayoutGrid,
    MessageCircle,
    Newspaper,
    Sparkles,
    Star,
    Tag,
} from 'lucide-react';

const FALLBACK_ICONS: LucideIcon[] = [Sparkles, Layers, Star, LayoutGrid, FolderTree];

function primaryContentType(tabs: HomeSectionTab[]): string {
    return tabs[0]?.content_type ?? 'product';
}

/** آیکون سکشن — فعلا از نوع محتوا / قالب؛ بعدا از تنظیمات ادمین */
export function resolveHomeSectionIcon(section: HomeSection): LucideIcon {
    const template = section.settings?.section_template;

    if (template === 'hero_split') {
        return Sparkles;
    }
    if (template === 'features') {
        return LayoutGrid;
    }
    if (template === 'testimonials') {
        return MessageCircle;
    }
    if (template === 'instructors') {
        return GraduationCap;
    }
    if (template === 'banners' || template === 'catalog') {
        const ct = primaryContentType(section.tabs ?? []);
        switch (ct) {
            case 'course':
                return GraduationCap;
            case 'product':
                return BookOpen;
            case 'post':
                return Newspaper;
            case 'brand':
                return Tag;
            case 'category':
                return FolderTree;
            case 'banner':
                return ImageIcon;
            default:
                break;
        }
    }

    const ct = primaryContentType(section.tabs ?? []);
    switch (ct) {
        case 'course':
            return GraduationCap;
        case 'product':
            return BookOpen;
        case 'post':
            return Newspaper;
        case 'brand':
            return Tag;
        case 'category':
            return FolderTree;
        case 'banner':
            return ImageIcon;
        case 'features':
            return LayoutGrid;
        case 'testimonials':
            return MessageCircle;
        default:
            return FALLBACK_ICONS[section.id % FALLBACK_ICONS.length];
    }
}
