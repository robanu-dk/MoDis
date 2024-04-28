<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{
    public function getVideo(Request $request)
    {
        try {
            if ($request->all_video) {
                if ($request->category_id == '') {
                    $data = $request->limit? DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`title` LIKE \'%' . $request->search . '%\' ORDER BY v.`id` DESC LIMIT ? OFFSET ?', [$request->limit, $request->start]) : DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` ORDER BY v.`id` DESC');
                } else {
                    $data = $request->limit? DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_video_category` = ? AND v.`title` LIKE \'%' . $request->search . '%\' ORDER BY v.`id` DESC LIMIT ? OFFSET ?', [$request->category_id, $request->limit, $request->start]) : DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_video_category` = ? ORDER BY v.`id` DESC', [$request->category_id]);
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                ], 200);
            } else {
                $check_user = User::where('email', $request->email)->first();

                if (!$check_user) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menampilkan video',
                    ], 200);
                }

                if ($request->bearerToken() != $check_user->token || !$check_user->token || !$check_user->verified) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'gagal menampilkan video',
                    ], 200);
                }

                if ($request->category_id == '') {
                    $data = $request->limit? DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_user` = ? AND v.`title` LIKE \'%' . $request->search . '%\' ORDER BY v.`id` DESC LIMIT ? OFFSET ?', [$check_user->id, $request->limit, $request->start]) : DB::select('SELECT v.*, u.`name` FROM `video` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_user` = ? ORDER BY v.`id` DESC', [$check_user->id]);
                } else {
                    $data = $request->limit? DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_user` = ? AND v.`id_video_category` = ? AND v.`title` LIKE \'%' . $request->search . '%\' ORDER BY v.`id` DESC LIMIT ? OFFSET ?', [$check_user->id, $request->category_id, $request->limit, $request->start]) : DB::select('SELECT v.*, u.`name` FROM `video` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_user` = ? AND v.`id_video_category` = ? ORDER BY v.`id` DESC', [$check_user->id, $request->category_id]);
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 400);
        }
    }

    public function updateVideo(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui video',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui video',
                ], 200);
            }

            $video = Video::where('id', $request->video_id)->first();

            if (!$video) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui video',
                ], 200);
            }

            $thumbnail_name = '';
            $video_name = '';

            if ($request->file('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                if (array_search($thumbnail->getClientOriginalExtension(), ['png', 'jpg', 'jpeg', 'svg']) === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'file thumbnail harus berformat PNG, JPG, JPEG, atau SVG'
                    ], 200);
                }

                unlink(public_path() . '/' . $video->thumbnail);

                $thumbnail_name = 'thumbnail/thumbnail_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $thumbnail->getClientOriginalExtension();

                $thumbnail->move(public_path() . '/thumbnail', $thumbnail_name);
            }

            if ($request->file('video')) {
                $video_update = $request->file('video');
                if (array_search($video_update->getClientOriginalExtension(), array('mp4', 'mov', 'webm', 'mkv', 'avi')) === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'file video harus berformat MP4, WebM, MOV, MKV, atau AVI',
                    ], 200);
                }

                $video_name = 'video/video_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $video_update->getClientOriginalExtension();

                unlink(public_path() . '/' . $video->video);

                $video_update->move(public_path() . '/video', $video_name);
            }

            $video->update([
                'title' => $request->title,
                'description' => $request->description,
                'id_video_category' => $request->category,
                'thumbnail' => $thumbnail_name != ''? $thumbnail_name : $video->thumbnail,
                'video' => $video_name != ''? $video_name : $video->video,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_user` = ? ORDER BY v.`id` DESC', [$user->id]),
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

    public function createVideo(Request $request)
    {
        date_default_timezone_set('Asia/Jakarta');
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal upload video',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal upload video',
                ], 200);
            }

            // check thumbnail and video
            if (!$request->file('thumbnail') || !$request->file('video')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal upload video',
                ], 200);
            }

            $thumbnail = $request->file('thumbnail');
            $video = $request->file('video');

            // check type thumbnail and video
            if (array_search($thumbnail->getClientOriginalExtension(), array('png', 'jpg', 'jpeg', 'svg')) === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'file thumbnail harus berformat PNG, JPG, JPEG, atau SVG',
                ], 200);
            }

            if (array_search($video->getClientOriginalExtension(), array('mp4', 'mov', 'webm', 'mkv', 'avi')) === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'file video harus berformat MP4, WebM, MOV, MKV, atau AVI',
                ], 200);
            }

            // upload thumbnail and video
            $video_name = 'video_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $video->getClientOriginalExtension();
            $thumbnail_name = 'thumbnail_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $thumbnail->getClientOriginalExtension();

            $video->move(public_path() . '/video', $video_name);
            $thumbnail->move(public_path() . '/thumbnail', $thumbnail_name);

            // save to db
            Video::create([
                'id_user' => $user->id,
                'title' => $request->title,
                'id_video_category' => $request->category,
                'thumbnail' => 'thumbnail/' . $thumbnail_name,
                'video' => 'video/' . $video_name,
                'description' => $request->description,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT v.*, u.`name` FROM `videos` v LEFT JOIN `users` u ON v.`id_user` = u.`id` WHERE v.`id_user` = ? ORDER BY v.`id` DESC', [$user->id]),
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

    public function deleteVideo(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus video',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus video',
                ], 200);
            }

            $video_check = Video::where('id', $request->video_id)->first();
            if (!$video_check) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'video tidak ditemukan',
                    'video_id' => $request->video_id
                ], 200);
            }

            // delete thumbnail and video
            unlink(public_path() . '/' . $video_check->video);
            unlink(public_path() . '/' . $video_check->thumbnail);

            $video_check->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'video berhasil dihapus'
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
