import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import type { FormEvent } from 'react';

export type EditableMember = {
    id: number;
    role: string;
    user: { name: string; email: string; must_change_password: boolean };
};

type Props = {
    member: EditableMember | null;
    onClose: () => void;
};

export default function MemberModal({ member, onClose }: Props) {
    const form = useForm({
        name: member?.user.name ?? '',
        email: member?.user.email ?? '',
        temporary_password: '',
        role: member?.role ?? 'evaluator',
    });

    useEffect(() => {
        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', closeOnEscape);
        document.body.classList.add('modal-open');

        return () => {
            document.removeEventListener('keydown', closeOnEscape);
            document.body.classList.remove('modal-open');
        };
    }, [onClose]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onClose };

        if (member) {
            form.patch(`/team/members/${member.id}`, options);
            return;
        }

        form.post('/team/members', options);
    };

    return (
        <div className="modal-backdrop" role="presentation" onMouseDown={onClose}>
            <section
                className="modal-card"
                role="dialog"
                aria-modal="true"
                aria-labelledby="member-modal-title"
                onMouseDown={(event) => event.stopPropagation()}
            >
                <header className="modal-header">
                    <div>
                        <p className="eyebrow">Acesso à organização</p>
                        <h2 id="member-modal-title">
                            {member ? 'Editar membro' : 'Cadastrar membro'}
                        </h2>
                    </div>
                    <button
                        type="button"
                        className="modal-close"
                        onClick={onClose}
                        aria-label="Fechar"
                    >
                        ×
                    </button>
                </header>

                <form className="modal-form" onSubmit={submit}>
                    <label>
                        Nome
                        <input
                            value={form.data.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                            autoComplete="name"
                            autoFocus
                            required
                        />
                        {form.errors.name && <span className="error">{form.errors.name}</span>}
                    </label>

                    <label>
                        E-mail
                        <input
                            type="email"
                            value={form.data.email}
                            onChange={(event) => form.setData('email', event.target.value)}
                            autoComplete="email"
                            required
                        />
                        {form.errors.email && <span className="error">{form.errors.email}</span>}
                    </label>

                    <label>
                        {member ? 'Nova senha temporária (opcional)' : 'Senha temporária'}
                        <input
                            type="password"
                            value={form.data.temporary_password}
                            onChange={(event) =>
                                form.setData('temporary_password', event.target.value)
                            }
                            autoComplete="new-password"
                            required={!member}
                        />
                        {form.errors.temporary_password && (
                            <span className="error">{form.errors.temporary_password}</span>
                        )}
                    </label>

                    <label>
                        Papel
                        <select
                            value={form.data.role}
                            onChange={(event) => form.setData('role', event.target.value)}
                        >
                            <option value="admin">Admin</option>
                            <option value="evaluator">Evaluator</option>
                            <option value="viewer">Viewer</option>
                        </select>
                        {form.errors.role && <span className="error">{form.errors.role}</span>}
                    </label>

                    <p className="modal-hint">
                        {member
                            ? 'Ao informar uma nova senha temporária, o membro deverá alterá-la no próximo acesso.'
                            : 'No primeiro acesso, o membro será direcionado para criar uma senha definitiva.'}
                    </p>

                    <footer className="modal-actions">
                        <button type="button" className="ghost" onClick={onClose}>
                            Cancelar
                        </button>
                        <button type="submit" disabled={form.processing}>
                            {form.processing
                                ? 'Salvando…'
                                : member
                                  ? 'Salvar alterações'
                                  : 'Cadastrar'}
                        </button>
                    </footer>
                </form>
            </section>
        </div>
    );
}
