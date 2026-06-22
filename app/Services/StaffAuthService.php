<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class StaffAuthService
{
    public function authenticate(string $identifier, string $password): Staff
    {
        $apiKey = config('app.appkey');
        $apiUrl = config('app.apiurl');

        if (blank($apiKey) || blank($apiUrl)) {
            throw ValidationException::withMessages([
                'identifier' => 'Staff API is not configured. Set API_KEY and API_URL in .env.',
            ]);
        }

        $response = Http::withHeaders([
            'X-API-KEY' => $apiKey,
            'Accept' => 'application/json',
        ])->timeout(10)->post(rtrim($apiUrl, '/').'/login', [
            'identifier' => $identifier,
            'password' => $password,
        ]);

        if ($response->status() === 401 && ($response->json('message') === 'Unauthorized')) {
            throw ValidationException::withMessages([
                'identifier' => 'API key rejected by staff system. Check API_KEY in .env matches the attendance app.',
            ]);
        }

        if (! $response->ok()) {
            throw ValidationException::withMessages([
                'identifier' => $response->json('message') ?? 'These credentials do not match our records.',
            ]);
        }

        $staffData = $response->json('user');

        if (! ($staffData['active'] ?? false)) {
            throw ValidationException::withMessages([
                'identifier' => 'Your account is inactive.',
            ]);
        }

        $staff = Staff::updateOrCreate(
            ['emp_no' => $staffData['emp_no']],
            [
                'name' => $staffData['name'],
                'api_id' => $staffData['id'],
                'email' => $staffData['email'],
                'email_verified_at' => $staffData['email_verified_at'],
                'gender' => $staffData['gender'],
                'designation' => $staffData['designation'],
                'mobile' => $staffData['mobile'],
                'phone' => $staffData['phone'],
                'department_id' => $staffData['department_id'],
                'active' => $staffData['active'],
                'location_id' => $staffData['location_id'],
                'nid' => $staffData['nid'],
                'supervisor_id' => $staffData['supervisor_id'],
                'joined_date' => $staffData['joined_date'],
                'is_annual_applicable' => $staffData['is_annual_applicable'],
                'profile_photo_path' => $staffData['profile_photo_path'],
                'profile_photo_url' => $staffData['profile_photo_url'],
                'external_id' => $staffData['external_id'],
            ]
        );

        Auth::guard('staff')->login($staff);

        return $staff;
    }
}
