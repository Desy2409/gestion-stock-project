<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Truck;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index()
    {
        $trucks = Truck::orderBy('truck_registration')->get();
        $providers = Provider::with(['person.addresses'])->get();
        return new JsonResponse([
            'datas' => ['trucks' => $trucks, 'providers' => $providers]
        ], 200);
    }

    // Enregistrement d'une nouveau camion
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'reference' => 'required|unique:trucks',
                'truck_registration' => 'required|unique:trucks',
                'tank_registration' => 'required|unique:trucks',
                'number_of_compartments' => 'required',
                'capacity' => 'required',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'truck_registration.required' => "L'immatriculation du camion est obligatoire.",
                'truck_registration.unique' => "Cette immatriculation de camion existe déjà.",
                'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                'tank_registration.unique' => "Cette immatriculation de citerne existe déjà.",
                'number_of_compartments.required' => "Le nombre de compartiments est obligatoire.",
                'capacity.required' => "La contenance est obligatoire.",
            ]
        );

        try {
            $truck = new Truck();
            $truck->reference = $request->reference;
            $truck->truck_registration = $request->truck_registration;
            $truck->tank_registration = $request->tank_registration;
            $truck->number_of_compartments = $request->number_of_compartments;
            $truck->capacity = $request->capacity;
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

    // Mise à jour d'une camion
    public function update(Request $request, $id)
    {

        $truck = Truck::findOrFail($id);
        $this->validate(
            $request,
            [
                'reference' => 'required',
                'truck_registration' => 'required',
                'tank_registration' => 'required',
                'number_of_compartments' => 'required',
                'capacity' => 'required',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'truck_registration.required' => "L'immatriculation du camion est obligatoire.",
                'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                'number_of_compartments.required' => "Le nombre de compartiments est obligatoire.",
                'capacity.required' => "La contenance est obligatoire.",
            ]
        );

        $existingTrucks = Truck::where('reference', $request->reference)->where('truck_registration', $request->truck_registration)->where('tank_registration', $request->tank_registration)->get();
        if (!empty($existingTrucks) && sizeof($existingTrucks) > 1) {
            $success = false;
            return new JsonResponse([
                'existingTruck' => $existingTrucks[0],
                'success' => $success,
                'message' => "Ce camion existe déjà."
            ], 400);
        }
        // $existingTrucksOnTruckRegistration = Truck::where()->get();
        // if (!empty($existingTrucksOnTruckRegistration) && sizeof($existingTrucksOnTruckRegistration) > 1) {
        //     $success = false;
        //     return new JsonResponse([
        //         'existingTruckOnTrucKRegistration' => $existingTrucksOnTruckRegistration[0],
        //         'success' => $success,
        //         'message' => "L'immatriculation de comion' " . $existingTrucksOnTruckRegistration[0]->truck_registration . " existe déjà."
        //     ], 400);
        // }
        // $existingTrucksOnTankRegistration = Truck::where('tank_registration', $request->tank_registration)->get();
        // if (!empty($existingTrucksOnTankRegistration) && sizeof($existingTrucksOnTankRegistration) > 1) {
        //     $success = false;
        //     return new JsonResponse([
        //         'existingTruckOnTankRegistration' => $existingTrucksOnTankRegistration[0],
        //         'success' => $success,
        //         'message' => "L'immatriculation de citerne " . $existingTrucksOnTankRegistration[0]->tank_registration . " existe déjà."
        //     ], 400);
        // }
        try {
            $truck->reference = $request->reference;
            $truck->truck_registration = $request->truck_registration;
            $truck->tank_registration = $request->tank_registration;
            $truck->number_of_compartments = $request->number_of_compartments;
            $truck->capacity = $request->capacity;
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

    // Suppression d'une camion
    public function destroy($id)
    {
        $truck = Truck::findOrFail($id);
        try {
            $truck->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
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
        $truck = Truck::findOrFail($id);
        return new JsonResponse([
            'truck' => $truck
        ], 200);
    }
}
