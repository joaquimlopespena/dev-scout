import { Head } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
type Log = { id: number; action: string; created_at: string; actor?: { name: string } };
export default function Audit({ logs }: { logs: { data: Log[] } }) {
    return (
        <AppLayout>
            <Head title="Auditoria" />
            <header className="page-head">
                <div>
                    <p className="eyebrow">Segurança</p>
                    <h1>Auditoria</h1>
                </div>
            </header>
            <section className="panel">
                {logs.data.length === 0 ? (
                    <div className="empty">
                        <strong>Sem eventos</strong>
                        <span>As ações administrativas aparecerão aqui.</span>
                    </div>
                ) : (
                    <div className="rows">
                        {logs.data.map((l) => (
                            <div key={l.id}>
                                <span>
                                    <strong>{l.action}</strong>
                                    <small>{l.actor?.name || 'Sistema'}</small>
                                </span>
                                <time>{new Date(l.created_at).toLocaleString('pt-BR')}</time>
                            </div>
                        ))}
                    </div>
                )}
            </section>
        </AppLayout>
    );
}
