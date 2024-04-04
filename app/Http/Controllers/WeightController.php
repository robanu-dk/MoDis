<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Weight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'data' => DB::select('SELECT * FROM `weights` w WHERE w.`id_user` = ? ORDER BY w.`date` DESC', [$user->id]),
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
                'data' => DB::select('SELECT * FROM `weights` w WHERE w.`id_user` = ? ORDER BY w.`date` DESC', [$user->id]),
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

    public function storeWeight(Request $request)
    {
        try {
            // by guide
            if ($request->guide_email) {
                $guide = User::where('email', $request->guide_email)->first();

                if (!$guide) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menyimpan data berat badan',
                    ], 200);
                }

                if ($request->bearerToken() != $guide->token || !$guide->token || !$guide->verified) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menyimpan data berat badan',
                    ], 200);
                }

                $user = User::where('email', $request->user_email)->where('guide_id', $guide->id)->first();
            }

            // by user
            else {
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menyimpan data berat badan',
                    ], 200);
                }

                if ($request->bearerToken() != $user->token || !$user->token) {
                    return response()-> json([
                        'status' => 'error',
                        'message' => 'gagal menyimpan data berat badan',
                    ], 200);
                }
            }

            Weight::create([
                'weight' => $request->weight,
                'id_user' => $user->id,
                'date' => date('Y-m-d', strtotime($request->date)),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT * FROM `weight` w WHERE w.`id_user` = ? ORDER BY w.`date` DESC', [$user->id]),
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

    public function updateWeight(Request $request)
    {
        try {

            // by guide
            if ($request->guide_email) {
                $guide = User::where('email', $request->guide_email)->first();

                if (!$guide) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal memperbarui data berat badan',
                    ], 200);
                }

                if ($request->bearerToken() != $guide->token || !$guide->token || !$guide->verified) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal memperbarui data berat badan',
                    ], 200);
                }

                $user = User::where('email', $request->user_email)->where('guide_id', $guide->id)->first();

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal memperbarui data berat badan',
                    ], 200);
                }
            }

            // by user
            else {
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal memperbarui data berat badan',
                    ], 200);
                }

                if ($request->bearerToken() != $user->token || !$user->token) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal memperbarui data berat badan',
                    ], 200);
                }
            }

            // update
            $weight = Weight::where('id_user', $user->id)->where('id', $request->weight_id)->first();

            if (!$weight) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menyimpan data berat badan',
                ], 200);
            }

            $weight->update([
                'weight' => $request->weight,
                'date' => date('Y-m-d', strtotime($request->date)),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $weight,
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

    public function deleteWeight(Request $request)
    {
        try {

            // by guide
            if ($request->guide_email) {
                $guide = User::where('email', $request->guide_email)->first();

                if (!$guide) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menghapus data berat badan',
                    ], 200);
                }

                if ($request->bearerToken() != $guide->token || !$guide->token || !$guide->verified) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menghapus data berat badan',
                    ], 200);
                }

                $user = User::where('email', $request->user_email)->where('guide_id', $guide->id)->first();

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menghapus data berat badan',
                    ], 200);
                }
            }

            // by user
            else {
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menghapus data berat badan',
                    ], 200);
                }

                if ($request->bearerToken() != $user->token || !$user->token) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menghapus data berat badan',
                    ], 200);
                }
            }

            DB::delete('DELETE `weight` w WHERE w.`id` = ? AND w.`user_id` = ?', [$request->weight_id, $user->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menghapus data berat badan',
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
