import { Star } from 'lucide-react';
import { cn } from '@/lib/utils';

export function clampTestimonialRating(value: unknown): number {
    const n = typeof value === 'number' ? value : Number.parseInt(String(value ?? ''), 10);
    if (!Number.isFinite(n)) {
        return 5;
    }

    return Math.min(5, Math.max(1, Math.round(n)));
}

type Props = {
    rating: number;
    className?: string;
    sizeClass?: string;
};

export function HomeTestimonialStars({ rating, className, sizeClass = 'size-4' }: Props) {
    const value = clampTestimonialRating(rating);

    return (
        <div className={cn('flex items-center gap-0.5', className)} aria-label={`امتیاز ${value} از ۵`}>
            {Array.from({ length: 5 }, (_, index) => {
                const filled = index < value;

                return (
                    <Star
                        key={index}
                        className={cn(sizeClass, filled ? 'fill-amber-400 text-amber-400' : 'text-neutral-black/20')}
                        strokeWidth={1.5}
                        aria-hidden
                    />
                );
            })}
        </div>
    );
}
