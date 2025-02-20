<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Response\ApiResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        return ApiResponse::handleResponse(
            function () use ($request) {
                User::create([
                    'nama' => $request->nama,
                    'email' => $request->email,
                    'npp' => $request->npp,
                    'npp_supervisor' => $request->npp_supervisor,
                    'password' => Hash::make($request->password)
                ]);

                return ApiResponse::successResponse('Berhasil mendaftarkan akun, silakan masuk');
            }
        );
    }

    public function login(LoginRequest $request)
    {
        return ApiResponse::handleResponse(function () use ($request) {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return ApiResponse::errorResponse('Email atau password salah, silahkan masukan ulang', null, 401);
            }

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::successResponse('Berhasil masuk', [
                'token' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
                'user' => $user,
            ]);
        });
    }
}
