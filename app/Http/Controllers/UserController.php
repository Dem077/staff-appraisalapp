<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Services\AccountLinkService;
use App\Support\AuthContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        abort_unless(AuthContext::can('view_any_user'), 403);

        $users = User::with(['roles', 'staff'])->latest()->paginate(15)->through(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'roles' => $u->roles->pluck('name'),
            'linked_staff' => $u->staff?->only(['id', 'name', 'emp_no']),
            'created_at' => $u->created_at?->format('M d, Y'),
        ]);

        return Inertia::render('Users/Index', ['users' => $users]);
    }

    public function create(): Response
    {
        abort_unless(AuthContext::can('create_user'), 403);

        return Inertia::render('Users/Form', [
            'user' => null,
            'roles' => \Spatie\Permission\Models\Role::where('guard_name', 'web')->pluck('name', 'id'),
            'linkedStaff' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(AuthContext::can('create_user'), 403);

        $request->merge([
            'staff_id' => $request->input('staff_id') ?: null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_ids' => ['nullable', 'array'],
            'staff_id' => ['nullable', 'exists:staff,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (! empty($data['role_ids'])) {
            $user->syncRoles(
                \Spatie\Permission\Models\Role::whereIn('id', $data['role_ids'])->pluck('name')
            );
        }

        AccountLinkService::linkUserToStaff($user, $data['staff_id'] ?? null);

        return redirect()->route('users.index')->with('success', 'User created.');
    }

    public function edit(User $user): Response
    {
        abort_unless(AuthContext::can('update_user'), 403);

        $user->load('staff');

        return Inertia::render('Users/Form', [
            'user' => $user->only(['id', 'name', 'email', 'staff_id']),
            'roles' => \Spatie\Permission\Models\Role::where('guard_name', 'web')->pluck('name', 'id'),
            'userRoles' => $user->roles->pluck('id'),
            'linkedStaff' => $user->staff ? $this->formatStaffOption($user->staff) : null,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(AuthContext::can('update_user'), 403);

        $request->merge([
            'staff_id' => $request->input('staff_id') ?: null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_ids' => ['nullable', 'array'],
            'staff_id' => ['nullable', 'exists:staff,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        if (isset($data['role_ids'])) {
            $user->syncRoles(
                \Spatie\Permission\Models\Role::whereIn('id', $data['role_ids'])->pluck('name')
            );
        }

        AccountLinkService::linkUserToStaff($user, $data['staff_id'] ?? null);

        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function searchStaffForLink(Request $request): JsonResponse
    {
        abort_unless(AuthContext::canAny(['create_user', 'update_user']), 403);

        $currentStaffId = $request->integer('current_staff_id') ?: null;
        $search = trim($request->string('q')->toString());

        $options = $this->linkableStaffQuery($currentStaffId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('emp_no', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'emp_no'])
            ->map(fn ($staff) => $this->formatStaffOption($staff))
            ->values()
            ->all();

        return response()->json(['options' => $options]);
    }

    protected function linkableStaffQuery(?int $currentStaffId = null)
    {
        $linkedStaffIds = User::query()
            ->whereNotNull('staff_id')
            ->when($currentStaffId, fn ($q) => $q->where('staff_id', '!=', $currentStaffId))
            ->pluck('staff_id');

        return Staff::query()->where(function ($q) use ($linkedStaffIds, $currentStaffId) {
            $q->whereNotIn('id', $linkedStaffIds);
            if ($currentStaffId) {
                $q->orWhere('id', $currentStaffId);
            }
        });
    }

    protected function formatStaffOption(Staff $staff): array
    {
        return [
            'id' => $staff->id,
            'label' => trim("{$staff->emp_no} — {$staff->name}"),
        ];
    }
}
