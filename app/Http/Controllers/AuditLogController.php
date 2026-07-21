<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Support\Tenancy\TenantContext;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function __invoke(TenantContext $tenant): Response
    {
        $organization = $tenant->organization();
        abort_unless(request()->user()->can('update', $organization), 403);

        return Inertia::render('Audit/Index', ['logs' => AuditLog::query()->whereBelongsTo($organization)->with('actor:id,name')->latest()->paginate(30)]);
    }
}
