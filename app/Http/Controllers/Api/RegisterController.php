<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // REQUEST VALIDATION
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|numeric',
            'country' => 'required',
            'address' => 'required',
            'password' => 'required',
            're-password' => 'required|same:password'
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.numeric' => 'Phone number must be numeric.',
            'country.required' => 'Country is required.',
            'address.required' => 'Address is required.',
            'password.required' => 'Password is required.',
            're-password.required' => 'Please re-type your password.',
            're-password.same' => 'Passwords do not match.'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check if email already exists
        $exists = User::where('email', $request->email)->first();
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Email already exists.'
            ], 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        // Create user details
        $userDetail = UserDetails::create([
            'user_id' => $user['id'],
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'country' => $request->country
        ]);

        // Check if user details creation failed
        if (!$userDetail) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create user details.'
            ], 500);
        }

        // Fetch user with details
        $data = User::with('user_details')->where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'Registration successfully',
            'data' => $data
        ], 200);
    }
}
