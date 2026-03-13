<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales invalidas'], 401);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'verification_code' => Hash::make($code),
            'verification_code_expires_at' => now()->addMinutes((int) config('auth.verification_code_ttl', 10)),
        ])->save();

        try {
            Mail::to($user->email)->send(new VerificationCodeMail($user, $code));
        } catch (Throwable $exception) {
            $this->clearVerificationCode($user);
            report($exception);

            return response()->json([
                'message' => 'No se pudo enviar el codigo de verificacion',
            ], 500);
        }

        return response()->json([
            'message' => 'Codigo de verificacion enviado al correo',
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->verification_code || ! $user->verification_code_expires_at) {
            return response()->json([
                'message' => 'Codigo de verificacion invalido o expirado',
            ], 401);
        }

        if ($user->verification_code_expires_at->isPast()) {
            $this->clearVerificationCode($user);

            return response()->json([
                'message' => 'Codigo de verificacion invalido o expirado',
            ], 401);
        }

        if (! Hash::check($request->code, $user->verification_code)) {
            return response()->json([
                'message' => 'Codigo de verificacion invalido o expirado',
            ], 401);
        }

        try {
            $token = JWTAuth::fromUser($user);
            $this->clearVerificationCode($user);

            return response()->json([
                'message' => 'Codigo verificado correctamente',
                'token' => $token,
                'user' => $user->fresh(),
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'message' => 'No se pudo generar el token',
            ], 500);
        }
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Logout correcto',
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'message' => 'No se pudo cerrar la sesion',
            ], 500);
        }
    }

    public function refresh()
    {
        return $this->refreshToken();
    }

    public function refreshToken()
    {
        try {
            $user = auth('api')->user();
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'message' => 'Token renovado correctamente',
                'token' => $token,
                'user' => $user ?? JWTAuth::setToken($token)->toUser(),
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'message' => 'No se pudo renovar el token',
            ], 401);
        }
    }

    private function clearVerificationCode(User $user): void
    {
        $user->forceFill([
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ])->save();
    }
}
