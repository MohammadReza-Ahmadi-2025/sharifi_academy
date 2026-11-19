import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/** Hamburger drawer state for instructor/student panel shells. */
export function usePanelMobileNav() {
    const { url } = usePage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        setOpen(false);
    }, [url]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        };

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.style.overflow = previousOverflow;
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [open]);

    return {
        open,
        openNav: () => setOpen(true),
        closeNav: () => setOpen(false),
        toggleNav: () => setOpen((value) => !value),
    };
}
