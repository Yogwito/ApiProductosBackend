<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request){

        $validador = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if($validador->fails()){
            return response()->json(['errors' => $validador->errors()], 422);
        }

        $credentials = $request->only('email', 'password');

        if(! $token = JWTAuth::attempt($credentials)){
            return response()->json(['message' => 'Credenciales invalidas'], 401);
        }

        return response()->json([
            'message' => 'Login Correcto',
            'token' => $token,
            'user' => JWTAuth::setToken($token)->toUser(),
        ]);
    }

    public function me(){
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
}
