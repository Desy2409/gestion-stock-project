<?php

namespace App\Http\Controllers;

use App\Models\Unity;
use App\Repositories\UnityRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UnityController extends Controller
{
    public $unityRepository;

    public function __construct(UnityRepository $unityRepository)
    {
        $this->unityRepository = $unityRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_UNITY_READ', Truck::class);
        $unities = Unity::orderBy('created_at','desc')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['unities' => $unities]
        ], 200);
    }

    // Enregistrement d'une nouvelle unité
    public function store(Request $request)
    {
        $this->authorize('ROLE_UNITY_CREATE', Truck::class);

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
                $unity = new Unity();
                $unity->code = Str::random(10);
                $unity->wording = $request->wording;
                $unity->symbol = $request->symbol;
                $unity->description = $request->description;
                $unity->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'unity' => $unity,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    // Mise à jour d'une unité
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_UNITY_UPDATE', Truck::class);
        $unity = Unity::findOrFail($id);

        $existingUnitiesOnSymbol = Unity::where('symbol', $request->symbol)->get();
        if (!empty($existingUnitiesOnSymbol) && sizeof($existingUnitiesOnSymbol) > 1) {
            $success = false;
            return new JsonResponse([
                'existingUnity' => $existingUnitiesOnSymbol[0],
                'success' => $success,
                'message' => "Ce symbole a déjà été attribué."
            ], 400);
        }

        $existingUnitiesOnWording = Unity::where('wording', $request->wording)->get();
        if (!empty($existingUnitiesOnWording) && sizeof($existingUnitiesOnWording) > 1) {
            $success = false;
            return new JsonResponse([
                'existingUnity' => $existingUnitiesOnWording[0],
                'success' => $success,
                'message' => "Cette unité existe déjà."
            ], 400);
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
                $unity->wording = $request->wording;
                $unity->symbol = $request->symbol;
                $unity->description = $request->description;
                $unity->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'unity' => $unity,
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

    // Suppression d'une unité
    public function destroy($id)
    {
        $this->authorize('ROLE_UNITY_DELETE', Truck::class);
        $unity = Unity::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (
                empty($unity->productOrders) || sizeof($unity->productOrders) == 0 &&
                empty($unity->productPurchases) || sizeof($unity->productPurchases) == 0 &&
                empty($unity->productDeliveryNotes) || sizeof($unity->productDeliveryNotes) == 0 &&
                empty($unity->productPurchaseOrders) || sizeof($unity->productPurchaseOrders) == 0 &&
                empty($unity->productSales) || sizeof($unity->productSales) == 0 &&
                empty($unity->productClientDeliveryNotes) || sizeof($unity->productClientDeliveryNotes) == 0
            ) {
                // dd('delete');
                $unity->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette unité ne peut être supprimée car elle a servi dans des traitements.";
            }

            return new JsonResponse([
                'unity' => $unity,
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
        $this->authorize('ROLE_UNITY_READ', Truck::class);
        $unity = Unity::findOrFail($id);
        return new JsonResponse([
            'unity' => $unity
        ], 200);
    }

    public function unityReports(Request $request)
    {
        $this->authorize('ROLE_UNITY_PRINT', Truck::class);
        try {
            $unities = $this->unityRepository->unityReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['unities' => $unities]], 200);
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
                    'wording' => 'required|unique:unities|max:150',
                    'symbol' => 'required|unique:unities',
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
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'wording' => 'required|max:150',
                    'symbol' => 'required',
                    'description' => 'max:255',
                ],
                [
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'symbol.required' => "Le symbole est obligatoire.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
    }
}
