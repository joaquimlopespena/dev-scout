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
            'logins' => ['sometimes', 'array'],
            'logins.*' => ['string', 'max:39', 'regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$/'],
            'login' => ['sometimes', 'string', 'max:39', 'regex:/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$/'],
        ]);

        $logins = $data['logins'] ?? (isset($data['login']) ? [$data['login']] : []);

        foreach ($logins as $login) {
            $developer = $sync->sync($login);
            $recruitment->update($developer, $request->user(), ['status' => 'sourced']);
        }

        $message = count($logins) > 1 ? count($logins) . ' candidatos adicionados ao pipeline.' : 'Candidato adicionado ao pipeline.';

        return redirect()->route('pipeline.index')->with('success', $message);
    }
}
