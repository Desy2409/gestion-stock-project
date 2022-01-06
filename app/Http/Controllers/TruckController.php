<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\Truck;
use App\Repositories\TruckRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public $truckRepository;

    public function __construct(TruckRepository $truckRepository)
    {
        $this->truckRepository = $truckRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_TRUCK_READ', Truck::class);
        $trucks = Truck::with('provider')->orderBy('truck_registration')->get();

        // $idOfProviderTypeCarriers = ProviderType::where('type', "Transporteur")->pluck('id')->toArray();
        // $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();

        return new JsonResponse([
            'datas' => ['trucks' => $trucks]
        ], 200);
    }

    // Enregistrement d'un nouveau tracteur
    public function store(Request $request)
    {
        $this->authorize('ROLE_TRUCK_CREATE', Truck::class);
        $this->validate(
            $request,
            [
                'provider'=>'required',
                'reference' => 'required|unique:trucks',
                'truck_registration' => 'required|unique:trucks',
            ],
            [
                'provider.required'=>"Le choix du fornisseur est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'truck_registration.required' => "L'immatriculation du tracteur est obligatoire.",
                'truck_registration.unique' => "Cette immatriculation de tracteur existe déjà.",
            ]
        );

        try {
            $truck = new Truck();
            $truck->reference = $request->reference;
            $truck->truck_registration = $request->truck_registration;
            $truck->provider_id = $request->provider;
            $truck->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'truck' => $truck,
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

    // Mise à jour d'un tracteur
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TRUCK_UPDATE', Truck::class);
        $truck = Truck::findOrFail($id);
        $this->validate(
            $request,
            [
                'provider'=>'required',
                'reference' => 'required',
                'truck_registration' => 'required',
            ],
            [
                'provider.required'=>"Le choix du fornisseur est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'truck_registration.required' => "L'immatriculation du tracteur est obligatoire.",
            ]
        );

        $existingTrucks = Truck::where('reference', $request->reference)->where('truck_registration', $request->truck_registration)->get();
        if (!empty($existingTrucks) && sizeof($existingTrucks) > 1) {
            $success = false;
            return new JsonResponse([
                'existingTruck' => $existingTrucks[0],
                'success' => $success,
                'message' => "Ce tracteur existe déjà."
            ], 400);
        }

        try {
            $truck->reference = $request->reference;
            $truck->truck_registration = $request->truck_registration;
            $truck->provider_id = $request->provider;
            $truck->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'truck' => $truck,
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

    // Suppression d'un tracteur
    public function destroy($id)
    {
        $this->authorize('ROLE_TRUCK_DELETE', Truck::class);
        $truck = Truck::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (
                empty($truck->tourns) || sizeof($truck->tourns) == 0 &&
                empty($truck->tankTrucks) || sizeof($truck->tankTrucks) == 0 
            ) {
                // dd('delete');
                $truck->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce camion ne peut être supprimé car il a servi dans des traitements.";
            }
            
            return new JsonResponse([
                'truck' => $truck,
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
        $this->authorize('ROLE_TRUCK_READ', Truck::class);
        $truck = Truck::findOrFail($id);
        return new JsonResponse([
            'truck' => $truck
        ], 200);
    }

    public function truckReports(Request $request)
    {
        $this->authorize('ROLE_TRUCK_PRINT', Truck::class);
        try {
            // dd($request->selected_default_fields);
            $trucks = $this->truckRepository->truckReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['trucks' => $trucks]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
