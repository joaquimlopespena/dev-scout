<?php

use App\Enums\MembershipRole;
use App\Models\Membership;
use App\Models\Organization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('resolves the organization belonging to the authenticated user', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    Membership::factory()->for($user)->for($organization)->owner()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('organization.id', $organization->id)
            ->where('role', MembershipRole::Owner->value));
});

it('prevents selecting another organizations tenant', function () {
    $user = User::factory()->create();
    $ownOrganization = Organization::factory()->create();
    $foreignOrganization = Organization::factory()->create();
    Membership::factory()->for($user)->for($ownOrganization)->create();

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $foreignOrganization->id])
        ->get('/dashboard')
        ->assertForbidden();
});

it('denies access when membership is revoked', function () {
    $membership = Membership::factory()->revoked()->create();

    $this->actingAs($membership->user)->get('/dashboard')->assertForbidden();
});
