<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            ...$data,
            'password' => bcrypt($data['password'])
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(compact('user', 'token'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $credentials['email'])->first();


        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
            return response()->json([
                'error' => 'ACCOUNT_LOCKED',
                'message' => 'Account is temporarily locked. Try again later.'
            ], 423);
        }

        $success = Auth::attempt($credentials);

        DB::table('login_logs')->insert([
            'user_id' => $user?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'success' => $success,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if (!$success) {

            if ($user) {
                $user->failed_attempts += 1;

                if ($user->failed_attempts >= 3) {
                    $user->locked_until = now()->addMinutes(15);
                    $user->failed_attempts = 0;
                }

                $user->save();
            }

            return response()->json([
                'error' => 'INVALID_CREDENTIALS',
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = Auth::user();

        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

    public function profile()
    {
        return response()->json(Auth::user());
    }
}
