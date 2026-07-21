import { Form, Head, Link } from '@inertiajs/react';

export default function FirstPassword() {
    return (
        <main className="auth-shell first-access-shell">
            <Head title="Definir nova senha" />
            <section className="auth-card first-access-card">
                <div className="brand">DevScout</div>
                <p className="eyebrow">Primeiro acesso</p>
                <h1>Defina sua nova senha</h1>
                <p className="auth-description">
                    Por segurança, substitua a senha temporária antes de acessar o workspace.
                </p>

                <Form action="/first-access/password" method="put" className="form">
                    {({ errors, processing }) => (
                        <>
                            <label>
                                Senha temporária
                                <input
                                    name="current_password"
                                    type="password"
                                    autoComplete="current-password"
                                    autoFocus
                                    required
                                />
                                {errors.current_password && (
                                    <span className="error">{errors.current_password}</span>
                                )}
                            </label>
                            <label>
                                Nova senha
                                <input
                                    name="password"
                                    type="password"
                                    autoComplete="new-password"
                                    required
                                />
                                {errors.password && (
                                    <span className="error">{errors.password}</span>
                                )}
                            </label>
                            <label>
                                Confirmar nova senha
                                <input
                                    name="password_confirmation"
                                    type="password"
                                    autoComplete="new-password"
                                    required
                                />
                            </label>
                            <button disabled={processing}>
                                {processing ? 'Atualizando…' : 'Definir senha e continuar'}
                            </button>
                        </>
                    )}
                </Form>

                <Link href="/logout" method="post" as="button" className="logout-link">
                    Sair desta conta
                </Link>
            </section>
        </main>
    );
}
