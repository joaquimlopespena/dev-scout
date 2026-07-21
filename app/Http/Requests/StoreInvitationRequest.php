<?php

namespace App\Http\Requests;

use App\Enums\MembershipRole;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(TenantContext $tenant): bool
    {
        return $this->user()->can('update', $tenant->organization());
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email', 'max:255'], 'role' => ['required', Rule::enum(MembershipRole::class)->except(MembershipRole::Owner)]];
    }
}
