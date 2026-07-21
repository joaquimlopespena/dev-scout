<?php

namespace App\Repositories\Organization;

use App\Models\Organization;

interface OrganizationRepository
{
    /** @param array{name: string, slug: string} $attributes */
    public function create(array $attributes): Organization;
}
