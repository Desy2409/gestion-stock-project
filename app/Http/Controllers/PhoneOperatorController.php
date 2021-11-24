<?php

namespace App\Http\Controllers;

use App\Models\PhoneOperator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhoneOperatorController extends Controller
{
    public function index()
    {
        $phoneOperators = PhoneOperator::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['phoneOperators' => $phoneOperators]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:phone_operators|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cet opérateur téléphonique existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $phoneOperator = new PhoneOperator();
            $phoneOperator->code = Str::random(10);
            $phoneOperator->wording = $request->wording;
            $phoneOperator->description = $request->description;
            $phoneOperator->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'phoneOperator' => $phoneOperator,
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
        $phoneOperator = PhoneOperator::findOrFail($id);
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

        try {
            $phoneOperator->wording = $request->wording;
            $phoneOperator->description = $request->description;
            $phoneOperator->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'phoneOperator' => $phoneOperator,
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
        $phoneOperator = PhoneOperator::findOrFail($id);
        try {
            $phoneOperator->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'phoneOperator' => $phoneOperator,
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
