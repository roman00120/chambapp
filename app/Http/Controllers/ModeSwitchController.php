<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModeSwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $targetMode = (string) $request->input('mode');

        if ($targetMode === 'professional' && $user->canActAsProfessional()) {
            session(['active_mode' => 'professional']);

            return redirect()->route('professional.dashboard')->with('status', 'Cambiaste al modo profesional.');
        }

        if ($targetMode === 'client' && $user->canActAsClient()) {
            session(['active_mode' => 'client']);

            return redirect()->route('client.dashboard')->with('status', 'Cambiaste al modo cliente.');
        }

        return back()->with('error', 'No tienes permisos para activar ese modo.');
    }
}
