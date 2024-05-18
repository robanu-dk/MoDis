<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    public function getAllUser(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memuat daftar pengguna',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->verified || !$user->token || !$user->role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memuat daftar pengguna',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('guide_id', NULL)->get(),
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

    public function getUserBasedGuide(Request $request)
    {
        try {

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memuat daftar pengguna',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->verified || !$user->token || !$user->role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memuat daftar pengguna',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('guide_id', $user->id)->get(),
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

    public function chooseUser(Request $request)
    {
        try {
            $guide = User::where('email', $request->guide_email)->first();

            if (!$guide) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memilih pengguna',
                ], 200);
            }

            if ($request->bearerToken() != $guide->token || !$guide->verified || !$guide->token || !$guide->role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memilih pengguna',
                ], 200);
            }

            User::where('email', $request->user_email)->first()->update([
                'guide_id' => $guide->id,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('guide_id', $guide->id)->get(),
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

    public function createUser(Request $request)
    {
        try {
            $guide = User::where('email', $request->guide_email)->first();

            if (!$guide) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menambah pengguna',
                ], 200);
            }

            if ($request->bearerToken() != $guide->token || !$guide->verified || !$guide->token || !$guide->role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menambah pengguna',
                ], 200);
            }

            if ($request->name == null || $request->username == null || $request->user_email == null || $request->gender === null || $request->password == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'data tidak boleh kosong',
                ], 200);
            }

            $check_email = User::where('email', $request->user_email)->get();
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

            $user = new User();
            $user->email = $request->user_email;
            $user->username = $request->username;
            $user->name = $request->name;
            $user->role = 0;
            $user->gender = $request->gender;
            $user->password = bcrypt($request->password);
            $user->verified = 1;
            $user->guide_id = $guide->id;
            $user->save();

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('guide_id', $guide->id)->get(),
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

    public function removeUser(Request $request)
    {
        try {
            $guide = User::where('email', $request->guide_email)->first();

            if (!$guide) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus pengguna dari relasi',
                ], 200);
            }

            if ($request->bearerToken() != $guide->token || !$guide->verified || !$guide->token || !$guide->role){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus pengguna dari relasi'
                ], 200);
            }

            $user = User::where('email', $request->user_email)->where('guide_id', $guide->id)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus pengguna dari relasi'
                ], 200);
            }

            $user->update([
                'guide_id' => NULL,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => User::where('guide_id', $guide->id)->get(),
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
}
