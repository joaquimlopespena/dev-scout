import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
type Dev = {
    id: number;
    login: string;
    name?: string;
    avatar_url?: string;
    primary_language?: string;
    followers: number;
    public_repositories: number;
    total_stars: number;
    latest_score?: { total: number };
};
export default function Compare({ developers }: { developers: Dev[] }) {
    return (
        <AppLayout>
            <Head title="Comparar" />
            <header className="page-head">
                <div>
                    <p className="eyebrow">Até quatro perfis</p>
                    <h1>Comparação</h1>
                </div>
                <Link href="/developers">Alterar seleção</Link>
            </header>
            {developers.length < 2 ? (
                <div className="empty">
                    <strong>Selecione ao menos dois perfis</strong>
                    <span>Use as caixas na listagem de desenvolvedores.</span>
                </div>
            ) : (
                <div className="compare">
                    {developers.map((d) => (
                        <article key={d.id}>
                            <img src={d.avatar_url} alt="" />
                            <Link href={`/developers/${d.id}`}>
                                <h2>{d.name || d.login}</h2>
                            </Link>
                            <b>{Math.round(d.latest_score?.total || 0)} score</b>
                            <dl>
                                <div>
                                    <dt>Linguagem</dt>
                                    <dd>{d.primary_language || '—'}</dd>
                                </div>
                                <div>
                                    <dt>Estrelas</dt>
                                    <dd>{d.total_stars}</dd>
                                </div>
                                <div>
                                    <dt>Seguidores</dt>
                                    <dd>{d.followers}</dd>
                                </div>
                                <div>
                                    <dt>Repos</dt>
                                    <dd>{d.public_repositories}</dd>
                                </div>
                            </dl>
                        </article>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
