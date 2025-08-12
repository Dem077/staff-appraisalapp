<?php

namespace App\Filament\Staff\Pages\Auth;

use Carbon\Carbon;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLoginPage;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class CustomLogin extends BaseLoginPage
{


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('identifier')
                    ->label('Email or NID/PP or Staff ID')
                    ->required(),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(),
            ]);
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::End;
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        // Call external API to validate credentials
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept' => 'application/json',
        ])->timeout(10)->post(config('app.apiurl') . '/login', [
            'identifier' => $data['identifier'],
            'password' => $data['password'],
        ]);

        if ($response->ok()) {
            $staffData = $response->json('user');

            // Find or create local staff record
            $staff = \App\Models\Staff::updateOrCreate(
                [
                    'name' => $staffData['name'],
                    'api_id' => $staffData['id'],
                    'email' => $staffData['email'],
                    'email_verified_at' => $staffData['email_verified_at'],
                    'emp_no' => $staffData['emp_no'],
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
                ],
                [
                    'name' => $staffData['name'],
                    'api_id' => $staffData['id'],
                    'email' => $staffData['email'],
                    'email_verified_at' => $staffData['email_verified_at'],
                    'emp_no' => $staffData['emp_no'],
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
            if($staffData['active']) {
                // Log in the staff locally using the staff guard
                \Illuminate\Support\Facades\Auth::guard('staff')->login($staff);

                session()->regenerate();
            }

            return app(LoginResponse::class);
        }

        $this->throwFailureValidationException();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'identifier' => $data['identifier'],
            'password' => $data['password'],
        ];
    }
}
