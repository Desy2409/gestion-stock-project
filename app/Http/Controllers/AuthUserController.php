<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
class AuthUserController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        $token = $user->createToken('myapptoken')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    // public function login(Request $request){
    //     $credentials = $request->only('email', 'password');
    //     try {
    //         if(!JWTAuth::attempt($credentials)){
    //             $response['status']= 0;
    //             $response['code'] = 401;
    //             $response['data'] = null;
    //             $response['message'] = "Email ou mot de passe incorrect";
    //             return response()->json($response);
    //         }
    //     } catch (JWTException $e) {
    //         $response['data']= null;
    //         $response['code'] = 500;
    //         $response['message'] = "Le jeton n'a pas pu être crée";
    //         return response()->json($response);
    //     }

    //     $user = auth()->user();
    //     $data['token'] = auth()->claims([
    //         'user_id' => $user->id,
    //         'email' => $user->email
    //     ])->attempt($credentials);
    //     $response['status']= 1;
    //     $response['code'] = 200;
    //     $response['data'] = $data;
    //     $response['message'] = "Connecté avec succès";
    //     return response()->json($response);
    // }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if(Auth::attempt($credentials)){
            $user = Auth::user();
            $token = $user->createToken('myapptoken')->plainTextToken;

            $response = [
                'user' => $user,
                'token' => $token,
            ];

            return response($response, 201);

        }else{
            return response([
                'message'=>'Email ou mot de passe incorrect.'
            ], 401);
        }

    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return ['message' => 'Déconnecté'];
    }
}
