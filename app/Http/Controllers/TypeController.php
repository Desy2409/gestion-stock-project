<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\PageOperation;
use App\Models\Role;
use App\Models\Type;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TypeController extends Controller
{
    public function index()
    {
        $roles = Role::with('operation')->with('pageOperation')->get();
        // $operations = Operation::all();
        // $pageOperations = PageOperation::all();
        $types = Type::with('roles')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['roles' => $roles, 'types' => $types]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'code' => 'required|unique:types',
                'wording' => 'required|unique:types|max:150',
                'description' => 'max:255',
                'checked_roles' => 'required',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.unique' => "Ce code existe déjà.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce type existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'checked_roles' => "Le choix d'au moins un rôle est obligatoire.",
            ]
        );

        try {
            $type = new Type();
            $type->code = strtoupper(str_replace(' ','_',$request->code));
            $type->wording = $request->wording;
            $type->description = $request->description;
            $type->save();

            if (!empty($request->checked_roles) && sizeof($request->checked_roles) > 0) {
                foreach ($request->checked_roles as $key => $checked_role) {
                    $role = Role::findOrFail($checked_role);
                    DB::table('role_types')->insert([
                        ['role_id' => $role->id],
                        ['type_id' => $type->id],
                    ]);
                }
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'type' => $type,
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

        $type = Type::findOrFail($id);
        $this->validate(
            $request,
            [
                'code' => 'required',
                'wording' => 'required|max:150',
                'description' => 'max:255',
                'checked_roles' => 'required',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.unique' => "Ce code existe déjà.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'checked_roles' => "Le choix d'au moins un rôle est obligatoire.",
            ]
        );

        $existingTypesOnCode = Type::where('code', $request->code)->get();
        if (!empty($existingTypesOnCode) && sizeof($existingTypesOnCode) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingType' => $existingTypesOnCode[0],
                'message' => "Le code " . $existingTypesOnCode[0]->wording . " existe déjà"
            ], 200);
        }

        $existingTypesOnWording = Type::where('wording', $request->wording)->get();
        if (!empty($existingTypesOnWording) && sizeof($existingTypesOnWording) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingType' => $existingTypesOnWording[0],
                'message' => "Le libellé " . $existingTypesOnWording[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $type->code = strtoupper(str_replace(' ','_',$request->code));
            $type->wording = $request->wording;
            $type->description = $request->description;
            $type->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'type' => $type,
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
        $type = Type::findOrFail($id);
        try {
            $type->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'type' => $type,
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
