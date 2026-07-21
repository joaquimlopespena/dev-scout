<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeveloperDiscoveryRequest;
use App\Services\Developers\DiscoverDevelopersService;
use Inertia\Inertia;
use Inertia\Response;

class DeveloperDiscoveryController extends Controller
{
    public function __invoke(
        DeveloperDiscoveryRequest $request,
        DiscoverDevelopersService $discovery,
    ): Response {
        $filters = $request->validated();

        return Inertia::render('Developers/Index', [
            'discovery' => $discovery->search($filters),
            'discoveryFilters' => $filters,
        ]);
    }
}
