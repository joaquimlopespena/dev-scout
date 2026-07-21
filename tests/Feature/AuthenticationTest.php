<?php

use App\Enums\MembershipRole;
use App\Models\AuditLog;
use App\Models\Membership;
use App\Models\User;

it('registers an owner with an initial organization', function () {
    $response = $this->post('/register', [
        'name' => 'Ada Lovelace',
        'organization_name' => 'Analytical Engines',
        'email' => 'ada@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHasNoErrors();
    $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect($user->memberships()->firstOrFail()->role)->toBe(MembershipRole::Owner)
        ->and(AuditLog::query()->where('action', 'organization.created')->exists())->toBeTrue();
});

it('authenticates and logs out an existing user', function () {
    $user = User::factory()->create(['password' => 'password123']);
    Membership::factory()->for($user)->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);

    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'invalid'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});
