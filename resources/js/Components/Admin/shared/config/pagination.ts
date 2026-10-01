export const DEFAULT_PAGE_SIZE = 25;

export const PAGE_SIZE_OPTIONS = [25, 50, 75, 100] as const;

export type PageSize = (typeof PAGE_SIZE_OPTIONS)[number];
