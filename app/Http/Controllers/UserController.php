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
                if (password_verify($request->password, $user->password)) {
                    // generate token
                    $token = bin2hex(random_bytes(5));

                    // set token
                    $user->update(['token' => $token]);
                    $user->token = $token;

                    return response()->json([
                        'status' => 'success',
                        'data' => $user
                    ], 200);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'gagal masuk'
            ]);
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
}
