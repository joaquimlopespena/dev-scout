<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFirstPasswordRequest;
use App\Services\Identity\FirstPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class FirstPasswordController extends Controller
{
    public function edit(): Response|RedirectResponse
    {
        if (! request()->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/FirstPassword');
    }

    public function update(UpdateFirstPasswordRequest $request, FirstPasswordService $service): RedirectResponse
    {
        Auth::logoutOtherDevices($request->validated('current_password'));
        $service->update($request->user(), $request->validated('password'));
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Senha definida com sucesso.');
    }
}
