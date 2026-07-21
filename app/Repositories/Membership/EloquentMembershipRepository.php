<?php

namespace App\Repositories\Membership;

use App\Models\Membership;
use App\Models\User;

class EloquentMembershipRepository implements MembershipRepository
{
    public function create(array $attributes): Membership
    {
        return Membership::query()->create($attributes);
    }

    public function activeForUser(User $user, ?int $organizationId = null): ?Membership
    {
        return Membership::query()->with('organization')->whereBelongsTo($user)
            ->whereNull('revoked_at')
            ->when($organizationId, fn ($query) => $query->where('organization_id', $organizationId))
            ->oldest('id')->first();
    }
}
