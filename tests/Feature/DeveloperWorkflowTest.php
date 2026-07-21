<?php

use App\Enums\MembershipRole;
use App\Models\Developer;
use App\Models\Favorite;
use App\Models\Membership;
use App\Models\Note;
use App\Models\PipelineEntry;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

it('synchronizes a GitHub profile and calculates an explainable score', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/users/octocat' => Http::response(['id' => 1, 'login' => 'octocat', 'name' => 'Octo Cat', 'avatar_url' => 'https://example.test/avatar.png', 'html_url' => 'https://github.com/octocat', 'location' => 'San Francisco', 'bio' => 'Developer', 'company' => 'GitHub', 'followers' => 200, 'public_repos' => 2, 'created_at' => '2011-01-25T18:44:36Z']),
        'api.github.com/users/octocat/repos*' => Http::response([['language' => 'PHP', 'stargazers_count' => 100, 'pushed_at' => now()->toISOString()], ['language' => 'TypeScript', 'stargazers_count' => 50, 'pushed_at' => now()->subDay()->toISOString()]]),
    ]);
    $user = User::factory()->create();
    $membership = Membership::factory()->for($user)->create();
    $this->actingAs($user)->post('/developers/sync', ['login' => 'octocat'])->assertRedirect('/pipeline');
    $this->assertDatabaseHas('developers', ['login' => 'octocat', 'primary_language' => 'PHP', 'total_stars' => 150]);
    $this->assertDatabaseHas('score_snapshots', ['algorithm_version' => '1.0.0']);
    $developer = Developer::query()->where('login', 'octocat')->firstOrFail();
    $this->assertDatabaseHas('pipeline_entries', ['organization_id' => $membership->organization_id, 'developer_id' => $developer->id, 'status' => 'sourced']);
});

it('keeps private recruitment data isolated by organization', function () {
    $user = User::factory()->create();
    $ownMembership = $membership = Membership::factory()->for($user)->create();
    $foreignMembership = Membership::factory()->create();
    $developer = Developer::factory()->create();
    Favorite::query()->create(['organization_id' => $foreignMembership->organization_id, 'developer_id' => $developer->id, 'created_by' => $foreignMembership->user_id]);
    Note::query()->create(['organization_id' => $foreignMembership->organization_id, 'developer_id' => $developer->id, 'author_id' => $foreignMembership->user_id, 'body' => 'Private note']);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $ownMembership->organization_id])
        ->get('/developers/'.$developer->id)
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('recruitment.favorite', false)
            ->has('recruitment.notes', 0));
});

it('prevents viewers from changing recruitment data', function () {
    $membership = Membership::factory()->create(['role' => MembershipRole::Viewer]);
    $developer = Developer::factory()->create();

    $this->actingAs($membership->user)
        ->withSession(['active_organization_id' => $membership->organization_id])
        ->patch('/developers/'.$developer->id.'/recruitment', ['status' => 'interview'])
        ->assertForbidden();
});

it('discovers GitHub candidates using market filters', function () {
    Cache::flush();
    config(['inertia.ssr.enabled' => false]);
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/search/users*' => Http::response([
            'total_count' => 1000,
            'items' => [[
                'login' => 'php-expert',
                'avatar_url' => 'https://example.test/php-expert.png',
                'html_url' => 'https://github.com/php-expert',
                'score' => 1,
            ]],
        ]),
        'api.github.com/users/*/repos*' => Http::response([
            ['stargazers_count' => 60],
            ['stargazers_count' => 50],
        ]),
    ]);
    $membership = Membership::factory()->create();

    $this->actingAs($membership->user)
        ->withSession(['active_organization_id' => $membership->organization_id])
        ->get('/developers/discover?username=php-expert&language=php&location=Brasil&min_repositories=10&min_stars=100&sort=followers&page=2')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Developers/Index')
            ->where('discovery.total', 1000)
            ->where('discovery.page', 2)
            ->where('discovery.previous_page_url', fn (string $value): bool => str_contains($value, 'page=1'))
            ->where('discovery.next_page_url', fn (string $value): bool => str_contains($value, 'page=3'))
            ->where('discovery.data.0.login', 'php-expert')
            ->where('discovery.data.0.total_stars', 110)
            ->where('discovery.data.0.selected', false));

    Http::assertSent(fn ($request): bool => str_starts_with($request->url(), 'https://api.github.com/search/users')
        && str_contains($request['q'], '"php-expert" in:login')
        && str_contains($request['q'], 'language:php')
        && str_contains($request['q'], 'repos:>=10')
        && str_contains($request['q'], 'location:"Brasil"')
        && $request['per_page'] === 12
        && $request['page'] === 2);
});

it('links discovered candidates to an existing evaluation', function () {
    Cache::flush();
    config(['inertia.ssr.enabled' => false]);
    Http::preventStrayRequests();
    Http::fake([
        'api.github.com/search/users*' => Http::response([
            'total_count' => 1000,
            'items' => [[
                'login' => 'known-developer',
                'avatar_url' => 'https://example.test/known.png',
                'html_url' => 'https://github.com/known-developer',
                'score' => 1,
            ]],
        ]),
        'api.github.com/users/*/repos*' => Http::response([
            ['stargazers_count' => 60],
            ['stargazers_count' => 50],
        ]),
    ]);
    $membership = Membership::factory()->create();
    $developer = Developer::factory()->create(['login' => 'known-developer']);
    PipelineEntry::query()->create(['organization_id' => $membership->organization_id, 'developer_id' => $developer->id, 'status' => 'sourced', 'updated_by' => $membership->user_id]);

    $this->actingAs($membership->user)
        ->withSession(['active_organization_id' => $membership->organization_id])
        ->get('/developers/discover?min_repositories=&min_stars=')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('discovery.data.0.selected', true));
});
