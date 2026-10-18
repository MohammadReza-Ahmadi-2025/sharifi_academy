import type { HomeSectionFormData } from '@/Components/Admin/Home/HomeSectionFormFields';
import { sectionToFormData } from '@/Components/Admin/Home/HomeSectionFormFields';

/** برای sync فرم بعد از redirect موفق از سرور */
export function homeSectionServerFingerprint(
    section: HomeSectionFormData & { id?: number },
): string {
    return JSON.stringify({
        id: section.id,
        title: section.title,
        status: section.status,
        settings: section.settings,
        tabs: section.tabs,
    });
}

export function homeSectionFormFromServer(
    section: HomeSectionFormData & { id?: number },
): HomeSectionFormData {
    return sectionToFormData(section);
}
