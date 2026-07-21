<?php

namespace App\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(TenantContext $tenant): Response
    {
        $organization = $tenant->organization();
        Gate::authorize('view', $organization);

        return Inertia::render('Dashboard', [
            'organization' => $organization->only(['id', 'name', 'slug']),
            'role' => $tenant->membership()->role->value,
        ]);
    }
}
