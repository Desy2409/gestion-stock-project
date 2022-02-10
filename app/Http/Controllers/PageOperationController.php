<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\PageOperation;
use App\Models\Role;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\File as FacadesFile;

class PageOperationController extends Controller
{
    private $role = "ROLE_";

    public function index()
    {
        $pageOperations = PageOperation::with('roles')->orderBy('wording')->get();
        $operations = Operation::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['pageOperations' => $pageOperations, 'operations' => $operations, 'roles' => $this->roles]
        ], 200);
    }

    public function rolesOfPageOperation($id)
    {
        $roles = Role::where('page_operation_id', $id)->get();
        return new JsonResponse(['roles' => $roles]);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'code' => 'required|unique:page_operations|max:50',
                'wording' => 'required|unique:page_operations|max:150',
                'description' => 'max:255',
                'checked_operations' => 'required',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.unique' => "Ce code existe déjà.",
                'code.max' => "Le code ne doit pas dépasser 50 caractères.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce libellé existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'checked_operations.required' => "Le choix d'au moins une opération est obligatoire."
            ]
        );

        try {
            $pageOperation = new PageOperation();
            $pageOperation->code = strtoupper(str_replace(' ', '_', $this->role . $request->code));
            $pageOperation->wording = $request->wording;
            $pageOperation->description = $request->description;
            $pageOperation->save();

            if (!empty($request->checked_operations) && sizeof($request->checked_operations) > 0) {
                foreach ($request->checked_operations as $key => $checked_operation) {
                    $operation = Operation::findOrFail($checked_operation);
                    $role = new Role();
                    $role->code = $pageOperation->code . $operation->wording;
                    $role->wording = $pageOperation->code . $operation->wording;
                    $role->page_operation_id = $pageOperation->id;
                    $role->operation_id = $operation->id;
                    $role->save();
                }
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'pageOperation' => $pageOperation,
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

        $pageOperation = PageOperation::findOrFail($id);
        $this->validate(
            $request,
            [
                'code' => 'required|max:50',
                'wording' => 'required|max:150',
                'description' => 'max:255',
                'checked_operations' => 'required',
            ],
            [
                'code.required' => "Le code est obligatoire.",
                'code.max' => "Le code ne doit pas dépasser 50 caractères.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'checked_operations.required' => "Le choix d'au moins une opération est obligatoire."
            ]
        );

        $existingPageOperationsOnCode = PageOperation::where('code', $request->code)->get();
        if (!empty($existingPageOperationsOnCode) && sizeof($existingPageOperationsOnCode) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingPageOperation' => $existingPageOperationsOnCode[0],
                'message' => "Le code " . $existingPageOperationsOnCode[0]->wording . " existe déjà"
            ], 200);
        }

        $existingPageOperationsOnWording = PageOperation::where('wording', $request->wording)->get();
        if (!empty($existingPageOperationsOnWording) && sizeof($existingPageOperationsOnWording) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingPageOperation' => $existingPageOperationsOnWording[0],
                'message' => "Le libellé " . $existingPageOperationsOnWording[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $pageOperation->code = strtoupper(str_replace(' ', '_', $this->role . $request->code));
            $pageOperation->wording = $request->wording;
            $pageOperation->description = $request->description;
            $pageOperation->save();

            Role::where('page_operation_id', $pageOperation->id)->delete();

            if (!empty($request->checked_operations) && sizeof($request->checked_operations) > 0) {
                foreach ($request->checked_operations as $key => $operation) {
                    $role = new Role();
                    $role->code = $pageOperation->code . $operation->wording;
                    $role->wording = $pageOperation->code . $operation->wording;
                    $role->page_operation_id = $pageOperation->id;
                    $role->operation_id = $operation->id;
                    $role->save();
                }
            }

            // $users = User::all();
            // $userRoles = explode();



            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'pageOperation' => $pageOperation,
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
        $pageOperation = PageOperation::findOrFail($id);
        try {
            $pageOperation->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'pageOperation' => $pageOperation,
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
