import { Head } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import AppLayout from '../../components/AppLayout';
import DeleteMemberModal from '../../components/Team/DeleteMemberModal';
import MemberModal from '../../components/Team/MemberModal';
import type { EditableMember } from '../../components/Team/MemberModal';

type Member = EditableMember & {
    revoked_at?: string;
    can_edit: boolean;
    can_delete: boolean;
};

type Props = {
    members: Member[];
    canManage: boolean;
};

export default function Team({ members, canManage }: Props) {
    const [modalOpen, setModalOpen] = useState(false);
    const [editingMember, setEditingMember] = useState<EditableMember | null>(null);
    const [deletingMember, setDeletingMember] = useState<EditableMember | null>(null);

    const closeModal = useCallback(() => setModalOpen(false), []);
    const closeDeleteModal = useCallback(() => setDeletingMember(null), []);

    const openCreateModal = () => {
        setEditingMember(null);
        setModalOpen(true);
    };

    const openEditModal = (member: Member) => {
        setEditingMember(member);
        setModalOpen(true);
    };

    return (
        <AppLayout>
            <Head title="Equipe" />

            <header className="page-head">
                <div>
                    <p className="eyebrow">Acessos</p>
                    <h1>Equipe</h1>
                </div>
                {canManage && <button onClick={openCreateModal}>Cadastrar membro</button>}
            </header>

            <section className="panel team-panel">
                <div className="panel-head">
                    <div>
                        <h2>Membros</h2>
                        <p className="section-description">Pessoas com acesso a este workspace.</p>
                    </div>
                    <span className="count-badge">{members.length}</span>
                </div>

                <div className="rows member-rows">
                    {members.map((member) => (
                        <div key={member.id}>
                            <div className="member-info">
                                <strong>{member.user.name}</strong>
                                <div className="member-meta">
                                    <small>{member.user.email}</small>
                                    <b className="role-badge">{member.role}</b>
                                </div>
                                {member.user.must_change_password && (
                                    <em className="first-access-badge">Primeiro acesso pendente</em>
                                )}
                            </div>
                            <div className="member-actions">
                                {canManage && member.can_edit && (
                                    <button
                                        className="ghost compact"
                                        onClick={() => openEditModal(member)}
                                    >
                                        Editar
                                    </button>
                                )}
                                {canManage && member.can_delete && (
                                    <button
                                        className="danger-link compact"
                                        onClick={() => setDeletingMember(member)}
                                    >
                                        Remover
                                    </button>
                                )}
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            {modalOpen && <MemberModal member={editingMember} onClose={closeModal} />}
            {deletingMember && (
                <DeleteMemberModal member={deletingMember} onClose={closeDeleteModal} />
            )}
        </AppLayout>
    );
}
