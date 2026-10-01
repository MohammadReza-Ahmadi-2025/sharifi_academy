import { Button } from '@/Components/ui/Button';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';

type Props = {
    book: { id: number; title: string; slug: string; price: number; description?: string | null };
};

export default function BookShow({ book }: Props) {
    const { auth } = usePage<PageProps>().props;

    return (
        <AppLayout>
            <Head title={book.title} />
            <h1 className="text-2xl font-bold">{book.title}</h1>
            <p className="mt-4">{book.description}</p>
            <p className="mt-4 font-semibold text-indigo-700">{book.price.toLocaleString('fa-IR')} ریال</p>
            {auth.user && (
                <Button className="mt-6" onClick={() => router.post('/checkout', { item_type: 'book', item_id: book.id })}>
                    خرید کتاب
                </Button>
            )}
        </AppLayout>
    );
}
