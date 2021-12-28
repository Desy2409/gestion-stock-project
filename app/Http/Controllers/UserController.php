<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\PageOperation;
use App\Models\Person;
use App\Models\SalePoint;
use App\Models\User;
use App\Models\UserType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // public $user;

    // public function __construct($user)
    // {
    //     // dd(Auth::user());
    //     $user = Auth::user();
    //     $this->user = $user;
    // }

    public function index()
    {
        $this->authorize('ROLE_USER_READ', User::class);
        $users = User::orderBy('last_name')->orderBy('first_name')->get();
        $userTypes = UserType::orderBy('wording')->get();
        // $roles = Role::with('operation')->with('pageOperation')->get();
        $pageOperations = PageOperation::orderBy('title')->get();
        $operations = Operation::orderBy('wording')->get();
        // $roles = Role::all();
        return new JsonResponse([
            'datas' => ['users' => $users, 'userTypes' => $userTypes, 'pageOperations' => $pageOperations, 'operations' => $operations]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_USER_CREATE', User::class);
        // dd('user store after check role');
        $this->validate(
            $request,
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
                'last_name' => 'required|max:30',
                'first_name' => 'required|max:50',
                'date_of_birth' => 'required|date|before:today', //|date_format:Ymd
                'place_of_birth' => 'required|max:100',
            ],
            [
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
                // 'date_of_birth.date_format' => "Le champ date de naissance doit être sous le format : Année Mois Jour.",
                'date_of_birth.before' => "Le champ date de naissance doit être antérieure ou égale à aujourd'hui.",
            ]
        );

        // $existingUser = UserType::where('last_name', $request->last_name)->where('first_name', $request->first_name)->get();
        // if (!empty($existingUser) && sizeof($existingUser) >= 1) {
        //     $success = false;
        //     return new JsonResponse([
        //         'success' => $success,
        //         'existingUserType' => $existingUser[0],
        //         'message' => "Cet utilisateur existe déjà"
        //     ], 200);
        // }

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
            // dd($e);
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function edit($id)
    {
        $this->authorize('ROLE_USER_UPDATE', User::class);
        $user = User::findOrFail($id);
        return new JsonResponse(['user' => $user], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_USER_UPDATE', User::class);
        $user = User::findOrFail($id);
        $this->validate(
            $request,
            [
                'email' => 'required|email',
                'password' => 'required|min:8',
                'last_name' => 'required|max:30',
                'first_name' => 'required|max:50',
                'date_of_birth' => 'required|date|before:today', //|date_format:Ymd
                'place_of_birth' => 'required|max:100',
            ],
            [
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
                // 'date_of_birth.date_format' => "Le champ date de naissance doit être sous le format : Année Mois Jour.",
                'date_of_birth.before' => "Le champ date de naissance doit être antérieure ou égale à aujourd'hui.",
            ]
        );

        $existingUser = Person::where('last_name', $request->last_name)->where('first_name', $request->first_name)->get();
        if (!empty($existingUser) && sizeof($existingUser) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingUser' => $existingUser[0],
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
        $this->authorize('ROLE_USER_DELETE', User::class);
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

    public function userTypeConfiguration(Request $request, $id)
    {
        $this->authorize('ROLE_USER_CREATE', User::class);
        $user = User::findOrFail($id);
        $userType = UserType::findOrFail($request->user_type);

        try {
            $user->user_type_id = $userType->id;
            $user->roles = $userType->roles;
            $user->save();

            $success = true;
            $message = "Type d'utilisateur attribué avec succès.";
            return new JsonResponse([
                'user' => $user,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'attribution d'un type à l'utilisateur.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function rolesConfiguration(Request $request, $id)
    {
        $this->authorize('ROLE_USER_CREATE', User::class);
        $user = User::findOrFail($id);

        try {
            $roles = array_merge($user->roles, $request->checkedRoles);

            $user->roles = $roles;
            $user->save();

            $success = true;
            $message = "Rôle(s) affecté(s) avec succès.";
            return new JsonResponse([
                'user' => $user,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'affectation des rôles à l'utilisateur.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function salePointsConfiguration(Request $request, $id)
    {
        // dd('user',Auth::user()->sale_points->toArray());
        // dd($this->user);

        $user = Auth::user();

        $userSalePoints = SalePoint::whereIn('id', $user->sale_points)->get();
        // dd('userSalePoints', $userSalePoints);


        $this->authorize('ROLE_USER_CREATE', User::class);
        $user = User::findOrFail($id);

        try {
            $user->sale_points = $request->sale_points;
            $user->save();

            $success = true;
            $message = "Point(s) de vente affecté(s) avec succès.";
            return new JsonResponse([
                'user' => $user,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            // dd($e);
            $success = false;
            $message = "Erreur survenue lors de l'affectation des points de vente à l'utilisateur.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
