<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->orWhere('username', $request->email)->first();

            if ($user) {
                if (!$user->verified) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'akun menunggu verifikasi, silakan kirim email ke modisapplication@gmail.com',
                    ]);
                }

                if (password_verify($request->password, $user->password)) {
                    // generate token
                    $token = bin2hex(random_bytes(5));

                    // set token
                    $user->update(['token' => $token]);
                    $user->token = $token;

                    // get user nurse
                    $user->guide = $user->id_pendamping? User::find($user->id_pendamping)->name : null;

                    return response()->json([
                        'status' => 'success',
                        'data' => $user
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'tidak berhasil login'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ], 400);
        }
    }

    public function regist(Request $request)
    {
        try {
            if ($request->name == null || $request->username == null || $request->email == null || $request->role === null || $request->jenis_kelamin === null || $request->password == null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'data tidak boleh kosong',
                ], 200);
            }

            $check_email = User::where('email', $request->email)->get();
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

            $data = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'jenis_kelamin' => $request->jenis_kelamin,
                'password' => bcrypt($request->password),
                'verified' => $request->role? 0 : 1,
            ];

            User::create($data);

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

    public function logout(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->orWhere('username', $request->email)->first();

            if ($user) {
                $user->update(['token' => NULL]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil keluar'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ], 400);
        }
    }

    public function forgetPassword(Request $request) {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'email tidak terdaftar',
            ]);
        }

        // generate random otp
        $otp = [random_int(0, 9), random_int(0, 9), random_int(0, 9), random_int(0, 9)];

        $mail = new PHPMailer(true);
        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'smtp';
        $mail->SMTPAuth = true;
        $mail->Username = 'modisapplication@gmail.com';
        $mail->Password = 'ckjumcivzhhuwovu';
        $mail->SMTPOptions = array(
			'tls' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

        $mail->setFrom('modisapplication@gmail.com', 'MoDis (Monitor Disabilitas) Application System');
        $mail->addAddress($user->email, $user->name);
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP';
        $mail->Body = '
        <p>Ini adalah kode OTP untuk melakukan reset password akun anda.</p>
        <p>Waspada pencurian data: <b>Jangan pernah berikan kode ini pada SIAPAPUN</b></p>
        <p>Kode Anda <b>' . implode(" ", $otp) . '</b></p>
        ';

        try {
            $mail->send();

            $user->update(['reset_password_token' => implode("", $otp)]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil mengirim email',
                'otp' => implode("", $otp),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 400);
        }
    }

    public function generateNewRandomPassword(Request $request)
    {
        if (!$request->token) {
            return response()->json([
                'status' => 'error',
                'message' => 'reset password gagal',
            ], 200);
        }

        try {
            $user = User::where('email', $request->email)->where('reset_password_token', $request->token)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'reset password gagal',
                ], 200);
            }

            $new_password = $user->username . '_' . bin2hex(random_bytes(2));
            $user->update([
                'password' => bcrypt($new_password),
                'reset_password_token' => null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil reset password',
                'password' => $new_password,
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

    public function update(Request $request)
    {
        try {
            // get data user
            $user = User::where('email', $request->old_email)->first();

            // check bearer token
            if ($request->bearerToken() != $user->token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'gagal memperbarui informasi akun',
                ], 200);
            }

            // check email and username because they must unique
            $check_email = User::where('email', $request->new_email)->first();
            $check_username = User::where('username', $request->username)->first();

            if ($check_email) {
                if ($check_email->email != $user->email) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'email telah terdaftar'
                    ], 200);
                }
            }

            if ($check_username) {
                if ($check_username->username != $user->username) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'username telah digunakan',
                    ], 200);
                }
            }

            // upload profile image
            $profile_image = $request->file('profile_image');
            $path = 'profile';

            if ($profile_image) {
                // check client extension
                if ($profile_image->getClientOriginalExtension() != 'png' && $profile_image->getClientOriginalExtension() != 'jpg' && $profile_image->getClientOriginalExtension() != 'jpeg') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'file foto harus berformat gambar',
                    ]);
                }

                // remove old_profile
                if ($user->profile_image) {
                    unlink(public_path() . '/' . $user->profile_image);
                }

                $filename = '/profile_image_' . $request->username . '.' . $profile_image->getClientOriginalExtension();
                $path .= $filename;

                $profile_image->move(public_path() . '/profile', $filename);
            } else {
                if ($request->reset_profile_image) {
                    unlink(public_path() . '/' . $user->profile_image);
                    $path = null;
                } else {
                    if ($user->profile_image) {
                        $old_path = explode('.', $user->profile_image);
                        $path = $user->username == $request->username? $user->profile_image : 'profile/profile_image_' . $request->username . '.' . $old_path[count($old_path) - 1];
                        rename(public_path() . '/' . $user->profile_image, public_path() . '/' . $path);
                    }
                }
            }


            // update data
            $user->update([
                'email' => $request->new_email,
                'username' => $request->username,
                'name' => $request->name,
                'jenis_kelamin' => $request->jenis_kelamin,
                'profile_image' => $path,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $user,
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

    public function changePassword(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();

            // check token
            if ($request->bearerToken() == $user->token) {
                $user->update([
                    'password' => bcrypt($request->password),
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'berhasil mengganti password',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'gagal mengganti password',
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
