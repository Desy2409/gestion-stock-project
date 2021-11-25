<?php

namespace App\Http\Controllers;

use App\Models\Compartment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompartmentController extends Controller
{
    public function index()
    {
        $this->authorize('ROLE_COMPARTMENT_READ', Compartment::class);
        $compartments = Compartment::orderBy('reference')->get();
        return new JsonResponse([
            'datas' => ['compartments' => $compartments]
        ], 200);
    }

    // Enregistrement d'un nouveau compartiment
    public function store(Request $request)
    {
        $this->authorize('ROLE_COMPARTMENT_CREATE', Compartment::class);
        $this->validate(
            $request,
            [
                'reference' => 'required|unique:compartments',
                'number' => 'required',
                'capacity' => 'required',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'number.required' => "Le numéro du compartiment est obligatoire.",
                'capacity.required' => "La capacité est obligatoire.",
            ]
        );

        try {
            $compartment = new Compartment();
            $compartment->reference = $request->reference;
            $compartment->number = $request->number;
            $compartment->capacity = $request->capacity;
            $compartment->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'compartment' => $compartment,
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

    // Mise à jour d'un compartiment
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_COMPARTMENT_UPDATE', Compartment::class);
        $compartment = Compartment::findOrFail($id);
        $this->validate(
            $request,
            [
                'reference' => 'required',
                'number' => 'required',
                'capacity' => 'required',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'number.required' => "Le numéro du compartiment est obligatoire.",
                'capacity.required' => "La capacité est obligatoire.",
            ]
        );

        $existingCompartments = Compartment::where('reference', $request->reference)->get();
        if (!empty($existingCompartments) && sizeof($existingCompartments) > 1) {
            $success = false;
            return new JsonResponse([
                'existingCompartment' => $existingCompartments[0],
                'success' => $success,
                'message' => "Ce camion existe déjà."
            ], 400);
        }

        try {
            $compartment->reference = $request->reference;
            $compartment->number = $request->number;
            $compartment->capacity = $request->capacity;
            $compartment->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'compartment' => $compartment,
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

    // Suppression d'un compartiment
    public function destroy($id)
    {
        $this->authorize('ROLE_COMPARTMENT_DELETE', Compartment::class);
        $compartment = Compartment::findOrFail($id);
        try {
            $compartment->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'compartment' => $compartment,
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
        $this->authorize('ROLE_COMPARTMENT_READ', Compartment::class);
        $compartment = Compartment::findOrFail($id);
        return new JsonResponse([
            'compartment' => $compartment
        ], 200);
    }
}
