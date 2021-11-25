<?php

namespace App\Http\Controllers;

use App\Models\Compartment;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\Tank;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TankController extends Controller
{
    public function index()
    {
        $this->authorize('ROLE_TANK_READ', Tank::class);
        $tanks = Tank::orderBy('tank_registration')->get();
        $idOfProviderTypeCarriers = ProviderType::where('type', "Transporteur")->pluck('id')->toArray();
        $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();
        $compartments = Compartment::orderBy('reference')->orderBy('number')->get();
        return new JsonResponse([
            'datas' => ['tanks' => $tanks, 'carriers' => $carriers, 'compartments' => $compartments]
        ], 200);
    }

    // Enregistrement d'une nouvelle citerne
    public function store(Request $request)
    {
        $this->authorize('ROLE_TANK_CREATE', Tank::class);
        $this->validate(
            $request,
            [
                'provider'=>'required',
                'compartment'=>'required',
                'reference' => 'required|unique:tanks',
                'tank_registration' => 'required|unique:tanks',
            ],
            [
                'provider.required'=>"Le choix du fornisseur est obligatoire.",
                'compartment.required'=>"Le choix du compartiment est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                'tank_registration.unique' => "Cette immatriculation de citerne existe déjà.",
            ]
        );

        try {
            $tank = new Tank();
            $tank->reference = $request->reference;
            $tank->tank_registration = $request->tank_registration;
            $tank->compartment_id = $request->compartment;
            $tank->provider_id = $request->provider;
            $tank->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'tank' => $tank,
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

    // Mise à jour d'une citerne
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TANK_UPDATE', Tank::class);
        $tank = Tank::findOrFail($id);
        $this->validate(
            $request,
            [
                'provider'=>'required',
                'compartment'=>'required',
                'reference' => 'required',
                'tank_registration' => 'required',
            ],
            [
                'provider.required'=>"Le choix du fornisseur est obligatoire.",
                'compartment.required'=>"Le choix du compartiment est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
            ]
        );

        $existingTanks = Tank::where('reference', $request->reference)->where('tank_registration', $request->tank_registration)->where('tank_registration', $request->tank_registration)->get();
        if (!empty($existingTanks) && sizeof($existingTanks) > 1) {
            $success = false;
            return new JsonResponse([
                'existingTank' => $existingTanks[0],
                'success' => $success,
                'message' => "Cette citerne existe déjà."
            ], 400);
        }

        try {
            $tank->reference = $request->reference;
            $tank->tank_registration = $request->tank_registration;
            $tank->compartment_id = $request->compartment;
            $tank->provider_id = $request->provider;
            $tank->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'tank' => $tank,
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

    // Suppression d'une citerne
    public function destroy($id)
    {
        $this->authorize('ROLE_TANK_DELETE', Tank::class);
        $tank = Tank::findOrFail($id);
        try {
            $tank->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'tank' => $tank,
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
        $this->authorize('ROLE_TANK_READ', Tank::class);
        $tank = Tank::findOrFail($id);
        return new JsonResponse([
            'tank' => $tank
        ], 200);
    }
}
