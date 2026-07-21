<?php

namespace App\Policies;

use App\Enums\MembershipRole;
use App\Models\Developer;
use App\Models\User;

class DeveloperPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->memberships()->whereNull('revoked_at')->exists();
    }

    public function view(User $user, Developer $developer): bool
    {
        return $this->viewAny($user);
    }

    public function updateRecruitment(User $user, Developer $developer): bool
    {
        return $user->memberships()->whereNull('revoked_at')->whereIn('role', [MembershipRole::Owner->value, MembershipRole::Admin->value, MembershipRole::Evaluator->value])->exists();
    }
}
