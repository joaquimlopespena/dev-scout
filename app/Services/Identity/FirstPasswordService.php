<?php

namespace App\Services\Identity;

use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FirstPasswordService
{
    public function update(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password): void {
            $user->update([
                'password' => $password,
                'must_change_password' => false,
            ]);

            $membership = Membership::query()
                ->whereBelongsTo($user)
                ->whereNull('revoked_at')
                ->oldest('id')
                ->first();

            if ($membership !== null) {
                AuditLog::query()->create([
                    'organization_id' => $membership->organization_id,
                    'actor_id' => $user->id,
                    'action' => 'user.first_password_changed',
                    'auditable_type' => $user->getMorphClass(),
                    'auditable_id' => $user->id,
                ]);
            }
        });
    }
}
