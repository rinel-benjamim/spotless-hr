<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanySettingsController extends Controller
{
    public function edit()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $settings = CompanySetting::current() ?? new CompanySetting([
            'company_name' => config('app.name'),
            'timezone' => config('app.timezone'),
            'currency' => 'EUR',
        ]);

        return Inertia::render('Settings/Company', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'business_hours_start' => ['nullable', 'date_format:H:i'],
            'business_hours_end' => ['nullable', 'date_format:H:i'],
            'timezone' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
        ]);

        $settings = CompanySetting::current();

        if ($settings) {
            $settings->update($validated);
        } else {
            $settings = CompanySetting::create($validated);
        }

        ActivityLog::log(
            'company_settings_updated',
            $settings,
            'Configurações da empresa atualizadas',
            $validated
        );

        return redirect()->back()->with('success', 'Configurações atualizadas com sucesso.');
    }
}
