<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->orWhere('username', $request->email)->first();

            if ($user) {
                if (password_verify($request->password, $user->password)) {
                    // generate token
                    $token = bin2hex(random_bytes(5));

                    // set token
                    $user->update(['token' => $token]);
                    $user->token = $token;

                    return response()->json([
                        'status' => 'success',
                        'data' => $user
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'gagal masuk'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 400);
        }
    }

    public function regist(Request $request)
    {
        try {
            if ($request->name == null || $request->username == null || $request->email == null || $request->role === null || $request->jenis_kelamin === null || $request->password == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'data tidak boleh kosong',
                ], 200);
            }

            $check_email = User::where('email', $request->email)->get();
            if (count($check_email)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'email telah digunakan',
                ], 200);
            }

            $check_username = User::where('username', $request->username)->get();
            if (count($check_username)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'nama pengguna telah digunakan'
                ], 200);
            }

            $data = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'jenis_kelamin' => $request->jenis_kelamin,
                'password' => bcrypt($request->password),
            ];

            User::create($data);

            return response()->json([
                'status' => 'success',
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->orWhere('username', $request->email)->first();

            if ($user) {
                $user->update(['token' => NULL]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil keluar'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ], 400);
        }
    }
}
