<?php

namespace App\Policies;

use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $user->memberships()->whereBelongsTo($organization)->whereNull('revoked_at')->exists();
    }

    public function update(User $user, Organization $organization): bool
    {
        $membership = Membership::query()->whereBelongsTo($user)->whereBelongsTo($organization)->whereNull('revoked_at')->first();

        return $membership?->role->canManageOrganization() ?? false;
    }
}
