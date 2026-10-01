import PanelLayout from '@/Layouts/PanelLayout';
import { Head } from '@inertiajs/react';

type Order = {
    id: number;
    status: string;
    total_amount: number;
    user?: { name: string; email: string };
    payment?: { ref_id: string | null; authority: string | null } | null;
};

type Props = { orders: { data: Order[] } };

export default function Index({ orders }: Props) {
    return (
        <PanelLayout
            title="سفارش‌ها"
            homeHref="/admin"
            links={[
                { href: '/admin', label: 'داشبورد' },
                { href: '/admin/courses', label: 'دوره‌ها' },
                { href: '/admin/books', label: 'کتاب‌ها' },
                { href: '/admin/orders', label: 'سفارش‌ها' },
            ]}
        >
            <Head title="سفارش‌ها" />
            <ul className="space-y-2 text-sm">
                {orders.data.map((order) => (
                    <li key={order.id} className="rounded-lg border px-3 py-2">
                        #{order.id} — {order.user?.name} — {order.status} — {order.total_amount.toLocaleString('fa-IR')} ریال
                    </li>
                ))}
            </ul>
        </PanelLayout>
    );
}
