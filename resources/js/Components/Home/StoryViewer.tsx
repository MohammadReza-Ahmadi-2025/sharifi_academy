import { recordStoryView } from '@/Components/Home/story-api';
import type { StoryFeedGroup, StoryFeedItem } from '@/Components/Home/stories-types';
import { cn } from '@/lib/utils';
import { X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

const STORY_DURATION_MS = 6000;

type Props = {
    group: StoryFeedGroup;
    initialStoryIndex?: number;
    isAuthenticated: boolean;
    onClose: () => void;
    onStoryViewed: (storyId: number) => void;
    onFinished: () => void;
};

export function StoryViewer({
    group,
    initialStoryIndex = 0,
    isAuthenticated,
    onClose,
    onStoryViewed,
    onFinished,
}: Props) {
    const stories = group.stories;
    const [index, setIndex] = useState(initialStoryIndex);
    const [progress, setProgress] = useState(0);
    const videoRef = useRef<HTMLVideoElement>(null);
    const recordedRef = useRef<Set<number>>(new Set());

    const story = stories[index];

    const goNext = useCallback(() => {
        if (index < stories.length - 1) {
            setIndex((i) => i + 1);
            setProgress(0);
            return;
        }
        onFinished();
    }, [index, onFinished, stories.length]);

    const goPrev = useCallback(() => {
        if (index > 0) {
            setIndex((i) => i - 1);
            setProgress(0);
        }
    }, [index]);

    useEffect(() => {
        if (!story) {
            return;
        }

        if (isAuthenticated && !recordedRef.current.has(story.id)) {
            recordedRef.current.add(story.id);
            recordStoryView(story.id);
            onStoryViewed(story.id);
        }
    }, [story, isAuthenticated, onStoryViewed]);

    useEffect(() => {
        if (!story || story.type === 'video') {
            return;
        }

        const started = performance.now();
        const timer = window.setInterval(() => {
            const elapsed = performance.now() - started;
            const ratio = Math.min(elapsed / STORY_DURATION_MS, 1);
            setProgress(ratio);
            if (ratio >= 1) {
                window.clearInterval(timer);
                goNext();
            }
        }, 50);

        return () => window.clearInterval(timer);
    }, [story, goNext]);

    useEffect(() => {
        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
            if (event.key === 'ArrowLeft') {
                goNext();
            }
            if (event.key === 'ArrowRight') {
                goPrev();
            }
        };
        window.addEventListener('keydown', onKey);

        return () => window.removeEventListener('keydown', onKey);
    }, [goNext, goPrev, onClose]);

    if (!story) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-[80] flex items-center justify-center bg-neutral-black/95 p-4">
            <div className="relative flex h-full max-h-[90vh] w-full max-w-md flex-col overflow-hidden rounded-2xl bg-neutral-black shadow-2xl">
                <div className="absolute inset-x-0 top-0 z-10 flex gap-1 p-3 pt-4">
                    {stories.map((item, i) => (
                        <div key={item.id} className="h-0.5 flex-1 overflow-hidden rounded-full bg-white/30">
                            <div
                                className="h-full bg-white transition-[width] duration-100 ease-linear"
                                style={{
                                    width: i < index ? '100%' : i === index ? `${progress * 100}%` : '0%',
                                }}
                            />
                        </div>
                    ))}
                </div>

                <div className="flex items-center justify-between gap-2 px-4 pb-2 pt-10 text-white">
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold">{story.title || group.ring_title}</p>
                        <p className="truncate text-xs text-white/70">{group.user_name ?? 'کاربر'}</p>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-full p-1.5 text-white/90 transition hover:bg-white/10"
                        aria-label="بستن"
                    >
                        <X className="size-5" />
                    </button>
                </div>

                <div className="relative flex flex-1 items-center justify-center">
                    <StorySlide story={story} videoRef={videoRef} onVideoEnded={goNext} onVideoProgress={setProgress} />

                    <button
                        type="button"
                        className="absolute inset-y-0 right-0 w-1/3"
                        aria-label="استوری قبلی"
                        onClick={goPrev}
                    />
                    <button
                        type="button"
                        className="absolute inset-y-0 left-0 w-1/3"
                        aria-label="استوری بعدی"
                        onClick={goNext}
                    />
                </div>

                {story.content && story.type !== 'text' && (
                    <p className="px-4 pb-6 text-center text-sm text-white/90">{story.content}</p>
                )}
            </div>
        </div>
    );
}

function StorySlide({
    story,
    videoRef,
    onVideoEnded,
    onVideoProgress,
}: {
    story: StoryFeedItem;
    videoRef: React.RefObject<HTMLVideoElement | null>;
    onVideoEnded: () => void;
    onVideoProgress: (ratio: number) => void;
}) {
    if (story.type === 'image' && story.file_url) {
        return <img src={story.file_url} alt="" className="max-h-full max-w-full object-contain" />;
    }

    if (story.type === 'video' && story.file_url) {
        return (
            <video
                ref={videoRef}
                key={story.id}
                src={story.file_url}
                className="max-h-full max-w-full object-contain"
                playsInline
                autoPlay
                onEnded={onVideoEnded}
                onTimeUpdate={(event) => {
                    const el = event.currentTarget;
                    if (el.duration > 0) {
                        onVideoProgress(el.currentTime / el.duration);
                    }
                }}
            />
        );
    }

    return (
        <div
            className={cn('flex h-full w-full items-center justify-center px-8 text-center text-lg font-semibold text-white')}
            style={{ backgroundColor: story.background_color ?? '#1e293b' }}
        >
            {story.content}
        </div>
    );
}
