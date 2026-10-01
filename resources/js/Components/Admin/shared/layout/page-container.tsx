import type { ReactNode } from 'react';

type PageContainerProps = {
    children: ReactNode;
};

export function PageContainer({ children }: PageContainerProps) {
    return <div className="p-6">{children}</div>;
}
