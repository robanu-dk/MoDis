<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Weight;
use Illuminate\Http\Request;

class WeightController extends Controller
{
    public function getWeightByGuide(Request $request)
    {
        $error_response = [
            'status' => 'error',
            'message' => 'gagal mendapatkan data berat badan',
        ];

        try {
            $guide = User::where('email', $request->guide_email)->first();

            if (!$guide) {
                return response()->json($error_response, 200);
            }

            if ($request->bearerToken() != $guide->token || !$guide->token || !$guide->verified) {
                return response()->json($error_response, 200);
            }

            $user = User::where('email', $request->user_email)->where('guide_id', $guide->id)->first();

            if (!$user) {
                return response()->json($error_response, 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => Weight::where('id_user', $user->id)->get(),
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

    public function getUserWeight(Request $request)
    {
        $error_response = [
            'status' => 'error',
            'message' => 'gagal mendapatkan data berat badan',
        ];

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json($error_response, 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->json($error_response, 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => Weight::where('id_user', $user->id)->get(),
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
