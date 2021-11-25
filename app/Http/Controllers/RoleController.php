<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\PageOperation;
use App\Models\Role;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('operation')->with('pageOperation')->orderBy('wording')->get();
        $pageOperations = PageOperation::orderBy('wording')->get();
        $operations = Operation::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['roles' => $roles, 'operations' => $operations, 'pageOperations' => $pageOperations]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:roles|max:150',
                'page_operation' => 'required',
                'operation' => 'required',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce rôle existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'operation.required' => "L'opération est obligatoire.",
                'page_operation.required' => "L'opération de page est obligatoire.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $role = new Role();
            $role->code = Str::random(10);
            $role->wording = $request->wording;
            $role->operation_id = $request->operation;
            $role->page_operation_id = $request->page_operation;
            $role->description = $request->description;
            $role->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'role' => $role,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
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
        $role = Role::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:150',
                'page_operation' => 'required',
                'operation' => 'required',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'operation.required' => "L'opération est obligatoire.",
                'page_operation.required' => "L'opération de page est obligatoire.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        $existingRoles = Role::where('wording', $request->wording)->get();
        if (!empty($existingRoles) && sizeof($existingRoles) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingRole' => $existingRoles[0],
                'message' => "Le rôle " . $existingRoles[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $role->wording = $request->wording;
            $role->operation_id = $request->operation;
            $role->page_operation_id = $request->page_operation;
            $role->description = $request->description;
            $role->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'role' => $role,
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
        $role = Role::findOrFail($id);
        try {
            $role->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'role' => $role,
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

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return new JsonResponse([
            'role' => $role
        ], 200);
    }
}
