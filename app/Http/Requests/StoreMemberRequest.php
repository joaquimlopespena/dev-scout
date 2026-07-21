<?php

namespace App\Http\Requests;

use App\Enums\MembershipRole;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreMemberRequest extends FormRequest
{
    public function authorize(TenantContext $tenant): bool
    {
        return $this->user()->can('update', $tenant->organization());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->withoutTrashed()],
            'temporary_password' => ['required', 'string', Password::defaults()],
            'role' => ['required', Rule::enum(MembershipRole::class)->except(MembershipRole::Owner)],
        ];
    }
}
