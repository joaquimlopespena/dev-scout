<?php

namespace App\Http\Requests;

use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

class DestroyMembershipRequest extends FormRequest
{
    public function authorize(TenantContext $tenant): bool
    {
        return $this->user()->can('update', $tenant->organization());
    }

    public function rules(): array
    {
        return [];
    }
}
