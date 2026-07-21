<?php

namespace App\Http\Middleware;

use App\Repositories\Membership\MembershipRepository;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveOrganization
{
    public function __construct(
        private MembershipRepository $memberships,
        private TenantContext $tenant,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->session()->get('active_organization_id');
        $membership = $this->memberships->activeForUser($request->user(), $organizationId);
        abort_if($membership === null, 403, 'No active organization.');

        $this->tenant->set($membership);
        $request->session()->put('active_organization_id', $membership->organization_id);
        Context::add('organization_id', $membership->organization_id);

        return $next($request);
    }
}
