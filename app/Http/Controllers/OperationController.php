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
        $operations = Operation::with('roles')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['operations' => $operations]
        ], 200);
    }
    
    public function rolesOfOperation($id)
    {
        $roles = Role::where('operation_id', $id)->get();
        return new JsonResponse(['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:operations|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette opération existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $operation = new Operation();
            $operation->code = Str::random(10);
            $operation->wording = $request->wording;
            $operation->description = $request->description;
            $operation->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'operation' => $operation,
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
        $operation = Operation::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        $existingOperations = Operation::where('wording', $request->wording)->get();
        if (!empty($existingOperations) && sizeof($existingOperations) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingOperation' => $existingOperations[0],
                'message' => "L'opération " . $existingOperations[0]->wording . " existe déjà"
            ], 200);
        }

        try {
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
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
