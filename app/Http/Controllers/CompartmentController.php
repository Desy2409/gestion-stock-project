<?php

namespace App\Http\Controllers;

use App\Models\Compartment;
use App\Models\Tank;
use App\Repositories\CompartmentRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompartmentController extends Controller
{
    public $compartmentRepository;

    public function __construct(CompartmentRepository $compartmentRepository)
    {
        $this->compartmentRepository = $compartmentRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_COMPARTMENT_READ', Compartment::class);
        $compartments = Compartment::with('tank')->orderBy('created_at', 'desc')->get();
        $tanks = Tank::orderBy('tank_registration')->get();

        return new JsonResponse([
            'datas' => ['compartments' => $compartments, 'tanks' => $tanks]
        ], 200);
    }

    // Enregistrement d'un nouveau compartiment
    public function store(Request $request)
    {
        $this->authorize('ROLE_COMPARTMENT_CREATE', Compartment::class);
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
                $compartment = new Compartment();
            $compartment->reference = $request->reference;
            $compartment->number = $request->number;
            $compartment->capacity = $request->capacity;
            $compartment->tank_id = $request->tank ? $request->tank : null;
            $compartment->save();

            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'compartment' => $compartment,
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

    public function associateCompartmentsToTank(Request $request)
    {
        if (empty($request->compartments) && sizeof($request->compartments) == 0) {
            $message = "Vous n'avez sélectionné aucun compartiment.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        } else {
            try {
                foreach ($request->compartments as $key => $compartment) {
                    $compartment = Compartment::findOrFail($compartment);
                    $compartment->tank_id = $request->tank;
                    $compartment->save();
                }

                $message = "Compartiments associés à la citerne avec succès.";
                return new JsonResponse([
                    'success' => true,
                    'message' => $message,
                ], 200);
            } catch (Exception $e) {
                // dd($e);
                $message = "Erreur survenue lors de l'association des compartiments à la citerne.";
                return new JsonResponse([
                    'success' => false,
                    'message' => $message,
                ], 200);
            }
        }
    }

    // Mise à jour d'un compartiment
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_COMPARTMENT_UPDATE', Compartment::class);
        $compartment = Compartment::findOrFail($id);

        $existingCompartments = Compartment::where('reference', $request->reference)->get();
        if (!empty($existingCompartments) && sizeof($existingCompartments) > 1) {
            $success = false;
            return new JsonResponse([
                'existingCompartment' => $existingCompartments[0],
                'success' => $success,
                'message' => "Ce camion existe déjà."
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
                $compartment->reference = $request->reference;
                $compartment->number = $request->number;
                $compartment->capacity = $request->capacity;
                $compartment->tank_id = $request->tank ? $request->tank : null;
                $compartment->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'compartment' => $compartment,
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

    // Suppression d'un compartiment
    public function destroy($id)
    {
        $this->authorize('ROLE_COMPARTMENT_DELETE', Compartment::class);
        $compartment = Compartment::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($compartment->tanks) || sizeof($compartment->tanks) == 0) {
                // dd('delete');
                $compartment->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce compartiment ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'compartment' => $compartment,
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

    public function show($id)
    {
        $this->authorize('ROLE_COMPARTMENT_READ', Compartment::class);
        $compartment = Compartment::findOrFail($id);
        return new JsonResponse([
            'compartment' => $compartment
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_COMPARTMENT_READ', Compartment::class);
        $compartment = Compartment::findOrFail($id);
        return new JsonResponse([
            'compartment' => $compartment
        ], 200);
    }

    public function compartmentReports(Request $request)
    {
        $this->authorize('ROLE_COMPARTMENT_PRINT', Compartment::class);
        try {
            $compartments = $this->compartmentRepository->oneJoinReport(SubCategory::class, 'compartments', 'tanks', 'cmp', 'tank', 'tank_id', $request->child_selected_fields, $request->parent_selected_fields);
            return new JsonResponse(['datas' => ['compartments' => $compartments]], 200);
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
                    // 'tank' => 'required',
                    'reference' => 'required|unique:compartments',
                    'number' => 'required',
                    'capacity' => 'required',
                ],
                [
                    // 'tank.required' => "La citerne est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette référence existe déjà.",
                    'number.required' => "Le numéro du compartiment est obligatoire.",
                    'capacity.required' => "La capacité est obligatoire.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
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
        }
    }
}
