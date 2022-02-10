<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\Role;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperationController extends Controller
{
    public function index()
    {
        $this->authorize('ROLE_OPERATION_READ', Operation::class);
        $operations = Operation::with('roles')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['operations' => $operations]
        ], 200);
    }

    public function rolesOfOperation($id)
    {
        $this->authorize('ROLE_OPERATION_READ', Operation::class);
        $roles = Role::where('operation_id', $id)->get();
        return new JsonResponse(['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'code' => 'required|unique:operations|max:50',
                'wording' => 'required|unique:operations|max:150',
                'description' => 'max:255',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.unique' => "Ce code existe déjà.",
                'code.max' => "Le code ne doit pas dépasser 50 caractères.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce libellé existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $operation = new Operation();
            $operation->code = strtoupper(str_replace(' ', '_', $request->code));
            $operation->wording = $request->wording;
            $operation->description = $request->description;
            $operation->save();

            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'operation' => $operation,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $operation = Operation::findOrFail($id);
        $this->validate(
            $request,
            [
                'code' => 'required|max:50',
                'wording' => 'required|max:150',
                'description' => 'max:255',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.max' => "Le code ne doit pas dépasser 50 caractères.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        $existingOperationsOnCode = Operation::where('code', $request->code)->get();
        if (!empty($existingOperationsOnCode) && sizeof($existingOperationsOnCode) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingOperation' => $existingOperationsOnCode[0],
                'message' => "Le code " . $existingOperationsOnCode[0]->wording . " existe déjà"
            ], 200);
        }

        $existingOperationsOnWording = Operation::where('wording', $request->wording)->get();
        if (!empty($existingOperationsOnWording) && sizeof($existingOperationsOnWording) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingOperation' => $existingOperationsOnWording[0],
                'message' => "Le libellé " . $existingOperationsOnWording[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $operation->code = strtoupper(str_replace(' ', '_', $request->code));
            $operation->wording = $request->wording;
            $operation->description = $request->description;
            $operation->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'operation' => $operation,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function destroy($id)
    {
        $operation = Operation::findOrFail($id);
        try {
            $operation->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'operation' => $operation,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }
}
