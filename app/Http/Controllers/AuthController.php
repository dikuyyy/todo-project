<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'username' => 'required|string|min:6',
        ]);


        try {
            if ($validator->fails()) {
                throw new \Exception($validator->errors(), 422);
            }

            DB::beginTransaction();
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);
            DB::commit();

            $token = auth('api')->login($user);

            return response()->json(compact('user', 'token'));
        }
        catch (\Exception $e) {
            DB::rollBack();

            if ($e->getCode() === '23505') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'
                ], 409);
            }

            return response()->json([
                'msg' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            if ($validator->fails()) {
                throw new \Exception($validator->errors(), 422);
            }

            $credentials = $request->only('username', 'password');
            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return response()->json(compact('token'));
        }
        catch (\Exception $e) {
            return response()->json([
                'msg' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
    }
}
