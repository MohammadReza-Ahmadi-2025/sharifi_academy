export type StoryFeedItem = {
    id: number;
    user_id: number;
    user_name: string | null;
    title: string;
    type: 'text' | 'image' | 'video';
    content: string | null;
    background_color: string | null;
    visibility: string;
    published_at: string | null;
    expires_at: string | null;
    file_url: string | null;
    thumbnail_url: string | null;
    viewed: boolean;
};

export type StoryFeedGroup = {
    user_id: number;
    user_name: string | null;
    user_avatar_url: string | null;
    ring_title: string;
    preview_url: string | null;
    has_unseen: boolean;
    stories: StoryFeedItem[];
};
