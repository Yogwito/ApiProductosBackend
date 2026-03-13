<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciales invalidas'], 401);
        }

        // El codigo se guarda cifrado y vence en pocos minutos.
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->verification_code = Hash::make($code);
        $user->verification_code_expires_at = now()->addMinutes((int) config('auth.verification_code_ttl', 10));
        $user->save();

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
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $user = User::where('email', $data['email'])->first();

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

        if (! Hash::check($data['code'], $user->verification_code)) {
            return response()->json([
                'message' => 'Codigo de verificacion invalido o expirado',
            ], 401);
        }

        $token = auth('api')->login($user);
        $this->clearVerificationCode($user);

        return response()->json([
            'message' => 'Codigo verificado correctamente',
            'token' => $token,
            'user' => $user->fresh(),
        ]);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Logout correcto',
        ]);
    }

    public function refresh()
    {
        return $this->refreshToken();
    }

    public function refreshToken()
    {
        $user = auth('api')->user();
        $token = auth('api')->refresh();

        return response()->json([
            'message' => 'Token renovado correctamente',
            'token' => $token,
            'user' => $user,
        ]);
    }

    private function clearVerificationCode(User $user): void
    {
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();
    }
}
