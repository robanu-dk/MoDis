<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function getMessage(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mendapatkan pesan'
                ], 200);
            }

            if ($request->bearerToken() != $user->token || $user->token == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mendapatkan pesan'
                ], 200);
            }

            if ($request->offset) {
                $data = DB::select('SELECT chats.*, users.email, users.name, users.profile_image FROM chats INNER JOIN users ON chats.sender_id = users.id ORDER BY chats.created_at ASC LIMIT 20 OFFSET ?', [$request->offset]);
            } else {
                $data = DB::select('SELECT chats.*, users.email, users.name, users.profile_image FROM chats INNER JOIN users ON chats.sender_id = users.id ORDER BY chats.created_at ASC');
            }

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

    public function sendMessage(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim pesan'
                ], 200);
            }

            if ($request->bearerToken() != $user->token || $user->token == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengirim pesan'
                ], 200);
            }

            Chat::create([
                'sender_id' => $user->id,
                'message' => $request->message,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil mengirim pesan',
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
