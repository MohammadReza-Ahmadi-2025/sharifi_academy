import AdminLayout from '@/Layouts/AdminLayout';
import InstructorLayout from '@/Layouts/InstructorLayout';
import type { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
    routePrefix: string;
    title?: string;
    description?: string;
}>;

/**
 * Shared course/class/session pages are rendered for both admin and instructor.
 * Always wrap with the matching panel shell so instructors never see admin chrome.
 */
export function CourseManageLayout({ routePrefix, title, description, children }: Props) {
    if (routePrefix === '/instructor') {
        return (
            <InstructorLayout
                title={title ?? 'مدیریت دوره'}
                description={description}
                contentVariant="plain"
            >
                {children}
            </InstructorLayout>
        );
    }

    return <AdminLayout>{children}</AdminLayout>;
}
