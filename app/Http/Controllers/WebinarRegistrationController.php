<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
use App\Models\WebinarRegistration;
use Illuminate\Http\Request;

class WebinarRegistrationController extends Controller
{
    /**
     * Store a new webinar registration.
     */
    public function store(Request $request, Webinar $webinar)
    {
        // Prevent registration for past webinars
        if ($webinar->webinar_date->isPast()) {
            return back()->with('error', 'Pendaftaran untuk webinar ini sudah ditutup.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'institution' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'motivation' => ['nullable', 'string', 'max:1000'],
            'referral_source' => ['nullable', 'string', 'max:255'],
        ]);

        // Check if already registered
        $existing = WebinarRegistration::where('webinar_id', $webinar->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->with('warning', 'Email ini sudah terdaftar untuk webinar ini.');
        }

        WebinarRegistration::create([
            'webinar_id' => $webinar->id,
            ...$validated,
        ]);

        return back()->with('success', 'Pendaftaran berhasil! Kami akan mengirimkan informasi lanjutan melalui email.');
    }
}
