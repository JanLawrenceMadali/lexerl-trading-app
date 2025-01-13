<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLoggerService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userService;
    protected $activityLog;
    private $actor;

    public function __construct(
        UserService $userService,
        ActivityLoggerService $activityLoggerService
    ) {
        $this->userService = $userService;
        $this->activityLog = $activityLoggerService;
        $this->actor = Auth::user()->username;
    }

    public function index()
    {
        return inertia('Settings/Users/Index', [
            'users' => User::with('roles')
                ->latest()
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role_id' => $user->roles->id,
                    'role' => $user->roles?->name,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]),
            'roles' => Role::all(['id', 'name'])
        ]);
    }

    public function store(UserRequest $userRequest)
    {
        $validated = $userRequest->validated();

        try {
            $user = $this->userService->createUser($validated);

            $this->activityLog->logUserAction(
                ActivityLog::ACTION_CREATED,
                "{$this->actor} created a new user: {$user->username}",
                ['new' => $user->toArray()]
            );

            return redirect()->back()->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to create a user');
        }
    }

    public function update(UserRequest $userRequest, User $user)
    {
        $validated = $userRequest->validated();

        try {
            $oldData = $user->toArray();

            $this->userService->updateUser($user, $validated);

            $this->activityLog->logUserAction(
                ActivityLog::ACTION_UPDATED,
                "{$this->actor} updated a user: {$user->username}",
                ['old' => $oldData, 'new' => $user->toArray()]
            );

            return redirect()->back()->with('success', 'User updated successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to update user');
        }
    }

    public function destroy(User $user)
    {
        // Prevent deleting the current authenticated user
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account');
        }

        try {
            $this->userService->deleteUser($user);

            $this->activityLog->logUserAction(
                ActivityLog::ACTION_DELETED,
                "{$this->actor} deleted a user: {$user->username}",
                ['old' => $user->toArray()]
            );

            return redirect()->back()->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('error', $e->getMessage() ?? 'Failed to delete user');
        }
    }
}
