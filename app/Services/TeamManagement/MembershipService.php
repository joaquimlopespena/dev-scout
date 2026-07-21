<?php

namespace App\Services\TeamManagement;

use App\Enums\MembershipRole;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function __construct(private TenantContext $tenant) {}

    /** @param array{name: string, email: string, temporary_password: string, role: string} $data */
    public function create(User $actor, array $data): Membership
    {
        return DB::transaction(function () use ($actor, $data): Membership {
            $organization = $this->tenant->organization();
            $user = User::query()->withTrashed()->where('email', $data['email'])->first();
            $userAttributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['temporary_password'],
                'must_change_password' => true,
            ];

            if ($user === null) {
                $user = User::query()->create($userAttributes);
            } else {
                $user->restore();
                $user->update($userAttributes);
            }

            $membership = Membership::query()
                ->withTrashed()
                ->where('organization_id', $organization->id)
                ->where('user_id', $user->id)
                ->first();

            if ($membership === null) {
                $membership = Membership::query()->create([
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                    'role' => $data['role'],
                ]);
            } else {
                $membership->restore();
                $membership->update(['role' => $data['role'], 'revoked_at' => null]);
            }
            $this->audit($membership, $actor, 'membership.created', ['role' => $data['role']]);

            return $membership;
        });
    }

    /** @param array{name: string, email: string, temporary_password?: string|null, role: string} $data */
    public function update(int $membershipId, User $actor, array $data): Membership
    {
        return DB::transaction(function () use ($membershipId, $actor, $data): Membership {
            $membership = $this->findEditable($membershipId);
            $userAttributes = ['name' => $data['name'], 'email' => $data['email']];

            if (! empty($data['temporary_password'])) {
                $userAttributes['password'] = $data['temporary_password'];
                $userAttributes['must_change_password'] = true;
            }

            $membership->user->update($userAttributes);
            $membership->update(['role' => $data['role']]);
            $this->audit($membership, $actor, 'membership.updated', [
                'role' => $data['role'],
                'temporary_password_reset' => ! empty($data['temporary_password']),
            ]);

            return $membership;
        });
    }

    public function remove(int $membershipId, User $actor): void
    {
        DB::transaction(function () use ($membershipId, $actor): void {
            $membership = $this->findEditable($membershipId);
            abort_if($membership->user_id === $actor->id, 422, 'Você não pode remover seu próprio acesso.');

            $user = $membership->user;
            $this->audit($membership, $actor, 'membership.deleted', [
                'role' => $membership->role->value,
                'user_id' => $user->id,
            ]);
            $membership->delete();

            if (! $user->memberships()->exists()) {
                $user->delete();
            }
        });
    }

    private function findEditable(int $membershipId): Membership
    {
        $membership = Membership::query()
            ->with('user')
            ->whereBelongsTo($this->tenant->organization())
            ->whereKey($membershipId)
            ->whereNull('revoked_at')
            ->firstOrFail();

        abort_if($membership->role === MembershipRole::Owner, 422, 'O owner não pode ser editado.');

        return $membership;
    }

    /** @param array<string, mixed> $metadata */
    private function audit(Membership $membership, User $actor, string $action, array $metadata): void
    {
        AuditLog::query()->create([
            'organization_id' => $membership->organization_id,
            'actor_id' => $actor->id,
            'action' => $action,
            'auditable_type' => $membership->getMorphClass(),
            'auditable_id' => $membership->id,
            'metadata' => $metadata,
        ]);
    }
}
