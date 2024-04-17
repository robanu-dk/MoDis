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
                $data = $request->limit? DB::select('SELECT * FROM `videos` v ORDER BY v.`published_at` DESC LIMIT ? OFFSET ?', [$request->limit, $request->offset]) : DB::select('SELECT * FROM `videos` v ORDER BY v.`published_at` DESC');

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

                $data = $request->limit? DB::select('SELECT * FROM `videos` v WHERE v.`id_user` = ? ORDER BY v.`published_at` DESC LIMIT ? OFFSET ?', [$check_user->id, $request->limit, $request->offset]) : DB::select('SELECT * FROM `video` v WHERE v.`id_user` = ? ORDER BY v.`published_at` DESC', [$check_user->id]);

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

                $thumbnail_name = 'thumbnail_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $thumbnail->getClientOriginalExtension();

                $thumbnail->move(public_path() . '/thumbnail', $thumbnail_name);
            }

            if ($request->file('video')) {
                $video_update = $request->file('video');
                if (array_search($video_update->getClientOriginalExtension(), ['mp4', 'webm']) === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'file video harus berformat MP4 atau WebM',
                    ], 200);
                }

                $video_name = 'video_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $video_update->getClientOriginalExtension();

                unlink(public_path() . '/' . $video->video);

                $video_update->move(public_path() . '/video', $video_name);
            }

            $video->update([
                'title' => $request->title,
                'description' => $request->description,
                'thumbnail' => $thumbnail_name != ''? $thumbnail_name : $video->thumbnail,
                'video' => $video_name != ''? $video_name : $video->video,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT * FROM `videos` v WHERE v.`id_user` = ? ORDER BY v.`id` DESC'),
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

            if (array_search($video->getClientOriginalExtension(), array('mp4', 'webm')) === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'file video harus berformat MP4 atau WebM',
                ], 200);
            }

            // upload thumbnail
            $thumbnail_name = 'thumbnail_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $thumbnail->getClientOriginalExtension();
            $video_name = 'video_created_by_' . $user->username . '_at_' . date('Y_m_d') . '_' . time() . '_title_' . $request->title . '.' . $video->getClientOriginalExtension();

            $thumbnail->move(public_path() . '/thumbnail', $thumbnail_name);
            $video->move(public_path() . '/video', $video_name);

            // save to db
            Video::create([
                'id_user' => $user->id,
                'title' => $request->title,
                'thumbnail' => 'thumbnail/' . $thumbnail_name,
                'video' => 'video/' . $video_name,
                'description' => $request->description,
            ]);

            return response()->json([
                'status' => 'error',
                'data' => DB::select('SELECT * FROM `videos` v WHERE v.`id_user` = ? ORDER BY v.`id` DESC'),
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

            Video::find($request->video_id)->delete();

            return response()->json([
                'status' => 'success',
                'data' => DB::select('SELECT * FROM `videos` v WHERE v.`id_user` = ? ORDER BY v.`id` DESC'),
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
