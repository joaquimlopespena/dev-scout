<?php

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia;

it('allows an owner to register a member with a temporary password', function () {
    $organization = Organization::factory()->create();
    $owner = User::factory()->create();
    Membership::factory()->for($organization)->for($owner)->owner()->create();

    $this->actingAs($owner)
        ->withSession(['active_organization_id' => $organization->id])
        ->post('/team/members', [
            'name' => 'Grace Hopper',
            'email' => 'grace@example.com',
            'temporary_password' => 'Temporary123!',
            'role' => MembershipRole::Evaluator->value,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Membro cadastrado.');

    $member = User::query()->where('email', 'grace@example.com')->firstOrFail();

    expect($member->name)->toBe('Grace Hopper')
        ->and($member->must_change_password)->toBeTrue()
        ->and(Hash::check('Temporary123!', $member->password))->toBeTrue();
    $this->assertDatabaseHas('memberships', [
        'organization_id' => $organization->id,
        'user_id' => $member->id,
        'role' => MembershipRole::Evaluator->value,
    ]);
});

it('allows an owner to edit a member and reset their temporary password', function () {
    $organization = Organization::factory()->create();
    $owner = User::factory()->create();
    $member = Membership::factory()->for($organization)->create([
        'role' => MembershipRole::Evaluator,
    ]);
    Membership::factory()->for($organization)->for($owner)->owner()->create();

    $this->actingAs($owner)
        ->withSession(['active_organization_id' => $organization->id])
        ->patch('/team/members/'.$member->id, [
            'name' => 'Updated Member',
            'email' => 'updated@example.com',
            'temporary_password' => 'NewTemporary123!',
            'role' => MembershipRole::Admin->value,
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Membro atualizado.');

    expect($member->refresh()->role)->toBe(MembershipRole::Admin)
        ->and($member->user->name)->toBe('Updated Member')
        ->and($member->user->email)->toBe('updated@example.com')
        ->and($member->user->must_change_password)->toBeTrue()
        ->and(Hash::check('NewTemporary123!', $member->user->password))->toBeTrue();
});

it('does not expose a membership from another organization', function () {
    $ownerMembership = Membership::factory()->owner()->create();
    $foreignMembership = Membership::factory()->create();

    $this->actingAs($ownerMembership->user)
        ->withSession(['active_organization_id' => $ownerMembership->organization_id])
        ->patch('/team/members/'.$foreignMembership->id, [
            'name' => $foreignMembership->user->name,
            'email' => $foreignMembership->user->email,
            'role' => MembershipRole::Admin->value,
        ])
        ->assertNotFound();
});

it('prevents a viewer from registering or editing members', function () {
    $viewerMembership = Membership::factory()->create([
        'role' => MembershipRole::Viewer,
    ]);
    $member = Membership::factory()->for($viewerMembership->organization)->create();
    $payload = [
        'name' => 'Restricted User',
        'email' => 'restricted@example.com',
        'temporary_password' => 'Temporary123!',
        'role' => MembershipRole::Admin->value,
    ];

    $this->actingAs($viewerMembership->user)
        ->withSession(['active_organization_id' => $viewerMembership->organization_id])
        ->post('/team/members', $payload)
        ->assertForbidden();

    $this->actingAs($viewerMembership->user)
        ->withSession(['active_organization_id' => $viewerMembership->organization_id])
        ->patch('/team/members/'.$member->id, $payload)
        ->assertForbidden();
});

it('forces a temporary-password user to define a new password before using the app', function () {
    $user = User::factory()->create([
        'password' => 'Temporary123!',
        'must_change_password' => true,
    ]);
    Membership::factory()->for($user)->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertRedirect(route('password.first.edit'));

    $this->actingAs($user)->get('/first-access/password')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('Auth/FirstPassword'));

    $this->actingAs($user)->put('/first-access/password', [
        'current_password' => 'Temporary123!',
        'password' => 'PermanentPassword123!',
        'password_confirmation' => 'PermanentPassword123!',
    ])->assertRedirect(route('dashboard'));

    expect($user->refresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('PermanentPassword123!', $user->password))->toBeTrue();

    $this->actingAs($user)->get('/dashboard')->assertSuccessful();
});

it('soft deletes a removed member and their user when no active memberships remain', function () {
    $organization = Organization::factory()->create();
    $owner = User::factory()->create();
    $member = Membership::factory()->for($organization)->create();
    Membership::factory()->for($organization)->for($owner)->owner()->create();
    $memberUser = $member->user;

    $this->actingAs($owner)
        ->withSession(['active_organization_id' => $organization->id])
        ->delete('/team/members/'.$member->id)
        ->assertRedirect()
        ->assertSessionHas('success', 'Membro removido.');

    $this->assertSoftDeleted($member);
    $this->assertSoftDeleted($memberUser);
    $this->assertDatabaseHas('audit_logs', [
        'organization_id' => $organization->id,
        'action' => 'membership.deleted',
    ]);
});

it('keeps a user active when removing only one of their organization memberships', function () {
    $ownerMembership = Membership::factory()->owner()->create();
    $member = Membership::factory()->for($ownerMembership->organization)->create();
    Membership::factory()->for($member->user)->create();

    $this->actingAs($ownerMembership->user)
        ->withSession(['active_organization_id' => $ownerMembership->organization_id])
        ->delete('/team/members/'.$member->id)
        ->assertRedirect();

    $this->assertSoftDeleted($member);
    $this->assertNotSoftDeleted($member->user);
});

it('prevents deleting owners, the current user, and foreign memberships', function () {
    $ownerMembership = Membership::factory()->owner()->create();
    $foreignMembership = Membership::factory()->create();

    $this->actingAs($ownerMembership->user)
        ->withSession(['active_organization_id' => $ownerMembership->organization_id])
        ->delete('/team/members/'.$ownerMembership->id)
        ->assertUnprocessable();

    $this->actingAs($ownerMembership->user)
        ->withSession(['active_organization_id' => $ownerMembership->organization_id])
        ->delete('/team/members/'.$foreignMembership->id)
        ->assertNotFound();
});

it('adds deleted_at to every domain entity table', function (string $table) {
    expect(Schema::hasColumn($table, 'deleted_at'))->toBeTrue();
})->with([
    'users', 'organizations', 'memberships', 'audit_logs', 'developers', 'score_snapshots',
    'favorites', 'notes', 'tags', 'pipeline_entries', 'invitations',
]);
