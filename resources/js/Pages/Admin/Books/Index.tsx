import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Book = { id: number; title: string; price: number; is_published: boolean };

type Props = { books: { data: Book[] } };

export default function Index({ books }: Props) {
    const form = useForm<{
        title: string;
        description: string;
        price: number;
        is_published: boolean;
        cover: File | null;
        pdf: File | null;
    }>({
        title: '',
        description: '',
        price: 0,
        is_published: false,
        cover: null,
        pdf: null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/admin/books', {
            forceFormData: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <PanelLayout
            title="مدیریت کتاب‌ها"
            homeHref="/admin"
            links={[
                { href: '/admin', label: 'داشبورد' },
                { href: '/admin/courses', label: 'دوره‌ها' },
                { href: '/admin/books', label: 'کتاب‌ها' },
                { href: '/admin/orders', label: 'سفارش‌ها' },
            ]}
        >
            <Head title="کتاب‌ها" />
            <form onSubmit={submit} className="mb-8 space-y-3 rounded-lg border p-4">
                <Input placeholder="عنوان" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                <Input
                    type="number"
                    placeholder="قیمت"
                    value={form.data.price}
                    onChange={(e) => form.setData('price', Number(e.target.value))}
                />
                <Input type="file" accept="image/*" onChange={(e) => form.setData('cover', e.target.files?.[0] ?? null)} />
                <Input type="file" accept="application/pdf" onChange={(e) => form.setData('pdf', e.target.files?.[0] ?? null)} />
                <Button type="submit" disabled={form.processing}>
                    افزودن کتاب
                </Button>
            </form>
            <ul className="space-y-2 text-sm">
                {books.data.map((book) => (
                    <li key={book.id} className="flex justify-between rounded-lg border px-3 py-2">
                        <span>{book.title}</span>
                        <Button variant="ghost" type="button" onClick={() => router.delete(`/admin/books/${book.id}`)}>
                            حذف
                        </Button>
                    </li>
                ))}
            </ul>
        </PanelLayout>
    );
}
