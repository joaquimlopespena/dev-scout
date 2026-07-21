import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, type PropsWithChildren } from 'react';
import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';
import BrandLogo from './BrandLogo';

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

    useEffect(() => {
        if (props.flash.success) {
            Toastify({
                text: props.flash.success,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                style: { background: '#16724a', color: '#fff', borderRadius: '8px' }
            }).showToast();
        }
    }, [props.flash.success]);

    return (
        <div className="app-shell">
            <aside>
                <BrandLogo compact href="/dashboard" />
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
                {children}
            </main>
        </div>
    );
}
