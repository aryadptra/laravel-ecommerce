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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|numeric',
            'country' => 'required',
            'address' => 'required',
            'password' => 'required',
            're-password' => 'required|same:password'
        ], [
            'name.required' => 'NAME REQUIRED',
            'email.required' => 'EMAIL REQUIRED',
            'phone_number.required' => 'PHONE NUMBER REQUIRED',
            'address.required' => 'ADDRESS REQUIRED',
            'email.email' => 'INVALID EMAIL FORMAT',
            'password.required' => 'PASSWORD REQUIRED',
            're-password.required' => 'REPASSWORD REQUIRED',
            're-password.same' => 'REPASSWORD SHOULD MATCH PASSWORD'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $exists = User::where('email', $request->email)->first();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'EMAIL ALREADY EXISTS'
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user_detail = UserDetails::create([
            'user_id' => $user['id'],
            'phone_number' => $request->phone_number,
            'address' => $request->address,
        ]);

        if (!$user_detail) {
            return response()->json([
                'status' => false,
                'message' => 'REGISTER FAILED'
            ], 400);
        }

        $data = User::with('user_details')->where('id', $user->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'REGISTER SUCCESSFUL',
            'data' => $data
        ], 200);
    }
}
