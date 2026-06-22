<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Services\AccountLinkService;
use App\Support\AuthContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffMemberController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(AuthContext::can('view_any_staff'), 403);

        $staff = Staff::query()
            ->with('user')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('emp_no', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'emp_no' => $s->emp_no,
                'email' => $s->email,
                'designation' => $s->designation,
                'mobile' => $s->mobile,
                'active' => $s->active,
                'linked_user' => $s->user?->only(['id', 'name', 'email']),
            ]);

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'filters' => $request->only('search'),
        ]);
    }

    public function edit(Staff $staff): Response
    {
        abort_unless(AuthContext::can('update_staff'), 403);

        return Inertia::render('Staff/Form', [
            'staff' => $staff->only(['id', 'name', 'nid', 'email', 'emp_no', 'designation', 'mobile', 'active']),
            'roles' => \Spatie\Permission\Models\Role::where('guard_name', 'staff')->pluck('name', 'id'),
            'staffRoles' => $staff->roles->pluck('id'),
            'linkedUserId' => $staff->user?->id,
            'userOptions' => $this->userOptions($staff->user?->id),
        ]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        abort_unless(AuthContext::can('update_staff'), 403);

        $request->merge([
            'user_id' => $request->input('user_id') ?: null,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string'],
            'nid' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'emp_no' => ['required', 'string'],
            'designation' => ['nullable', 'string'],
            'mobile' => ['nullable', 'string'],
            'active' => ['boolean'],
            'role_ids' => ['nullable', 'array'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $staff->update(collect($data)->except(['role_ids', 'user_id'])->toArray());

        if (isset($data['role_ids'])) {
            $staff->syncRoles(
                \Spatie\Permission\Models\Role::whereIn('id', $data['role_ids'])->pluck('name')
            );
        }

        if (array_key_exists('user_id', $data)) {
            if ($data['user_id']) {
                AccountLinkService::linkUserToStaff(User::findOrFail($data['user_id']), $staff->id);
            } else {
                User::query()->where('staff_id', $staff->id)->update(['staff_id' => null]);
            }
        }

        return redirect()->route('staff.index')->with('success', 'Staff updated.');
    }

    protected function userOptions(?int $currentUserId = null): array
    {
        $linkedUserIds = User::query()
            ->whereNotNull('staff_id')
            ->when($currentUserId, fn ($q) => $q->where('id', '!=', $currentUserId))
            ->pluck('id');

        return User::query()
            ->where(function ($q) use ($linkedUserIds, $currentUserId) {
                $q->whereNotIn('id', $linkedUserIds);
                if ($currentUserId) {
                    $q->orWhere('id', $currentUserId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn ($u) => [
                'id' => $u->id,
                'label' => trim("{$u->name} ({$u->email})"),
            ])
            ->values()
            ->all();
    }
}
