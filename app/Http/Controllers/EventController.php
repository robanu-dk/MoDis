<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function getEventTodayByUserBasedGuide(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        try {
            $guide = User::where('email', $request->guide_email)->first();

            if (!$guide) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'pendamping tidak ditemukan',
                ], 200);
            }

            if ($request->bearerToken() != $guide->token || !$guide->token || !$guide->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan data',
                ], 200);
            }

            $user = User::where('email', $request->user_email)->first();

            if (!$user || $user->guide_id != $guide->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'pengguna tidak ditemukan',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT * FROM user_events ue INNER JOIN events e ON ue.`id_event` = e.`id` WHERE ue.`id_user` = ? AND e.`date` = ? AND ue.`status` != 0 ORDER BY e.`start_time`', [$user->id, date('Y-m-d')]),
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
