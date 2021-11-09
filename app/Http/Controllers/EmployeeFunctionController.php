<?php

namespace App\Http\Controllers;

use App\Models\EmployeeFunction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmployeeFunctionController extends Controller
{
    public function index()
    {
        $employeeFunctions = EmployeeFunction::orderBy('wording')->get();
        return new JsonResponse([
            'datas'=>['employeeFunctions'=>$employeeFunctions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:employee_functions|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette fonction existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $employeeFunction = new EmployeeFunction();
            $employeeFunction->code = Str::random(10);
            $employeeFunction->wording = $request->wording;
            $employeeFunction->description = $request->description;
            $employeeFunction->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'employeeFunction' => $employeeFunction,
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
        $employeeFunction = EmployeeFunction::findOrFail($id);
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
        
        $existingEmployeeFunctions = EmployeeFunction::where('wording', $request->wording)->get();
        if (!empty($existingEmployeeFunctions) && sizeof($existingEmployeeFunctions) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingEmployeeFunction' => $existingEmployeeFunctions[0],
                'message' => "Le type " . $existingEmployeeFunctions[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $employeeFunction->wording = $request->wording;
            $employeeFunction->description = $request->description;
            $employeeFunction->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'employeeFunction' => $employeeFunction,
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
        $employeeFunction = EmployeeFunction::findOrFail($id);
        try {
            $employeeFunction->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'employeeFunction' => $employeeFunction,
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
