<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\UserActivity;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function getActivityTodayByUserBasedGuide(Request $request)
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
                'data' => DB::select('SELECT * FROM user_activities ua INNER JOIN activities a ON ua.`id_activity` = a.`id` WHERE ua.`id_user` = ? AND a.`date` = ? ORDER BY a.`start_time` ASC', [$user->id, date('Y-m-d')]),
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

    public function getAllListActivity(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan informasi',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan informasi',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT a.*, ua.`done` FROM `activities` a INNER JOIN `user_activities` ua ON a.`id` = ua.`id_activity` WHERE ua.`id_user` = ? ORDER BY a.`date` DESC, a.`start_time` DESC', [$user->id]),
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

    public function getDetailActivity(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan informasi kegiatan',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->son([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan informasi kegiatan',
                ], 200);
            }

            $activity = Activity::where('id', $request->activity_id)->first();

            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan informasi kegiatan',
                ], 200);
            }

            $data = [];
            if ($user->role) {
                $data = DB::select('SELECT a.*, ua.`done`, ua.`start_time` AS `start_activity_time`, ua.`finishing_time` AS `finishing_activity_time`, ua.`track_coordinates` AS `coordinates`, u.`id` AS `user_id`, u.`name` AS `user_name`, u.`email`, u.`profile_image` FROM `activities` a INNER JOIN `user_activities` ua ON a.`id` = ua.`id_activity` LEFT JOIN `users` u ON u.`id` = ua.`id_user` WHERE a.`id` = ?', [$activity->id]);
            } else {
                $data = DB::select('SELECT a.*, ua.`done`, ua.`start_time` AS `start_activity_time`, ua.`finishing_time` AS `finishing_activity_time`, ua.`track_coordinates` AS `coordinates`, u.`id` AS `user_id`, u.`name` AS `user_name`, u.`email`, u.`profile_image` FROM `activities` a INNER JOIN `user_activities` ua ON a.`id` = ua.`id_activity` LEFT JOIN `users` u ON u.`id` = ua.`id_user` WHERE a.`id` = ? AND ua.`id_user` = ?', [$activity->id, $user->id]);
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

    public function getCoordinatesUserByGuide(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan koordinat',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->role || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan koordinat',
                ], 200);
            }

            $user_activity = UserActivity::where('id_user', $request->child_id)->where('id_activity')->first();

            if (!$user_activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal mendapatkan koordinat',
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'data' => $user_activity,
            ], 200);
        } catch (\THrowable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 400);
        }
    }

    public function createActivity(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => '',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->son([
                    'status' => 'error',
                    'message' =>'',
                ], 200);
            }

            $activity = new Activity();
            $activity->name = $request->activity_name;
            $activity->date = $request->activity_date;
            $activity->start_time = $request->activity_start_time;
            $activity->end_time = $request->activity_end_time;
            $activity->note = $request->activity_note;
            $activity->created_by_guide = $request->list_child_account_id && $user->role;
            $activity->created_at = now();
            $activity->updated_at = now();
            $activity->save();

            UserActivity::create([
                'id_user' => $user->id,
                'id_activity' => $activity->id,
            ]);

            if ($user->role && $request->list_child_account_id) {
                foreach ($request->list_child_account_id as $child_id) {
                    UserActivity::create([
                        'id_user' => $child_id,
                        'id_activity' => $activity->id,
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menambahkan kegiatan',
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

    public function updateActivity(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui kegiatan',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->son([
                    'status' => 'error',
                    'message' => 'gagal memperbarui kegiatan',
                ], 200);
            }

            $activity = Activity::where('id', $request->activity_id)->first();

            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui kegiatan',
                ], 200);
            }

            if ($activity->created_by_guide && !$user->role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui kegiatan',
                ], 200);
            }

            $activity->update([
                'name' => $request->activity_name,
                'date' => $request->activity_date,
                'start_time' => $request->activity_start_time,
                'end_time' => $request->activity_end_time,
                'note' => $request->activity_note,
                'created_by_guide' => $request->list_child_account_id && $user->role,
            ]);

            if ($user->role && $request->list_child_account_id) {
                $list_child_id = implode('\',\'', $request->list_child_account_id);
                DB::delete('DELETE FROM `user_activities` WHERE `id_activity` = ? AND `id_user` NOT IN (\'' . $list_child_id . '\')', [$activity->id]);

                foreach($request->list_child_account_id as $child_id) {
                    if (!UserActivity::where('id_user', $child_id)->where('id_activity', $activity->id)->first()) {
                        UserActivity::create([
                            'id_user' => $child_id,
                            'id_activity' => $activity->id,
                        ]);
                    }
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'berhasil memperbarui kegiatan',
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

    public function deleteActivity(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus kegiatan',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->son([
                    'status' => 'error',
                    'message' => 'gagal menghapus kegiatan',
                ], 200);
            }

            $activity = Activity::where('id', $request->activity_id)->first();

            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus kegiatan',
                ], 200);
            }

            $date_event = new DateTime($activity->date . ' ' . $activity->start_time);
            $date_now = new DateTime();

            if ($date_now > $date_event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus kegiatan',
                ], 200);
            }

            if ($activity->created_by_guide && !$user->role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus kegiatan',
                ], 200);
            }

            if ($user->role) {
                DB::delete('DELETE FROM `user_activities` WHERE `id_activity` = ?', [$activity->id]);
            } else {
                DB::delete('DELETE FROM `user_activities` WHERE `id_user` = ? AND `id_activity` = ?', [$user->id, $activity->id]);
            }

            $activity->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menghapus kegiatan',
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

    public function finishActivity(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menyelesaikan kegiatan',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token) {
                return response()->son([
                    'status' => 'error',
                    'message' => 'gagal menyelesaikan kegiatan',
                ], 200);
            }

            $activity = Activity::where('id', $request->activity_id)->first();
            if (!$activity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menyelesaikan kegiatan',
                ], 200);
            }

            $user_activities = UserActivity::where('id_activity', $activity->id)->where('id_user', $user->id)->first();

            if (!$user_activities) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menyelesaikan kegiatan',
                ], 200);
            }

            $user_activities->update([
                'start_time' => $request->start_time,
                'finishing_time' => $request->finishing_time,
                'track_coordinates' => $request->track_coordinates,
                'done' => 1,
            ]);

            foreach ($request->list_child_account_id as $child_id) {
                $child_activity = UserActivity::where('id_activity', $activity->id)->where('id_user', $child_id)->first();
                if ($child_activity) {
                    if (!$child_activity->done) {
                        $child_activity->update([
                            'start_time' => $request->start_time,
                            'finishing_time' => $request->finishing_time,
                            'track_coordinates' => $request->track_coordinates,
                            'done' => 1,
                        ]);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menyelesaikan event',
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
