<?php

namespace App\Repositories\Membership;

use App\Models\Membership;
use App\Models\User;

interface MembershipRepository
{
    /** @param array{organization_id: int, user_id: int, role: string} $attributes */
    public function create(array $attributes): Membership;

    public function activeForUser(User $user, ?int $organizationId = null): ?Membership;
}
