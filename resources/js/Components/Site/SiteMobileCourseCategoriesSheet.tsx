import type { CourseCategoryNavNode } from '@/types';
import { Link } from '@inertiajs/react';
import { ChevronLeft, FolderTree, X } from 'lucide-react';
import { useEffect } from 'react';

type Props = {
    open: boolean;
    onClose: () => void;
    categories: CourseCategoryNavNode[];
};

function CategoryTree({ nodes, depth = 0, onNavigate }: { nodes: CourseCategoryNavNode[]; depth?: number; onNavigate: () => void }) {
    if (nodes.length === 0) {
        return null;
    }

    return (
        <ul className={depth === 0 ? 'space-y-1' : 'mt-1 space-y-1 border-r border-secondary/60 pr-3'}>
            {nodes.map((node) => (
                <li key={node.id}>
                    <Link
                        href={node.url}
                        onClick={onNavigate}
                        className="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-neutral-black transition hover:bg-surface-muted"
                        style={{ paddingInlineStart: depth > 0 ? `${depth * 0.35 + 0.75}rem` : undefined }}
                    >
                        <span className="min-w-0 truncate">{node.title}</span>
                        <ChevronLeft className="size-4 shrink-0 text-neutral-black/35" aria-hidden />
                    </Link>
                    {node.children.length > 0 && (
                        <CategoryTree nodes={node.children} depth={depth + 1} onNavigate={onNavigate} />
                    )}
                </li>
            ))}
        </ul>
    );
}

export function SiteMobileCourseCategoriesSheet({ open, onClose, categories }: Props) {
    useEffect(() => {
        if (!open) {
            return;
        }

        const previous = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        return () => {
            document.body.style.overflow = previous;
        };
    }, [open]);

    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-[60] lg:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-course-categories-title">
            <button
                type="button"
                className="absolute inset-0 bg-neutral-black/40 backdrop-blur-[2px]"
                aria-label="بستن"
                onClick={onClose}
            />
            <div className="absolute inset-x-0 bottom-0 max-h-[min(88vh,640px)] overflow-hidden rounded-t-3xl border border-secondary/80 bg-neutral-white shadow-2xl">
                <div className="flex items-center justify-between border-b border-secondary/70 px-5 py-4">
                    <div className="flex items-center gap-2">
                        <FolderTree className="size-5 text-primary" aria-hidden />
                        <span id="mobile-course-categories-title" className="font-extrabold text-neutral-black">
                            دسته‌بندی دوره‌ها
                        </span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-full p-2 text-neutral-black/60 hover:bg-surface-muted"
                        aria-label="بستن"
                    >
                        <X className="size-5" />
                    </button>
                </div>
                <div className="overflow-y-auto px-4 py-4 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                    {categories.length === 0 ? (
                        <p className="px-2 py-8 text-center text-sm text-neutral-black/60">هنوز دسته‌بندی دوره‌ای ثبت نشده است.</p>
                    ) : (
                        <CategoryTree nodes={categories} onNavigate={onClose} />
                    )}
                </div>
            </div>
        </div>
    );
}
