<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthUserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validation = $this->validator('register',$request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {

                $user = new User();
                $user->name=$request->name;
                $user->email=$request->email;
                $user->password=bcrypt($request->password);
                $user->save();
                // $user = User::create([
                //     'name' => $fields['name'],
                //     'email' => $fields['email'],
                //     'password' => bcrypt($fields['password']),
                // ]);

                $token = $user->createToken('myapptoken')->plainTextToken;

                $response = [
                    'user' => $user,
                    'token' => $token
                ];

                return response($response, 201);
            }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de l'inscription.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
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
        try {
            $validation = $this->validator('login',$request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $credentials = $request->only('email', 'password');

                if (Auth::attempt($credentials)) {
                    $user = Auth::user();
                    $token = $user->createToken('myapptoken')->plainTextToken;

                    $response = [
                        'user' => $user,
                        'token' => $token,
                    ];

                    return response($response, 201);
                } else {
                    return response([
                        'message' => 'Email ou mot de passe incorrect.'
                    ], 401);
                }
            }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de la connexion.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();
        return ['message' => 'Déconnecté'];
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'register') {
            return Validator::make(
                $data,
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:8|confirmed',
                ],
                [
                    ''
                ]
            );
        }
        if ($mode == 'login') {
            return Validator::make(
                $data,
                [
                    'email' => 'required|string|email|max:255',
                    'password' => 'required',
                ],
                [
                    'email.required' => "L'email est obligatoire.",
                    'email.string' => "Format d'email incorrect.",
                    'email.email' => "Cet email est invalide.",
                    'email.max' => "L'email ne doit pas dépasser 255 caractères.",
                    'password.required' => "Le mot de passe est obligatoire.",
                ]
            );
        }
    }
}
