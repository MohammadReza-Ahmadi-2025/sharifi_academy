export type RichHtmlTocItem = {
    id: string;
    title: string;
    level: 1 | 2 | 3 | 4;
};

function slugifyHeading(title: string, used: Set<string>): string {
    const base =
        title
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\p{L}\p{N}\u0600-\u06FF-]/gu, '')
            .slice(0, 72) || 'section';

    let id = base;
    let n = 2;
    while (used.has(id)) {
        id = `${base}-${n++}`;
    }
    used.add(id);

    return id;
}

function wrapTables(root: ParentNode): void {
    root.querySelectorAll('table').forEach((table) => {
        if (table.parentElement?.classList.contains('site-rich-html__table-scroll')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'site-rich-html__table-scroll';
        table.parentNode?.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
}

function assignHeadingIds(root: ParentNode): RichHtmlTocItem[] {
    const used = new Set<string>();
    const toc: RichHtmlTocItem[] = [];

    // CKEditor often uses h1 for section titles inside the body — include them in TOC.
    root.querySelectorAll('h1, h2, h3, h4').forEach((node) => {
        const el = node as HTMLElement;
        const title = el.textContent?.trim() ?? '';
        if (title === '') {
            return;
        }

        const level = Number(el.tagName.charAt(1)) as 1 | 2 | 3 | 4;
        if (!el.id) {
            el.id = slugifyHeading(title, used);
        } else {
            used.add(el.id);
        }

        el.classList.add('site-rich-html__heading-anchor');
        toc.push({ id: el.id, title, level });
    });

    return toc;
}

/**
 * Wrap wide tables and collect heading anchors for blog-style pages.
 */
export function processBlogRichHtml(html: string): { html: string; toc: RichHtmlTocItem[] } {
    if (html === '' || typeof DOMParser === 'undefined') {
        return { html, toc: [] };
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');
    const toc = assignHeadingIds(doc.body);
    wrapTables(doc.body);

    return { html: doc.body.innerHTML, toc };
}

export function wrapRichHtmlTables(root: HTMLElement | null): void {
    if (root === null) {
        return;
    }

    wrapTables(root);
}
