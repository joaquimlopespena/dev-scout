<?php

namespace App\Services\TeamManagement;

use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;

class InvitationService
{
    public function __construct(private TenantContext $tenant) {}

    /** @param array{email:string,role:string} $data */
    public function invite(User $actor, array $data): Invitation
    {
        $organization = $this->tenant->organization();
        $invitation = Invitation::query()->updateOrCreate(['organization_id' => $organization->id, 'email' => $data['email']], ['role' => $data['role'], 'token_hash' => hash('sha256', Str::random(64)), 'invited_by' => $actor->id, 'expires_at' => now()->addDays(7), 'accepted_at' => null, 'revoked_at' => null]);
        AuditLog::query()->create(['organization_id' => $organization->id, 'actor_id' => $actor->id, 'action' => 'invitation.created', 'auditable_type' => $invitation->getMorphClass(), 'auditable_id' => $invitation->id]);

        return $invitation;
    }
}
