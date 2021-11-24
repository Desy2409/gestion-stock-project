<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTypeController extends Controller
{
    public function index()
    {
        // $roles = Role::with('operation')->with('pageOperation')->get();
        $roles = Role::all();
        $userTypes = UserType::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['roles' => $roles, 'user_types' => $userTypes]
        ], 200);
    }

    public function store(Request $request)
    {
        // dd(array($request->checkedRoles));
        $this->validate(
            $request,
            [
                'code' => 'required|unique:user_types',
                'wording' => 'required|unique:user_types|max:150',
                'description' => 'max:255',
                'checkedRoles' => 'required',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.unique' => "Ce code existe déjà.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce type existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'checkedRoles' => "Le choix d'au moins un rôle est obligatoire.",
            ]
        );

        try {
            $userType = new UserType();
            $userType->code = strtoupper(str_replace(' ', '_', $request->code));
            $userType->wording = $request->wording;
            $userType->description = $request->description;
            $userType->roles = $request->checkedRoles;
            $userType->save();


            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'userType' => $userType,
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

        $userType = UserType::findOrFail($id);
        $this->validate(
            $request,
            [
                'code' => 'required',
                'wording' => 'required|max:150',
                'description' => 'max:255',
                'checkedRoles' => 'required',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'checkedRoles' => "Le choix d'au moins un rôle est obligatoire.",
            ]
        );

        $existingUserTypesOnCode = UserType::where('code', $request->code)->get();
        if (!empty($existingUserTypesOnCode) && sizeof($existingUserTypesOnCode) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingUserType' => $existingUserTypesOnCode[0],
                'message' => "Le code " . $existingUserTypesOnCode[0]->wording . " existe déjà"
            ], 200);
        }

        $existingUserTypesOnWording = UserType::where('wording', $request->wording)->get();
        if (!empty($existingUserTypesOnWording) && sizeof($existingUserTypesOnWording) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingUserType' => $existingUserTypesOnWording[0],
                'message' => "Le libellé " . $existingUserTypesOnWording[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $userType->code = strtoupper(str_replace(' ', '_', $request->code));
            $userType->wording = $request->wording;
            $userType->description = $request->description;
            $userType->roles = $request->checkedRoles;
            $userType->save();

            $usersOfThisType = User::where('user_type_id', $userType)->get();
            if (!empty($usersOfThisType) && sizeof($usersOfThisType) > 0) {
                foreach ($usersOfThisType as $key => $user) {
                    $user->roles = $request->checkedRoles;
                    $user->save();
                }
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'userType' => $userType,
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
        $userType = UserType::findOrFail($id);
        try {
            $userType->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'userType' => $userType,
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
