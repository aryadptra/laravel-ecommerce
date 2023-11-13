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
            'email.required' => 'EMAIL REQUIRED',
            'email.email' => 'INVALID EMAIL FORMAT',
            'password.required' => 'PASSWORD REQUIRED'
        ]);

        // If validation fail
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find user by email from request "email"
        $user = User::with('user_details')->where('email', $request->email)->first();

        // If password from user and password from request not same
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Return with status code "400" and login failed
            return response()->json([
                'success' => false,
                'message' => 'LOGIN_FAILED',
            ], 400);
        }

        // User success login and create token
        return response()->json([
            'success' => true,
            'message' => 'LOGIN_SUCCESS',
            'data'    => $user,
            'token'   => $user->createToken('authToken')->accessToken
        ], 200);
    }
}
