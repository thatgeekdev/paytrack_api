<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            ...$data,
            'password' => bcrypt($data['password'])
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(array $data, Request $request)
    {
        $user = User::where('email', $data['email'])->first();

        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
            return $this->error('ACCOUNT_LOCKED', 'Account temporarily locked', 423);
        }

        $success = Auth::attempt($data);

        DB::table('login_logs')->insert([
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => $success,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if (!$success) {
            $this->handleFailedLogin($user);

            return $this->error('INVALID_CREDENTIALS', 'Invalid email or password', 401);
        }

        $user = Auth::user();

        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token
        ]);
    }

    private function handleFailedLogin($user)
    {
        if (!$user) return;

        $user->failed_attempts += 1;

        if ($user->failed_attempts >= 3) {
            $user->locked_until = now()->addMinutes(15);
            $user->failed_attempts = 0;
        }

        $user->save();
    }

    private function success($data)
    {
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    private function error($code, $message, $status)
    {
        return response()->json([
            'success' => false,
            'error' => $code,
            'message' => $message
        ], $status);
    }
}