import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
type Dimension = {
    value: number | null;
    weight: number;
    available: boolean;
    evidence: Record<string, unknown>;
};
type Dev = {
    id: number;
    login: string;
    name?: string;
    avatar_url?: string;
    html_url: string;
    location?: string;
    bio?: string;
    company?: string;
    primary_language?: string;
    languages?: string[];
    followers: number;
    public_repositories: number;
    total_stars: number;
    last_synced_at?: string;
    latest_score?: {
        total: number;
        algorithm_version: string;
        dimensions: Record<string, Dimension>;
    };
};
type Props = {
    developer: Dev;
    recruitment: {
        favorite: boolean;
        notes: { id: number; body: string; created_at: string }[];
        pipeline?: string;
        tags: string[];
    };
};
const labels: Record<string, string> = {
    technical_impact: 'Impacto técnico',
    contribution_quality: 'Qualidade',
    consistency: 'Consistência',
    technical_depth: 'Profundidade',
    collaboration: 'Colaboração',
    profile_completeness: 'Perfil',
};
export default function Show({ developer, recruitment }: Props) {
    const form = useForm({
        note: '',
        status: recruitment.pipeline || 'sourced',
        tags: recruitment.tags.join(', '),
    });
    const save = () =>
        router.patch(`/developers/${developer.id}/recruitment`, {
            note: form.data.note,
            status: form.data.status,
            tags: form.data.tags
                .split(',')
                .map((x) => x.trim())
                .filter(Boolean),
        });
    return (
        <AppLayout>
            <Head title={developer.name || developer.login} />
            <Link href="/developers">← Voltar</Link>
            <section className="profile-head">
                <img src={developer.avatar_url} alt="" />
                <div>
                    <p className="eyebrow">@{developer.login}</p>
                    <h1>{developer.name || developer.login}</h1>
                    <p>{developer.bio || 'Biografia não informada.'}</p>
                    <span>
                        {developer.location || 'Local não informado'} ·{' '}
                        {developer.company || 'Sem empresa'}
                    </span>
                </div>
                <div className="score">
                    <strong>{Math.round(developer.latest_score?.total || 0)}</strong>
                    <span>score</span>
                </div>
            </section>
            <div className="two-cols">
                <section className="panel">
                    <h2>Score explicável</h2>
                    <div className="dimensions">
                        {Object.entries(developer.latest_score?.dimensions || {}).map(
                            ([key, d]) => (
                                <div key={key}>
                                    <span>
                                        {labels[key] || key}
                                        <small>{Math.round(d.weight * 100)}%</small>
                                    </span>
                                    <strong>{d.available ? Math.round(d.value || 0) : '—'}</strong>
                                </div>
                            ),
                        )}
                    </div>
                    <small>
                        Algoritmo {developer.latest_score?.algorithm_version || 'indisponível'}
                    </small>
                </section>
                <section className="panel">
                    <h2>Sinais públicos</h2>
                    <dl>
                        <div>
                            <dt>Estrelas</dt>
                            <dd>{developer.total_stars}</dd>
                        </div>
                        <div>
                            <dt>Seguidores</dt>
                            <dd>{developer.followers}</dd>
                        </div>
                        <div>
                            <dt>Repositórios</dt>
                            <dd>{developer.public_repositories}</dd>
                        </div>
                        <div>
                            <dt>Linguagem</dt>
                            <dd>{developer.primary_language || '—'}</dd>
                        </div>
                    </dl>
                    <a href={developer.html_url} target="_blank" rel="noreferrer">
                        Abrir GitHub ↗
                    </a>
                </section>
            </div>
            <section className="panel">
                <div className="panel-head">
                    <h2>Recrutamento</h2>
                    <button
                        className={recruitment.favorite ? 'danger' : 'ghost'}
                        onClick={() =>
                            router.patch(`/developers/${developer.id}/recruitment`, {
                                favorite: !recruitment.favorite,
                            })
                        }
                    >
                        {recruitment.favorite ? 'Remover favorito' : 'Favoritar'}
                    </button>
                </div>
                <div className="form-grid">
                    <label>
                        Status
                        <select
                            value={form.data.status}
                            onChange={(e) => form.setData('status', e.target.value)}
                        >
                            {[
                                'sourced',
                                'screening',
                                'interview',
                                'offer',
                                'hired',
                                'rejected',
                            ].map((s) => (
                                <option key={s}>{s}</option>
                            ))}
                        </select>
                    </label>
                    <label>
                        Tags
                        <input
                            value={form.data.tags}
                            onChange={(e) => form.setData('tags', e.target.value)}
                            placeholder="Laravel, Backend"
                        />
                    </label>
                    <label className="full">
                        Nota
                        <textarea
                            value={form.data.note}
                            onChange={(e) => form.setData('note', e.target.value)}
                            placeholder="Registre uma evidência ou avaliação"
                        />
                    </label>
                    <button onClick={save} disabled={form.processing}>
                        Salvar
                    </button>
                </div>
                {recruitment.notes.length > 0 && (
                    <div className="notes">
                        {recruitment.notes.map((n) => (
                            <p key={n.id}>{n.body}</p>
                        ))}
                    </div>
                )}
            </section>
        </AppLayout>
    );
}
