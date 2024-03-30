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

            if ($request->bearerToken() != $user->token || !$user->verified || !$user->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memuat daftar pengguna',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('id_pendamping', NULL)->get(),
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

            if ($request->bearerToken() != $user->token || !$user->verified || !$user->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memuat daftar pengguna',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('id_pendamping', $user->id)->get(),
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

            if ($request->bearerToken() != $guide->token || !$guide->verified || !$guide->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memilih pengguna',
                ], 200);
            }

            User::where('email', $request->user_email)->first()->update([
                'id_pendamping' => $guide->id,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('id_pendamping', $guide->id)->get(),
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

            if ($request->bearerToken() != $guide->token || !$guide->verified || !$guide->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menambah pengguna',
                ], 200);
            }

            if ($request->name == null || $request->username == null || $request->user_email == null || $request->jenis_kelamin === null || $request->password == null) {
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
            $user->jenis_kelamin = $request->jenis_kelamin;
            $user->password = bcrypt($request->password);
            $user->verified = 1;
            $user->id_pendamping = $guide->id;
            $user->save();

            return response()->json([
                'status' => 'success',
                'data' => User::where('role', 0)->where('id_pendamping', $guide->id)->get(),
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

            if ($request->bearerToken() != $guide->token || !$guide->verified || !$guide->token){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus user dari relasi'
                ], 200);
            }

            $user = User::where('email', $request->user_email)->first();
            $user->update([
                'id_pendamping' => NULL,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => User::where('id_pendamping', $guide->id)->get(),
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
