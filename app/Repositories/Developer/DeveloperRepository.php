<?php

namespace App\Repositories\Developer;

use App\Models\Developer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DeveloperRepository
{
    /** @param array<string,mixed> $filters */
    public function paginate(array $filters, int $organizationId): LengthAwarePaginator;

    /** @param array<string,mixed> $attributes */
    public function upsertFromGitHub(array $attributes): Developer;
}
