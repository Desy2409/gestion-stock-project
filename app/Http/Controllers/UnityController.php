<?php

namespace App\Http\Controllers;

use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UnityController extends Controller
{
    public function index()
    {
        $unities = Unity::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['unities' => $unities]
        ], 200);
    }

    // Enregistrement d'une nouvelle unité
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:unities|max:150',
                'symbol'=>'required|unique:unities',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette unité existe déjà.",
                'symbol.required' => "Le symbole est obligatoire.",
                'symbol.unique' => "Ce symbole a déjà été attribué.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $unity = new Unity();
            $unity->code = Str::random(10);
            $unity->wording = $request->wording;
            $unity->symbol = $request->symbol;
            $unity->description = $request->description;
            $unity->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'unity' => $unity,
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

    // Mise à jour d'une unité
    public function update(Request $request, $id)
    {

        $unity = Unity::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:150',
                'symbol'=>'required',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'symbol.required' => "Le symbole est obligatoire.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        $existingUnitiesOnSymbol = Unity::where('reference', $request->reference)->where('truck_registration', $request->truck_registration)->get();
        if (!empty($existingUnitiesOnSymbol) && sizeof($existingUnitiesOnSymbol) > 1) {
            $success = false;
            return new JsonResponse([
                'existingUnity' => $existingUnitiesOnSymbol[0],
                'success' => $success,
                'message' => "Ce symbole a déjà été attribué."
            ], 400);
        }

        $existingUnitiesOnWording = Unity::where('reference', $request->reference)->where('truck_registration', $request->truck_registration)->get();
        if (!empty($existingUnitiesOnWording) && sizeof($existingUnitiesOnWording) > 1) {
            $success = false;
            return new JsonResponse([
                'existingUnity' => $existingUnitiesOnWording[0],
                'success' => $success,
                'message' => "Cette unité existe déjà."
            ], 400);
        }

        try {
            $unity->wording = $request->wording;
            $unity->symbol = $request->symbol;
            $unity->description = $request->description;
            $unity->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'unity' => $unity,
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

    // Suppression d'une unité
    public function destroy($id)
    {
        $unity = Unity::findOrFail($id);
        try {
            $unity->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'unity' => $unity,
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
        $unity = Unity::findOrFail($id);
        return new JsonResponse([
            'unity' => $unity
        ], 200);
    }
}
