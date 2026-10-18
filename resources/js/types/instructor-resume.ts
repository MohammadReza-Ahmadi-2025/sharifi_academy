export type ResumeAttachment = {
    id: number;
    url: string;
    name: string;
    size: number;
    mime_type: string;
};

export type ResumeEntry = {
    id: number;
    type: string;
    type_label?: string;
    title: string;
    organization?: string | null;
    period?: string | null;
    description?: string | null;
    link?: string | null;
    is_current?: boolean;
    attachments?: ResumeAttachment[];
    start_date?: string | null;
    end_date?: string | null;
    display_order?: number;
};

export type ResumeSection = {
    type: string;
    label: string;
    items: ResumeEntry[];
};

export type InstructorResumeProfile = {
    id: number;
    fullName: string;
    headline?: string | null;
    bio?: string | null;
    avatar?: { url: string; alt?: string | null; title?: string | null } | null;
    profile_url?: string;
    sections: ResumeSection[];
    skills?: { id: number; name: string; level?: number | null }[];
};
