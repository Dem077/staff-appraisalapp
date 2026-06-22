<?php

namespace App\Support;

use App\Models\Staff;
use App\Models\User;
use App\Services\Shortcuts;
use Illuminate\Contracts\Auth\Authenticatable;

class AuthContext
{
    public static function user(): Staff|User|null
    {
        return auth('staff')->user() ?? auth('web')->user();
    }

    public static function guard(): ?string
    {
        if (auth('staff')->check()) {
            return 'staff';
        }

        if (auth('web')->check()) {
            return 'web';
        }

        return null;
    }

    public static function staffActor(): ?Staff
    {
        if (auth('staff')->check()) {
            return auth('staff')->user();
        }

        if (auth('web')->check()) {
            return auth('web')->user()->staff;
        }

        return null;
    }

    public static function webActor(): ?User
    {
        if (auth('web')->check()) {
            return auth('web')->user();
        }

        if (auth('staff')->check()) {
            return User::query()->where('staff_id', auth('staff')->id())->first();
        }

        return null;
    }

    public static function staffId(): ?int
    {
        return self::staffActor()?->id;
    }

    public static function isStaff(): bool
    {
        return auth('staff')->check();
    }

    public static function isAdmin(): bool
    {
        return auth('web')->check();
    }

    public static function hasLinkedStaff(): bool
    {
        return self::staffActor() !== null;
    }

    public static function hasLinkedUser(): bool
    {
        return self::webActor() !== null;
    }

    public static function can(string $permission): bool
    {
        if (self::webActor()?->can($permission)) {
            return true;
        }

        if (self::staffActor()?->can($permission)) {
            return true;
        }

        return false;
    }

    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }

        return false;
    }

    public static function isHr(): bool
    {
        if (self::can('view_all_appraisal::form::assigned::to::staff')) {
            return true;
        }

        $staff = self::staffActor();
        if (! $staff?->api_id) {
            return false;
        }

        $roles = Shortcuts::callgetapi('/user/roles', ['id' => $staff->api_id])->json() ?? [];

        return in_array('HR', $roles, true);
    }

    public static function avatarUrl(Authenticatable $user): string
    {
        if (property_exists($user, 'profile_photo_url') && $user->profile_photo_url) {
            return $user->profile_photo_url;
        }

        if (property_exists($user, 'avatar_url') && $user->avatar_url) {
            return asset('storage/'.$user->avatar_url);
        }

        $email = $user->email ?? '';

        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d=mp&r=g&s=250';
    }

    public static function toArray(): ?array
    {
        $user = self::user();
        if (! $user) {
            return null;
        }

        $web = self::webActor();
        $staff = self::staffActor();

        $permissions = collect()
            ->when($web, fn ($c) => $c->merge($web->getAllPermissions()))
            ->when($staff, fn ($c) => $c->merge($staff->getAllPermissions()))
            ->pluck('name')
            ->unique()
            ->values()
            ->all();

        $roles = collect()
            ->when($web, fn ($c) => $c->merge($web->getRoleNames()))
            ->when($staff, fn ($c) => $c->merge($staff->getRoleNames()))
            ->unique()
            ->values()
            ->all();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email ?? null,
            'emp_no' => $staff?->emp_no ?? $user->emp_no ?? null,
            'avatar' => self::avatarUrl($user),
            'permissions' => $permissions,
            'roles' => $roles,
            'is_hr' => self::isHr(),
            'guard' => self::guard(),
            'staff_id' => $staff?->id,
            'linked_staff' => $staff && self::guard() !== 'staff'
                ? $staff->only(['id', 'name', 'emp_no'])
                : null,
            'linked_user' => $web && self::guard() !== 'web'
                ? $web->only(['id', 'name', 'email'])
                : null,
        ];
    }

    public static function navigation(): array
    {
        $groups = [
            [
                'label' => null,
                'items' => [
                    ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'home'],
                ],
            ],
        ];

        $appraisalItems = [];

        if (self::canAny([
            'view_any_appraisal::form::assigned::to::staff',
            'view_appraisal::form::assigned::to::staff',
        ])) {
            $appraisalItems[] = ['label' => 'Appraisal Assignments', 'href' => route('assignments.index'), 'icon' => 'clipboard'];
        }

        if (self::canAny([
            'view_any_forms::assigned::to::hod',
            'view_forms::assigned::to::hod',
        ])) {
            $appraisalItems[] = ['label' => 'HOD Appraisals', 'href' => route('hod-assignments.index'), 'icon' => 'users'];
        }

        if ($appraisalItems !== []) {
            $groups[] = ['label' => 'Appraisals', 'items' => $appraisalItems];
        }

        $formItems = [];

        if (self::can('view_any_appraisal::form')) {
            $formItems[] = ['label' => 'Appraisal Forms', 'href' => route('appraisal-forms.index'), 'icon' => 'document'];
            $formItems[] = ['label' => 'Content Library', 'href' => route('appraisal-library.index'), 'icon' => 'list'];
        }

        if ($formItems !== []) {
            $groups[] = ['label' => 'Forms & Content', 'items' => $formItems];
        }

        $adminItems = [];

        if (self::can('view_any_staff')) {
            $adminItems[] = ['label' => 'Staff', 'href' => route('staff.index'), 'icon' => 'user-group'];
        }

        if (self::can('view_any_user')) {
            $adminItems[] = ['label' => 'Users', 'href' => route('users.index'), 'icon' => 'cog'];
        }

        if (self::can('view_any_role')) {
            $adminItems[] = ['label' => 'Roles & Permissions', 'href' => route('roles.index'), 'icon' => 'shield'];
        }

        if ($adminItems !== []) {
            $groups[] = ['label' => 'Administration', 'items' => $adminItems];
        }

        return $groups;
    }
}
