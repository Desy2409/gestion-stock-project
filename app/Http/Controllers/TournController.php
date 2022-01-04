<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\RemovalOrder;
use App\Models\Tourn;
use App\Models\TournRegister;
use App\Repositories\TournRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TournController extends Controller
{
    use UtilityTrait;

    public $tournRepository;

    public function __construct(TournRepository $tournRepository)
    {
        $this->tournRepository = $tournRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_TOURN_READ', Tourn::class);
        $tourns = Tourn::orderBy('reference')->get();
        // $removalOrders = RemovalOrder::orderBy('voucher_date')->orderBy('reference')->get();

        $lastTournRegister = TournRegister::latest()->first();

        $tournRegister = new TournRegister();
        if ($lastTournRegister) {
            $tournRegister->code = $this->formateNPosition('TO', $lastTournRegister->id + 1, 8);
        } else {
            $tournRegister->code = $this->formateNPosition('TO', 1, 8);
        }
        $tournRegister->save();

        return new JsonResponse([
            'datas' => ['tourns' => $tourns]//, 'removalOrders' => $removalOrders
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_TOURN_READ', Tourn::class);
        $lastTournRegister = TournRegister::latest()->first();
        if ($lastTournRegister) {
            $code = $this->formateNPosition('TO', $lastTournRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('TO', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    // Enregistrement d'une nouvelle tournée
    public function store(Request $request)
    {
        $this->authorize('ROLE_TOURN_CREATE', Tourn::class);
        $this->validate(
            $request,
            [
                'good_to_remove' => 'required',
                'reference' => 'required|unique:tourns',
            ],
            [
                'good_to_remove.required' => "Le choix du bon à enlever est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
            ]
        );

        try {
            $lastTourn = Tourn::latest()->first();

            $tourn = new Tourn();
            if ($lastTourn) {
                $tourn->code = $this->formateNPosition('TO', $lastTourn->id + 1, 8);
            } else {
                $tourn->code = $this->formateNPosition('TO', 1, 8);
            }
            $tourn->reference = $request->reference;
            $tourn->good_to_remove_id = $request->good_to_remove;
            $tourn->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'tourn' => $tourn,
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

    // Mise à jour d'une tournée
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TOURN_UPDATE', Tourn::class);
        $tourn = Tourn::findOrFail($id);
        $this->validate(
            $request,
            [
                'good_to_remove' => 'required',
                'reference' => 'required',
            ],
            [
                'good_to_remove.required' => "Le choix du bon à enlever est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
            ]
        );

        $existingTourns = Tourn::where('reference', $request->reference)->get();
        if (!empty($existingTourns) && sizeof($existingTourns) > 1) {
            $success = false;
            return new JsonResponse([
                'existingTourn' => $existingTourns[0],
                'success' => $success,
                'message' => "Cette tournée existe déjà."
            ], 400);
        }

        try {
            $tourn->reference = $request->reference;
            $tourn->good_to_remove_id = $request->good_to_remove;
            $tourn->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'tourn' => $tourn,
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

    // Suppression d'une tournée
    public function destroy($id)
    {
        $this->authorize('ROLE_TOURN_DELETE', Tourn::class);
        $tourn = Tourn::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($tourn->clientDeliveryNotes) || sizeof($tourn->clientDeliveryNotes) == 0) {
                // dd('delete');
                $tourn->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette tournée ne peut être supprimée car elle a servi dans des traitements.";
            }
            
            return new JsonResponse([
                'tourn' => $tourn,
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
        $this->authorize('ROLE_TOURN_READ', Tourn::class);
        $tourn = Tourn::findOrFail($id);
        return new JsonResponse([
            'tourn' => $tourn
        ], 200);
    }

    public function tournReports(Request $request)
    {
        $this->authorize('ROLE_TOURN_PRINT', Tourn::class);
        try {
            $tourns = $this->tournRepository->tournReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['tourns' => $tourns]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
