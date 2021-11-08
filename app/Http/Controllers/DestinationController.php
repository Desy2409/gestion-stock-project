<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['destinations' => $destinations]
        ], 200);
    }

    // Enregistrement d'une nouvelle destination
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'reference' => 'required|unique:destinations',
                'wording' => 'required|unique:destinations|max:150',
                'description' => 'max:255',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette réference a déjà été attribuée déjà.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette destination existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $destination = new Destination();
            $destination->reference = $request->reference;
            $destination->wording = $request->wording;
            $destination->description = $request->description;
            $destination->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'destination' => $destination,
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
            ], 200);
        }
    }

    // Mise à jour d'une destination
    public function update(Request $request, $id)
    {

        $destination = Destination::findOrFail($id);
        $this->validate(
            $request,
            [
                'reference' => 'required',
                'wording' => 'required|max:150',
                'description' => 'max:255',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $destination->reference = $request->reference;
            $destination->wording = $request->wording;
            $destination->description = $request->description;
            $destination->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'destination' => $destination,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    // Suppression d'une destination
    public function destroy($id)
    {
        $destination = Destination::findOrFail($id);
        try {
            $destination->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'destination' => $destination,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $destination = Destination::findOrFail($id);
        return new JsonResponse([
            'destination' => $destination
        ], 200);
    }
}
