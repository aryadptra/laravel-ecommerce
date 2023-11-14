<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // REQUEST VALIDATION
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required'
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email address.',
            'password.required' => 'Password is required.'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find user by email from request "email"
        $user = User::with('user_details')->where('email', $request->email)->first();

        // If no user found or passwords do not match
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Please check your credentials.'
            ], 422); // 401 for unauthorized access
        }

        // User successfully logged in, create token
        return response()->json([
            'success' => true,
            'message' => 'Login successfully',
            'data'    => $user,
            'token'   => $user->createToken('authToken')->accessToken
        ], 200);
    }
}
