import type { User } from '@/types';

export type ProfileUser = {
    name: string;
    roleLabel: string;
    email: string;
    phone: string;
    initials: string;
    avatarUrl?: string | null;
    avatarAlt?: string | null;
};

const roleLabels: Record<string, string> = {
    'super-admin': 'مدیر ارشد',
    admin: 'مدیر',
    instructor: 'مدرس',
    student: 'دانش‌آموز',
};

export function mapAuthUserToProfile(user: User): ProfileUser {
    const fullName = [user.name, user.family].filter(Boolean).join(' ') || user.email;
    const primaryRole = user.roles[0] ?? '';
    const initials = fullName
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    return {
        name: fullName,
        roleLabel: roleLabels[primaryRole] ?? (primaryRole || 'کاربر'),
        email: user.email,
        phone: user.mobile ?? '',
        initials: initials || '؟',
        avatarUrl: user.avatar?.url ?? null,
        avatarAlt: user.avatar?.alt ?? null,
    };
}
