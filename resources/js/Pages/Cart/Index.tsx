import { Head } from '@inertiajs/react';

type CartLine = {
    id: number | string;
    product_title?: string | null;
    quantity: number;
    price: number;
    subtotal: number;
};

type Props = {
    items: CartLine[];
    totalQty: number;
};

export default function CartIndex({ items, totalQty }: Props) {
    return (
        <>
            <Head title="سبد خرید" />
            <div className="mx-auto max-w-3xl px-4 py-10">
                <h1 className="text-2xl font-bold">سبد خرید</h1>
                <p className="mt-2 text-sm text-gray-600">{totalQty} قلم</p>
                {items.length === 0 ? (
                    <p className="mt-8 text-gray-500">سبد خرید شما خالی است.</p>
                ) : (
                    <ul className="mt-8 space-y-4">
                        {items.map((item) => (
                            <li key={item.id} className="rounded-lg border p-4">
                                <div className="font-medium">{item.product_title}</div>
                                <div className="mt-1 text-sm text-gray-600">
                                    {item.quantity} × {item.price.toLocaleString('fa-IR')} ریال
                                </div>
                                <div className="mt-1 text-sm">جمع: {item.subtotal.toLocaleString('fa-IR')} ریال</div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </>
    );
}
