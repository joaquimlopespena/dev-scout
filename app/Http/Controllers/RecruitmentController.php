<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecruitmentRequest;
use App\Models\Developer;
use App\Models\PipelineEntry;
use App\Services\Recruitment\RecruitmentService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RecruitmentController extends Controller
{
    public function index(TenantContext $tenant): Response
    {
        $entries = PipelineEntry::query()->where('organization_id', $tenant->organization()->id)->with('developer.latestScore')->latest('updated_at')->get();

        return Inertia::render('Recruitment/Pipeline', ['entries' => $entries]);
    }

    public function update(StoreRecruitmentRequest $request, Developer $developer, RecruitmentService $service): RedirectResponse
    {
        $service->update($developer, $request->user(), $request->validated());

        return back()->with('success', 'Candidato atualizado.');
    }
}
