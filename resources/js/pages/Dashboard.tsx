import { Head, Link } from '@inertiajs/react';
import AppLayout from '../components/AppLayout';
type Props = { organization: { name: string }; role: string };
export default function Dashboard({ organization, role }: Props) {
    return (
        <AppLayout>
            <Head title="Visão geral" />
            <header className="page-head">
                <div>
                    <p className="eyebrow">
                        {organization.name} · {role}
                    </p>
                    <h1>Visão geral</h1>
                </div>
                <Link className="button" href="/developers">
                    Buscar talentos
                </Link>
            </header>
            <div className="stats">
                <article>
                    <span>Score transparente</span>
                    <strong>6 dimensões</strong>
                </article>
                <article>
                    <span>Pipeline</span>
                    <strong>6 etapas</strong>
                </article>
                <article>
                    <span>Fonte</span>
                    <strong>GitHub público</strong>
                </article>
            </div>
            <section className="panel">
                <h2>Comece por um perfil</h2>
                <p>
                    Sincronize um username do GitHub para calcular métricas e iniciar a avaliação.
                </p>
                <Link href="/developers">Ir para busca →</Link>
            </section>
        </AppLayout>
    );
}
