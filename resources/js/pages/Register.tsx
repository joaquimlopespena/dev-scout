import { Form, Head, Link } from '@inertiajs/react';

export default function Register() {
    return (
        <main className="auth-shell">
            <Head title="Criar conta" />
            <section className="auth-card">
                <div className="brand">DevScout</div>
                <h1>Crie seu workspace</h1>
                <Form action="/register" method="post" className="form">
                    {({ errors, processing }) => (
                        <>
                            <label>
                                Nome
                                <input name="name" autoComplete="name" required />
                            </label>
                            <label>
                                Organização
                                <input name="organization_name" required />
                            </label>
                            <label>
                                E-mail
                                <input name="email" type="email" autoComplete="email" required />
                            </label>
                            <label>
                                Senha
                                <input
                                    name="password"
                                    type="password"
                                    autoComplete="new-password"
                                    required
                                />
                            </label>
                            <label>
                                Confirmar senha
                                <input
                                    name="password_confirmation"
                                    type="password"
                                    autoComplete="new-password"
                                    required
                                />
                            </label>
                            {Object.values(errors)[0] && (
                                <p className="error">{Object.values(errors)[0]}</p>
                            )}
                            <button disabled={processing}>
                                {processing ? 'Criando…' : 'Criar conta'}
                            </button>
                        </>
                    )}
                </Form>
                <p className="muted">
                    Já possui conta? <Link href="/login">Entrar</Link>
                </p>
            </section>
        </main>
    );
}
