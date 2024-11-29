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
        $users = User::with('roles')->latest()->get();
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
                User::create([
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'role_id' => $validated['role_id']
                ]);

                $this->logs('User ' . $validated['username'] . ' was created');
            });
            return redirect()->route('users')->with('success', 'User created successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users')->with('error', 'Something went wrong');
        }
    }

    public function update(UserRequest $userRequest, User $user)
    {
        $validated = $userRequest->validated();

        try {
            DB::transaction(function () use ($validated, $user) {
                $user->update([
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'role_id' => $validated['role_id']
                ]);

                $this->logs('User ' . $validated['username'] . ' was updated');
            });
            return redirect()->route('users')->with('success', 'User updated successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users')->with('error', 'Something went wrong');
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                $user->delete();

                $this->logs('User ' . $user->username . ' was deleted');
            });
            return redirect()->route('users')->with('success', 'User deleted successfully');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('users')->with('error', 'Something went wrong');
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
