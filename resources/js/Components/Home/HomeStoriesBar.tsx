import { StoryViewer } from '@/Components/Home/StoryViewer';
import type { StoryFeedGroup } from '@/Components/Home/stories-types';
import { cn } from '@/lib/utils';
import { useCallback, useMemo, useState } from 'react';

type Props = {
    initialGroups: StoryFeedGroup[];
    isAuthenticated: boolean;
};

export function HomeStoriesBar({ initialGroups, isAuthenticated }: Props) {
    const [groups, setGroups] = useState(initialGroups);
    const [activeGroupIndex, setActiveGroupIndex] = useState<number | null>(null);

    const markStoryViewed = useCallback((storyId: number) => {
        setGroups((prev) =>
            prev.map((group) => {
                const stories = group.stories.map((story) =>
                    story.id === storyId ? { ...story, viewed: true } : story,
                );
                const hasUnseen = stories.some((s) => !s.viewed);

                return { ...group, stories, has_unseen: hasUnseen };
            }),
        );
    }, []);

    const openGroup = (index: number) => {
        setActiveGroupIndex(index);
    };

    const closeViewer = () => {
        setActiveGroupIndex(null);
    };

    const sortedGroups = useMemo(() => {
        const copy = [...groups];
        copy.sort((a, b) => {
            if (a.has_unseen !== b.has_unseen) {
                return a.has_unseen ? -1 : 1;
            }
            return 0;
        });
        return copy;
    }, [groups]);

    const handleFinished = () => {
        if (activeGroupIndex === null) {
            return;
        }
        const next = activeGroupIndex + 1;
        if (next < sortedGroups.length) {
            setActiveGroupIndex(next);
            return;
        }
        closeViewer();
    };

    const activeGroup = activeGroupIndex !== null ? sortedGroups[activeGroupIndex] : null;

    if (sortedGroups.length === 0) {
        return null;
    }

    return (
        <>
            <section className="border-b border-secondary/60 bg-neutral-white">
                <div className="mx-auto max-w-6xl px-4 py-4">
                    <div className="flex gap-4 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        {sortedGroups.map((group, index) => (
                            <StoryBubble key={group.user_id} group={group} onClick={() => openGroup(index)} />
                        ))}
                    </div>
                </div>
            </section>

            {activeGroup && (
                <StoryViewer
                    group={activeGroup}
                    isAuthenticated={isAuthenticated}
                    onClose={closeViewer}
                    onStoryViewed={markStoryViewed}
                    onFinished={handleFinished}
                />
            )}
        </>
    );
}

function StoryBubble({ group, onClick }: { group: StoryFeedGroup; onClick: () => void }) {
    const ringClass = group.has_unseen
        ? 'bg-gradient-to-tr from-primary via-amber-400 to-pink-500 p-[3px]'
        : 'bg-neutral-black/15 p-[2px]';

    const ringImage = group.preview_url ?? group.user_avatar_url;

    return (
        <button type="button" onClick={onClick} className="flex w-[4.5rem] shrink-0 flex-col items-center gap-2">
            <span className={cn('rounded-full', ringClass)}>
                <span className="flex size-[4.25rem] items-center justify-center overflow-hidden rounded-full bg-neutral-white">
                    {ringImage ? (
                        <img src={ringImage} alt="" className="size-full object-cover" />
                    ) : (
                        <span className="text-lg font-bold text-primary">
                            {(group.user_name ?? '؟').charAt(0)}
                        </span>
                    )}
                </span>
            </span>
            <span className="max-w-[4.5rem] truncate text-center text-xs font-medium text-neutral-black">
                {group.ring_title}
            </span>
        </button>
    );
}
