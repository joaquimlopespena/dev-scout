<?php

namespace App\Services\Identity;

use App\Enums\MembershipRole;
use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\Membership\MembershipRepository;
use App\Repositories\Organization\OrganizationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationService
{
    public function __construct(
        private OrganizationRepository $organizations,
        private MembershipRepository $memberships,
    ) {}

    /** @param array{name: string, email: string, password: string, organization_name: string} $data */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
            ]);
            $organization = $this->organizations->create([
                'name' => $data['organization_name'],
                'slug' => Str::slug($data['organization_name']).'-'.Str::lower(Str::random(6)),
            ]);
            $this->memberships->create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'role' => MembershipRole::Owner->value,
            ]);
            AuditLog::query()->create([
                'organization_id' => $organization->id,
                'actor_id' => $user->id,
                'action' => 'organization.created',
                'auditable_type' => $organization->getMorphClass(),
                'auditable_id' => $organization->id,
            ]);

            return $user;
        });
    }
}
