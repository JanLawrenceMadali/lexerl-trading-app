<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $users = User::with('roles')->get();
        return inertia('Settings/Users/Index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function store(UserRequest $userRequest)
    {
        $validated = $userRequest->validated();

        try {
            DB::transaction(function () use ($validated) {
                User::create($validated);

                $this->logs('User Created');
            });
            return redirect()->route('users')->with('message', 'User created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users')->with('message', 'Something went wrong');
        }
    }

    public function update(UserRequest $userRequest, User $user)
    {
        $validated = $userRequest->validated();

        try {
            DB::transaction(function () use ($validated, $user) {
                $user->update($validated);

                $this->logs('User Updated');
            });
            return redirect()->route('users')->with('message', 'User updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users')->with('message', 'Something went wrong');
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                $user->delete();

                $this->logs('User Deleted');
            });
            return redirect()->route('users')->with('message', 'User deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users')->with('message', 'Something went wrong');
        }
    }

    private function logs(string $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $action . ' by ' . Auth::user()->username,
        ]);
    }
}
