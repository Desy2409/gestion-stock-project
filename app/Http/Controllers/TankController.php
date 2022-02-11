<?php

namespace App\Http\Controllers;

use App\Models\Compartment;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\Tank;
use App\Repositories\TankRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TankController extends Controller
{
    public $tankRepository;

    public function __construct(TankRepository $tankRepository)
    {
        $this->tankRepository = $tankRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_TANK_READ', Tank::class);
        $tanks = Tank::with('provider')->orderBy('tank_registration')->get();
        $idOfProviderTypeCarriers = ProviderType::where('type', "Transport")->pluck('id')->toArray();
        $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();
        // $compartments = Compartment::orderBy('reference')->orderBy('number')->get();

        return new JsonResponse([
            'datas' => ['tanks' => $tanks, 'carriers' => $carriers]
        ], 200);
    }

    // Enregistrement d'une nouvelle citerne
    public function store(Request $request)
    {
        $this->authorize('ROLE_TANK_CREATE', Tank::class);

        try {
            $validation = $this->validator('store', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $tank = new Tank();
                $tank->reference = $request->reference;
                $tank->tank_registration = $request->tank_registration;
                $tank->capacity = $request->capacity;
                $tank->provider_id = $request->provider;
                $tank->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'tank' => $tank,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_TANK_READ', Tank::class);
        $tank = Tank::with('compartments')->findOrFail($id);

        return new JsonResponse([
            'tank' => $tank
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_TANK_READ', Tank::class);
        $tank = Tank::with('compartments')->findOrFail($id);

        return new JsonResponse([
            'tank' => $tank
        ], 200);
    }

    // Mise à jour d'une citerne
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TANK_UPDATE', Tank::class);
        $tank = Tank::findOrFail($id);

        $existingTanks = Tank::where('reference', $request->reference)->where('tank_registration', $request->tank_registration)->get();
        if (!empty($existingTanks) && sizeof($existingTanks) > 1) {
            return new JsonResponse([
                'existingTank' => $existingTanks[0],
                'success' => false,
                'message' => "Cette citerne existe déjà."
            ], 200);
        }

        try {
            $validation = $this->validator('update', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $tank->reference = $request->reference;
                $tank->tank_registration = $request->tank_registration;
                $tank->capacity = $request->capacity;
                $tank->provider_id = $request->provider;
                $tank->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'tank' => $tank,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    // Suppression d'une citerne
    public function destroy($id)
    {
        $this->authorize('ROLE_TANK_DELETE', Tank::class);
        $tank = Tank::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (
                empty($tank->tourns) || sizeof($tank->tourns) == 0 &&
                empty($tank->compartments) || sizeof($tank->compartments) == 0 &&
                empty($tank->tankTrucks) || sizeof($tank->tankTrucks) == 0
            ) {
                // dd('delete');
                $tank->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette citerne ne peut être supprimée car elle a servi dans des traitements.";
            }

            return new JsonResponse([
                'tank' => $tank,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function tankReports(Request $request)
    {
        $this->authorize('ROLE_TANK_PRINT', Tank::class);
        try {
            $tanks = $this->tankRepository->tankReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['tanks' => $tanks]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'provider' => 'required',
                    // 'capacity'=>'required',
                    'reference' => 'required|unique:tanks',
                    'tank_registration' => 'required|unique:tanks',
                ],
                [
                    'provider.required' => "Le choix du fornisseur est obligatoire.",
                    // 'capacity.required'=>"Le choix du compartiment est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette référence existe déjà.",
                    'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                    'tank_registration.unique' => "Cette immatriculation de citerne existe déjà.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'provider' => 'required',
                    // 'compartment'=>'required',
                    'reference' => 'required',
                    'tank_registration' => 'required',
                ],
                [
                    'provider.required' => "Le choix du fornisseur est obligatoire.",
                    // 'compartment.required'=>"Le choix du compartiment est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                    'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                    'tank_registration.required' => "L'immatriculation de la citerne est obligatoire.",
                ]
            );
        }
    }
}
