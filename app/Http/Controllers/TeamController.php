<?php

namespace App\Http\Controllers;

use App\Enums\MembershipRole;
use App\Http\Requests\DestroyMembershipRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMembershipRequest;
use App\Models\Membership;
use App\Services\TeamManagement\MembershipService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(TenantContext $tenant): Response
    {
        $organization = $tenant->organization();

        return Inertia::render('Team/Index', [
            'members' => Membership::query()
                ->whereBelongsTo($organization)
                ->with('user:id,name,email,must_change_password')
                ->latest()
                ->get()
                ->map(fn (Membership $membership): array => [
                    'id' => $membership->id,
                    'role' => $membership->role->value,
                    'revoked_at' => $membership->revoked_at,
                    'user' => $membership->user->only(['name', 'email', 'must_change_password']),
                    'can_edit' => $membership->role !== MembershipRole::Owner,
                    'can_delete' => $membership->role !== MembershipRole::Owner
                        && $membership->user_id !== request()->user()->id,
                ]),
            'canManage' => request()->user()->can('update', $organization),
        ]);
    }

    public function store(StoreMemberRequest $request, MembershipService $service): RedirectResponse
    {
        $service->create($request->user(), $request->validated());

        return back()->with('success', 'Membro cadastrado.');
    }

    public function destroy(DestroyMembershipRequest $request, int $membership, MembershipService $service): RedirectResponse
    {
        $service->remove($membership, $request->user());

        return back()->with('success', 'Membro removido.');
    }

    public function update(UpdateMembershipRequest $request, int $membership, MembershipService $service): RedirectResponse
    {
        $service->update($membership, $request->user(), $request->validated());

        return back()->with('success', 'Membro atualizado.');
    }
}
