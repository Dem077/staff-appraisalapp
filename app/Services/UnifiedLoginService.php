<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UnifiedLoginService
{
    public function __construct(
        private StaffAuthService $staffAuth,
    ) {}

    public function attempt(string $identifier, string $password, bool $remember = false): void
    {
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        if (! $isEmail) {
            $this->loginStaff($identifier, $password);

            return;
        }

        try {
            $this->loginStaff($identifier, $password);

            return;
        } catch (ValidationException $exception) {
            if ($this->isConfigurationError($exception)) {
                throw $exception;
            }
        }

        if (! Auth::guard('web')->attempt([
            'email' => $identifier,
            'password' => $password,
        ], $remember)) {
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }
    }

    protected function loginStaff(string $identifier, string $password): Staff
    {
        $staff = $this->staffAuth->authenticate($identifier, $password);

        if ($staff->roles()->count() === 0) {
            $staff->assignRole('normal_user');
        }

        return $staff;
    }

    protected function isConfigurationError(ValidationException $exception): bool
    {
        $message = $exception->errors()['identifier'][0] ?? '';

        return str_contains($message, 'API') || str_contains($message, 'configured');
    }
}
