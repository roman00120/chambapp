<?php

namespace App\Http\Controllers\Professional;

use App\Http\Controllers\Controller;
use App\Http\Requests\Professional\UpdateProfessionalProfileRequest;
use App\Services\ProfessionalProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalProfileController extends Controller
{
    public function __construct(private readonly ProfessionalProfileService $profiles) {}

    public function show(Request $request): View
    {
        $profile = $this->profiles->profileFor($request->user())->load('user');
        $this->authorize('view', $profile);

        return view('professional.profile.show', compact('profile'));
    }

    public function edit(Request $request): View
    {
        $profile = $this->profiles->profileFor($request->user())->load('user');
        $this->authorize('update', $profile);

        return view('professional.profile.edit', compact('profile'));
    }

    public function update(UpdateProfessionalProfileRequest $request): RedirectResponse
    {
        $profile = $this->profiles->profileFor($request->user());
        $this->authorize('update', $profile);
        $this->profiles->update($request->user(), $profile, $request->validated(), $request->file('profile_photo'));

        return redirect()->route('professional.profile.show')->with('status', 'Perfil actualizado correctamente.');
    }
}
