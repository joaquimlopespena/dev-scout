<?php

namespace App\Support\Tenancy;

use App\Models\Membership;
use App\Models\Organization;

class TenantContext
{
    private ?Membership $membership = null;

    public function set(Membership $membership): void
    {
        $this->membership = $membership;
    }

    public function organization(): Organization
    {
        abort_if($this->membership === null, 403, 'No active organization.');

        return Organization::query()->findOrFail($this->membership->organization_id);
    }

    public function membership(): Membership
    {
        abort_if($this->membership === null, 403, 'No active organization.');

        return $this->membership;
    }
}
