import { Link, router, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

type Shared = { auth: { user: { name: string } }; flash: { success?: string } };
const links = [
    ['/dashboard', 'Visão geral'],
    ['/developers', 'Desenvolvedores'],
    ['/pipeline', 'Pipeline'],
    ['/team', 'Equipe'],
    ['/audit', 'Auditoria'],
];
export default function AppLayout({ children }: PropsWithChildren) {
    const { url, props } = usePage<Shared>();
    return (
        <div className="app-shell">
            <aside>
                <div className="brand brand-light">DevScout</div>
                <nav>
                    {links.map(([href, label]) => (
                        <Link
                            key={href}
                            href={href}
                            className={url.startsWith(href) ? 'active' : ''}
                        >
                            {label}
                        </Link>
                    ))}
                </nav>
                <div className="user">
                    <span>{props.auth.user.name}</span>
                    <button onClick={() => router.post('/logout')}>Sair</button>
                </div>
            </aside>
            <main className="content">
                {props.flash.success && <div className="flash">{props.flash.success}</div>}
                {children}
            </main>
        </div>
    );
}
