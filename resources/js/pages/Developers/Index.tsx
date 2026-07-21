import { Form, Head, Link } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';

type DiscoveryCandidate = {
    login: string;
    avatar_url: string;
    html_url: string;
    total_stars: number;
    selected: boolean;
};

type Discovery = {
    data: DiscoveryCandidate[];
    total: number;
    page: number;
    last_page: number;
    previous_page_url: string | null;
    next_page_url: string | null;
};

type Props = {
    discovery: Discovery | null;
    discoveryFilters: Record<string, string | number | null>;
};

export default function Index({ discovery, discoveryFilters }: Props) {
    return (
        <AppLayout>
            <Head title="Desenvolvedores" />
            <header className="page-head">
                <div>
                    <p className="eyebrow">Descoberta</p>
                    <h1>Desenvolvedores</h1>
                </div>
            </header>

            <section className="panel discovery-panel">
                <div className="panel-head">
                    <div>
                        <h2>Encontrar talentos no GitHub</h2>
                        <p className="section-description">
                            Pesquise candidatos públicos e selecione os melhores para o Pipeline.
                        </p>
                    </div>
                </div>
                <Form action="/developers/discover" method="get" className="discovery-filters">
                    {({ errors, processing }) => (
                        <>
                            <label>
                                Usuário
                                <input
                                    name="username"
                                    defaultValue={String(discoveryFilters.username ?? '')}
                                    placeholder="Username do GitHub"
                                />
                                {errors.username && (
                                    <span className="error">{errors.username}</span>
                                )}
                            </label>
                            <label>
                                Linguagem
                                <input
                                    name="language"
                                    defaultValue={String(discoveryFilters.language ?? '')}
                                    placeholder="PHP"
                                />
                                {errors.language && (
                                    <span className="error">{errors.language}</span>
                                )}
                            </label>
                            <label>
                                Localização
                                <input
                                    name="location"
                                    defaultValue={String(discoveryFilters.location ?? '')}
                                    placeholder="Brasil"
                                />
                            </label>
                            <label>
                                Mín. repositórios
                                <input
                                    name="min_repositories"
                                    type="number"
                                    min="0"
                                    placeholder="10"
                                    defaultValue={String(discoveryFilters.min_repositories ?? '')}
                                />
                            </label>
                            <label>
                                Mín. estrelas
                                <input
                                    name="min_stars"
                                    type="number"
                                    min="0"
                                    placeholder="50"
                                    defaultValue={String(discoveryFilters.min_stars ?? '')}
                                />
                            </label>
                            <label>
                                Ordenar por
                                <select
                                    name="sort"
                                    defaultValue={String(discoveryFilters.sort ?? 'followers')}
                                >
                                    <option value="followers">Seguidores</option>
                                    <option value="repositories">Repositórios</option>
                                    <option value="joined">Data de entrada</option>
                                </select>
                            </label>
                            <button disabled={processing}>
                                {processing ? 'Buscando…' : 'Buscar candidatos'}
                            </button>
                        </>
                    )}
                </Form>
            </section>

            {discovery && (
                <section className="discovery-results">
                    <div className="results-heading">
                        <strong>{discovery.data.length} candidatos nesta página</strong>
                        <Link href="/pipeline">Ver Pipeline</Link>
                    </div>
                    {discovery.data.length === 0 ? (
                        <div className="empty">
                            <strong>Nenhum candidato encontrado</strong>
                            <span>Ajuste os critérios da busca.</span>
                        </div>
                    ) : (
                        <div className="candidate-grid">
                            {discovery.data.map((candidate) => (
                                <article className="candidate-card" key={candidate.login}>
                                    <img src={candidate.avatar_url} alt="" />
                                    <div>
                                        <strong>@{candidate.login}</strong>
                                        <span>
                                            ★ {candidate.total_stars.toLocaleString('pt-BR')} ·{' '}
                                            <a
                                                href={candidate.html_url}
                                                target="_blank"
                                                rel="noreferrer"
                                            >
                                                GitHub
                                            </a>
                                        </span>
                                    </div>
                                    {candidate.selected ? (
                                        <Link className="button compact" href="/pipeline">
                                            No Pipeline
                                        </Link>
                                    ) : (
                                        <Form action="/developers/sync" method="post">
                                            {({ processing }) => (
                                                <>
                                                    <input
                                                        type="hidden"
                                                        name="login"
                                                        value={candidate.login}
                                                    />
                                                    <button
                                                        className="compact"
                                                        disabled={processing}
                                                    >
                                                        {processing
                                                            ? 'Selecionando…'
                                                            : 'Selecionar perfil'}
                                                    </button>
                                                </>
                                            )}
                                        </Form>
                                    )}
                                </article>
                            ))}
                        </div>
                    )}
                    <div className="pagination">
                        {discovery.previous_page_url && (
                            <Link href={discovery.previous_page_url}>‹ Anterior</Link>
                        )}
                        <span>
                            Página {discovery.page} de {discovery.last_page}
                        </span>
                        {discovery.next_page_url && (
                            <Link href={discovery.next_page_url}>Próxima ›</Link>
                        )}
                    </div>
                </section>
            )}
        </AppLayout>
    );
}
