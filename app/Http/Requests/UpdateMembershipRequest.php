<?php

namespace App\Http\Requests;

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateMembershipRequest extends FormRequest
{
    public function authorize(TenantContext $tenant): bool
    {
        return $this->user()->can('update', $tenant->organization());
    }

    public function rules(TenantContext $tenant): array
    {
        $membership = Membership::query()
            ->whereBelongsTo($tenant->organization())
            ->whereKey((int) $this->route('membership'))
            ->whereNull('revoked_at')
            ->firstOrFail();

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->withoutTrashed()->ignore($membership->user_id)],
            'temporary_password' => ['nullable', 'string', Password::defaults()],
            'role' => ['required', Rule::enum(MembershipRole::class)->except(MembershipRole::Owner)],
        ];
    }
}
