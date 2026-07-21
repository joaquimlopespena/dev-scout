<?php

namespace App\Repositories\Organization;

use App\Models\Organization;

class EloquentOrganizationRepository implements OrganizationRepository
{
    public function create(array $attributes): Organization
    {
        return Organization::query()->create($attributes);
    }
}
