import { Form, Head, Link } from '@inertiajs/react';

export default function Login() {
    return (
        <main className="auth-shell">
            <Head title="Entrar" />
            <section className="auth-card">
                <div className="brand">DevScout</div>
                <h1>Entre na sua conta</h1>
                <Form action="/login" method="post" className="form">
                    {({ errors, processing }) => (
                        <>
                            <label>
                                E-mail
                                <input
                                    name="email"
                                    type="email"
                                    autoComplete="email"
                                    autoFocus
                                    required
                                />
                            </label>
                            {errors.email && <p className="error">{errors.email}</p>}
                            <label>
                                Senha
                                <input
                                    name="password"
                                    type="password"
                                    autoComplete="current-password"
                                    required
                                />
                            </label>
                            <label className="check">
                                <input name="remember" type="checkbox" value="1" /> Manter conectado
                            </label>
                            <button disabled={processing}>
                                {processing ? 'Entrando…' : 'Entrar'}
                            </button>
                        </>
                    )}
                </Form>
                <p className="muted">
                    Primeiro acesso? <Link href="/register">Criar conta</Link>
                </p>
            </section>
        </main>
    );
}
