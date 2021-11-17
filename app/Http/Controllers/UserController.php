<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('last_name')->orderBy('first_name')->get();
        $userTypes = UserType::orderBy('wording')->get();

        return new JsonResponse([
            'datas' => ['users' => $users, 'userTypes' => $userTypes]
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'last_name' => 'required|max:30',
            'first_name' => 'required|max:50',
            'date_of_birth' => 'required|date|date_format:Ymd|before:today',
            'place_of_birth' => 'required|max:100',
        ], [
            'email.required' => "Le champ email est obligatoire.",
            'email.email' => "Le champ email est incorrect.",
            'password.required' => "Le champ mot de passe est obligatoire.",
            'password.min' => "Le champ mot de passe doit contenir 8 caractères.",
            'last_name.required' => "e champ nom est obligatoire.",
            'last_name.max' => "Le champ nom ne doit pas dépasser 30 caractères.",
            'first_name.required' => "Le champ prénom(s) est obligatoire.",
            'first_name.max' => "Le champ prénom(s) ne doit pas dépasser 50 caractères.",
            'date_of_birth.required' => "Le champ date de naissance est obligatoire.",
            'date_of_birth.date' => "Le champ date de naissance doit être une date valide.",
            'date_of_birth.date_format' => "Le champ date de naissance doit être sous le format : Année Mois Jour.",
            'date_of_birth.before' => "Le champ date de naissance doit être antérieure ou égale à aujourd'hui.",
        ]);

        $existingUser = UserType::where('last_name', $request->last_name)->where('first_name', $request->first_name)->get();
        if (!empty($existingUser) && sizeof($existingUser) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingUserType' => $existingUser[0],
                'message' => "Cet utilisateur existe déjà"
            ], 200);
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->last_name = $request->last_name;
            $user->first_name = $request->first_name;
            $user->date_of_birth = $request->date_of_birth;
            $user->place_of_birth = $request->place_of_birth;
            $user->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'user' => $user,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            dd($e);
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'last_name' => 'required|max:30',
            'first_name' => 'required|max:50',
            'date_of_birth' => 'required|date|date_format:Ymd|before:today',
            'place_of_birth' => 'required|max:100',
        ], [
            'email.required' => "Le champ email est obligatoire.",
            'email.email' => "Le champ email est incorrect.",
            'password.required' => "Le champ mot de passe est obligatoire.",
            'password.min' => "Le champ mot de passe doit contenir 8 caractères.",
            'last_name.required' => "e champ nom est obligatoire.",
            'last_name.max' => "Le champ nom ne doit pas dépasser 30 caractères.",
            'first_name.required' => "Le champ prénom(s) est obligatoire.",
            'first_name.max' => "Le champ prénom(s) ne doit pas dépasser 50 caractères.",
            'date_of_birth.required' => "Le champ date de naissance est obligatoire.",
            'date_of_birth.date' => "Le champ date de naissance doit être une date valide.",
            'date_of_birth.date_format' => "Le champ date de naissance doit être sous le format : Année Mois Jour.",
            'date_of_birth.before' => "Le champ date de naissance doit être antérieure ou égale à aujourd'hui.",
        ]);

        $existingUser = UserType::where('last_name', $request->last_name)->where('first_name', $request->first_name)->get();
        if (!empty($existingUser) && sizeof($existingUser) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingUserType' => $existingUser[0],
                'message' => "Cet utilisateur existe déjà"
            ], 200);
        }

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->last_name = $request->last_name;
            $user->first_name = $request->first_name;
            $user->date_of_birth = $request->date_of_birth;
            $user->place_of_birth = $request->place_of_birth;
            $user->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'user' => $user,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        try {
            $user->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'user' => $user,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
