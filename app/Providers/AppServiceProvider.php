<?php

namespace App\Providers;

use App\Repositories\Developer\DeveloperRepository;
use App\Repositories\Developer\EloquentDeveloperRepository;
use App\Repositories\Membership\EloquentMembershipRepository;
use App\Repositories\Membership\MembershipRepository;
use App\Repositories\Organization\EloquentOrganizationRepository;
use App\Repositories\Organization\OrganizationRepository;
use App\Support\Tenancy\TenantContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OrganizationRepository::class, EloquentOrganizationRepository::class);
        $this->app->bind(DeveloperRepository::class, EloquentDeveloperRepository::class);
        $this->app->bind(MembershipRepository::class, EloquentMembershipRepository::class);
        $this->app->scoped(TenantContext::class);
    }

    public function boot(): void
    {
        RateLimiter::for('github-sync', fn (Request $request): Limit => Limit::perMinute(10)->by((string) $request->user()?->id));

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
    }
}
