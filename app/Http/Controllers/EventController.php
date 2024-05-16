<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function getAllEvent(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperoleh informasi event',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperoleh informasi event',
                ], 200);
            }

            $search = $request->search ?? '';

            if ($request->limit) {
                $offset = $request->start ?? 0;
                $data = DB::select('SELECT * FROM `events` e WHERE e.`name` LIKE \'%' . $search .'%\' AND e.`status` = 1 ORDER BY e.`date` DESC, e.`start_time` DESC LIMIT ? OFFSET ?', [$request->limit, $offset]);
            } else {
                $data = DB::select('SELECT * FROM `events` e WHERE e.`name` LIKE \'%' . $search .'%\' AND e.`status` = 1 ORDER BY e.`date` DESC, e.`start_time` DESC');
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

    public function createEvent(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menambahkan event',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menambahkan event',
                ], 200);
            }

            $poster = $request->file('event_poster');

            if ($poster) {
                if (array_search($poster->getClientOriginalExtension(), ['jpg', 'png', 'jpeg']) === false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'poster harus dalam bentuk JPG, PNG, atau JPEG',
                    ], 200);
                }

                $filename = 'event_poster_' . substr($poster->getClientOriginalName(), 0, 10) .'_' . time() . '.' . $poster->getClientOriginalExtension();
                $poster->move(public_path() . '/poster', $filename);
            }

            Event::create([
                'name' => $request->event_name,
                'poster' => $poster? 'poster/' . $filename : NULL,
                'type' => $request->event_type,
                'date' => $request->event_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'coordinate_location' => $request->coordinate_location,
                'contact_person' => $request->contact_person,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menambahkan event',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' =>$th->getFile(),
            ], 400);
        }
    }

    public function updateEvent(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui event',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui event',
                ], 200);
            }

            $event = Event::where('id', $request->id_event)->first();
            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui event',
                ], 200);
            }

            if ($request->update_poster) {
                $poster = $request->file('event_poster');

                if ($poster) {
                    if (array_search($poster->getClientOriginalExtension(), ['jpg', 'png', 'jpeg']) === false) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'poster harus berupa JPG, PNG, atau JPEG',
                        ], 200);
                    }

                    $filename = 'event_poster_' . substr($poster->getClientOriginalName(), 0, 10) .'_' . time() . '.' . $poster->getClientOriginalExtension();
                    $poster->move(public_path() . '/poster', $filename);
                }

                if ($event->poster) {
                    unlink(public_path() . '/' . $event->poster);
                }

            }

            $event->update([
                'name' => $request->event_name,
                'poster' => $request->update_poster? ($request->file('event_poster')? 'poster/' . $filename : NULL)  : $event->poster,
                'type' => $request->event_type,
                'date' => $request->event_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'coordinate_location' => $request->coordinate_location,
                'contact_person' => $request->contact_person,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menambahkan event',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' =>$th->getFile(),
            ], 400);
        }
    }

    public function deleteEvent(Request $request) {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus event',
                ], 200);
            }

            if ($request->bearerToken() != $user->token || !$user->token || !$user->verified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus event',
                ], 200);
            }

            $event = Event::where('id', $request->id_event)->first();
            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal menghapus event',
                ], 200);
            }

            $event->update([
                'status' => 0,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil menghapus event',
            ], 200);


        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' =>$th->getFile(),
            ], 400);
        }
    }
}
