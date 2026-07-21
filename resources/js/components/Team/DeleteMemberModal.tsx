import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import type { EditableMember } from './MemberModal';

type Props = {
    member: EditableMember;
    onClose: () => void;
};

export default function DeleteMemberModal({ member, onClose }: Props) {
    const form = useForm({});

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

    const remove = () => {
        form.delete(`/team/members/${member.id}`, {
            preserveScroll: true,
            onSuccess: onClose,
        });
    };

    return (
        <div className="modal-backdrop" role="presentation" onMouseDown={onClose}>
            <section
                className="modal-card confirm-card"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="delete-member-title"
                aria-describedby="delete-member-description"
                onMouseDown={(event) => event.stopPropagation()}
            >
                <header className="modal-header">
                    <div>
                        <p className="eyebrow danger-text">Remover acesso</p>
                        <h2 id="delete-member-title">Remover {member.user.name}?</h2>
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
                <div className="confirm-body">
                    <p id="delete-member-description">
                        O membro perderá o acesso ao workspace. O registro será preservado no
                        histórico.
                    </p>
                    <footer className="modal-actions">
                        <button type="button" className="ghost" onClick={onClose}>
                            Cancelar
                        </button>
                        <button
                            type="button"
                            className="danger"
                            onClick={remove}
                            disabled={form.processing}
                        >
                            {form.processing ? 'Removendo…' : 'Remover membro'}
                        </button>
                    </footer>
                </div>
            </section>
        </div>
    );
}
