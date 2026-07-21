<?php

namespace App\Http\Controllers;

use App\Services\Developers\SyncDeveloperService;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeveloperSyncController extends Controller
{
    public function __invoke(
        Request $request,
        SyncDeveloperService $sync,
        RecruitmentService $recruitment,
    ): RedirectResponse {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:39', 'regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$/'],
        ]);
        $developer = $sync->sync($data['login']);
        $recruitment->update($developer, $request->user(), ['status' => 'sourced']);

        return redirect()->route('pipeline.index')->with('success', 'Candidato adicionado ao pipeline.');
    }
}
