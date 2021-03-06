<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\ProductTourn;
use App\Models\RemovalOrder;
use App\Models\Tank;
use App\Models\Tourn;
use App\Models\TournRegister;
use App\Models\Truck;
use App\Repositories\TournRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $tourns = Tourn::orderBy('created_at','desc')->orderBy('reference')->get();
        // $removalOrders = RemovalOrder::orderBy('voucher_date')->orderBy('reference')->get();
        $tanks = Tank::all();
        $trucks = Truck::all();

        $lastTournRegister = TournRegister::latest()->first();

        $tournRegister = new TournRegister();
        if ($lastTournRegister) {
            $tournRegister->code = $this->formateNPosition(Tourn::class, $lastTournRegister->id + 1);
        } else {
            $tournRegister->code = $this->formateNPosition(Tourn::class, 1);
        }
        $tournRegister->save();

        return new JsonResponse([
            'datas' => ['tourns' => $tourns, 'tanks' => $tanks, 'trucks' => $trucks] //, 'removalOrders' => $removalOrders
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_TOURN_READ', Tourn::class);
        $lastTournRegister = TournRegister::latest()->first();
        if ($lastTournRegister) {
            $code = $this->formateNPosition(Tourn::class, $lastTournRegister->id + 1);
        } else {
            $code = $this->formateNPosition(Tourn::class, 1);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    // Enregistrement d'une nouvelle tournée
    public function store(Request $request)
    {
        $this->authorize('ROLE_TOURN_CREATE', Tourn::class);

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
                $lastTourn = Tourn::latest()->first();

                $tourn = new Tourn();
                if ($lastTourn) {
                    $tourn->code = $this->formateNPosition(Tourn::class, $lastTourn->id + 1);
                } else {
                    $tourn->code = $this->formateNPosition(Tourn::class, 1);
                }
                $clientDeliveryNotes = [];
                array_push($clientDeliveryNotes, $request->client_delivery_note);

                $tourn->reference = $request->reference_tourn;
                $tourn->date_of_edition = $request->date_of_edition;
                $tourn->removal_order_id = $request->removal_order;
                $tourn->truck_id = $request->truck;
                $tourn->tank_id = $request->tank;
                $tourn->destination_id = $request->destination;
                $tourn->client_delivery_notes = $clientDeliveryNotes;
                $tourn->save();

                $productsTourns = [];
                foreach ($request->productTourns as $key => $productTournLine) {
                    // dd($productTournLine);
                    $productTourn = new ProductTourn();
                    $productTourn->quantity = $productTournLine['quantity'];
                    $productTourn->product_id = $productTournLine['product_id'];
                    $productTourn->tourn_id = $tourn->id;
                    $productTourn->unity_id = $productTournLine['unity_id'];
                    $productTourn->save();

                    array_push($productsTourns, $productTourn);
                }

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'tourn' => $tourn,
                    'success' => true,
                    'message' => $message,
                    'datas' => ['productsTourns' => $productsTourns],
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

    // Mise à jour d'une tournée
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TOURN_UPDATE', Tourn::class);
        $tourn = Tourn::findOrFail($id);

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
            $validation = $this->validator('update', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $tourn->reference = $request->reference;
                $tourn->removal_order_id = $request->removal_order;
                $tourn->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'tourn' => $tourn,
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
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
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

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'removal_order' => 'required',
                    'reference' => 'required|unique:tourns',
                ],
                [
                    'removal_order.required' => "Le choix du bon à enlever est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette référence existe déjà.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'removal_order' => 'required',
                    'reference' => 'required',
                ],
                [
                    'removal_order.required' => "Le choix du bon à enlever est obligatoire.",
                    'reference.required' => "La référence est obligatoire.",
                ]
            );
        }
    }
}
