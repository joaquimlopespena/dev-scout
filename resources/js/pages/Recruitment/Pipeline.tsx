import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '../../components/AppLayout';
type Entry = {
    id: number;
    status: string;
    developer: {
        id: number;
        login: string;
        name?: string;
        avatar_url?: string;
        latest_score?: { total: number };
    };
};
const stages = ['sourced', 'screening', 'interview', 'offer', 'hired', 'rejected'];
export default function Pipeline({ entries }: { entries: Entry[] }) {
    return (
        <AppLayout>
            <Head title="Pipeline" />
            <header className="page-head">
                <div>
                    <p className="eyebrow">Recrutamento</p>
                    <h1>Pipeline</h1>
                </div>
                <Link className="button" href="/developers">
                    Adicionar candidato
                </Link>
            </header>
            {entries.length === 0 ? (
                <div className="empty">
                    <strong>Pipeline vazio</strong>
                    <span>Defina o status de um desenvolvedor para adicioná-lo.</span>
                </div>
            ) : (
                <div className="board">
                    {stages.map((stage) => (
                        <section key={stage}>
                            <h3>
                                {stage}
                                <span>{entries.filter((e) => e.status === stage).length}</span>
                            </h3>
                            {entries
                                .filter((e) => e.status === stage)
                                .map((e) => (
                                    <article key={e.id}>
                                        <img src={e.developer.avatar_url} alt="" />
                                        <Link href={`/developers/${e.developer.id}`}>
                                            <strong>{e.developer.name || e.developer.login}</strong>
                                        </Link>
                                        <small>
                                            Score {Math.round(e.developer.latest_score?.total || 0)}
                                        </small>
                                        <select
                                            value={e.status}
                                            onChange={(x) =>
                                                router.patch(
                                                    `/developers/${e.developer.id}/recruitment`,
                                                    { status: x.target.value },
                                                )
                                            }
                                        >
                                            {stages.map((s) => (
                                                <option key={s}>{s}</option>
                                            ))}
                                        </select>
                                    </article>
                                ))}
                        </section>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
