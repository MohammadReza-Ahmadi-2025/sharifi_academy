/** همان منطق Nexin — جلوگیری از فعال شدن والد وقتی مسیر فرزند فعال است */
export function isNavigationItemActive(
    pathname: string,
    href: string,
    allHrefs: readonly string[],
): boolean {
    if (href === '/') {
        return pathname === '/';
    }

    const matches = pathname === href || pathname.startsWith(`${href}/`);
    if (!matches) {
        return false;
    }

    return !allHrefs.some(
        (other) =>
            other !== href &&
            other !== '/' &&
            other.length > href.length &&
            other.startsWith(`${href}/`) &&
            (pathname === other || pathname.startsWith(`${other}/`)),
    );
}
