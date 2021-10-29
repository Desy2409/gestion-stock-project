<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductTransferDemandLine;
use App\Models\SalePoint;
use App\Models\TransferDemand;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class TransferDemandController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $salesPoints = SalePoint::orderBy('social_reason')->get();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $transfersDemands = TransferDemand::with('salePoint')->with('productsTransfersDemandsLines')->orderBy('date_of_demand', 'desc')->orderBy('request_reason')->get();
        return new JsonResponse([
            'datas' => ['transfersDemands' => $transfersDemands, 'salesPoints' => $salesPoints, 'products' => $products]
        ], 200);
    }

    public function store(Request $request)
    {
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'transmitter' => 'required',
                'receiver' => 'required',
                'request_reason' => 'required',
                'date_of_demand' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_deadline' => 'required|date|date_format:Y-m-d|after:date_of_demand',
                'date_of_processing' => 'date|date_format:Y-m-d|after:date_of_demand',
                'products' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'transmitter.required' => "Le point de vente source est obligatoire.",
                'receiver.required' => "Le point de vente destination est obligatoire.",
                'request_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_demand.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_demand.date' => "La date de la demande de transfert est invalide.",
                'date_of_demand.date_format' => "La date de la demande de transfert doit être sous le format : AAAA-MM-JJ.",
                'date_of_demand.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'delivery_deadline.required' => "La date limite de livraison est obligatoire.",
                'delivery_deadline.date' => "La date limite de livraison est invalide.",
                'delivery_deadline.date_format' => "La date limite de livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_deadline.after' => "La date limite de livraison ne peut être antérieur à la date de demande de transfert.",
                'products.required' => "Vous devez ajouter au moins un produit.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $transfersDemands = TransferDemand::all();
            $transferDemand = new TransferDemand();
            $transferDemand->code = $this->formateNPosition('DT', sizeof($transfersDemands) + 1, 8);
            $transferDemand->request_reason = $request->request_reason;
            $transferDemand->date_of_demand = $request->date_of_demand;
            $transferDemand->delivery_deadline = $request->delivery_deadline;
            $transferDemand->transmitter_id = $request->transmitter;
            $transferDemand->receiver_id = $request->receiver;
            $transferDemand->state = 'P';
            $transferDemand->save();

            if (!empty($request->products) && sizeof($request->products) > 0) {
                foreach ($request->products as $key => $product) {
                    $transferDemandLine = new ProductTransferDemandLine();
                    $transferDemandLine->quantity = $request->quantities[$key];
                    $transferDemandLine->unit_price = $request->unit_prices[$key];
                    $transferDemandLine->product_id = $product;
                    $transferDemandLine->transfer_demand_id = $transferDemand->id;
                    $transferDemandLine->save();
                }
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
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

    public function show($id)
    {
        $transferDemand = TransferDemand::with('salePoint')->with('productsTransfersDemandsLines')->findOrFail($id);
        $productsTransferDemands = $transferDemand ? $transferDemand->productsTransfersDemandsLines : null;

        return new JsonResponse([
            'transferDemand' => $transferDemand,
            'datas' => ['productsTransferDemands' => $productsTransferDemands]
        ], 200);
    }

    public function edit($id)
    {
        $transferDemand = TransferDemand::with('salePoint')->with('productsTransfersDemandsLines')->findOrFail($id);
        $products = Product::orderBy('wording')->get();
        $productsTransferDemands = $transferDemand ? $transferDemand->productsTransfersDemandsLines : null;

        return new JsonResponse([
            'transferDemand' => $transferDemand,
            'datas' => ['products' => $products, 'productsTransferDemands' => $productsTransferDemands]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $transferDemand = TransferDemand::findOrFail($id);
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'transmitter' => 'required',
                'receiver' => 'required',
                'request_reason' => 'required',
                'date_of_demand' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'delivery_deadline' => 'required|date|date_format:Y-m-d|after:date_of_demand',
                'date_of_processing' => 'date|date_format:Y-m-d|after:date_of_demand',
                'products' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'transmitter.required' => "Le point de vente source est obligatoire.",
                'receiver.required' => "Le point de vente destination est obligatoire.",
                'request_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_demand.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_demand.date' => "La date de la demande de transfert est invalide.",
                'date_of_demand.date_format' => "La date de la demande de transfert doit être sous le format : AAAA-MM-JJ.",
                'date_of_demand.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'delivery_deadline.required' => "La date limite de livraison est obligatoire.",
                'delivery_deadline.date' => "La date limite de livraison est invalide.",
                'delivery_deadline.date_format' => "La date limite de livraison doit être sous le format : AAAA-MM-JJ.",
                'delivery_deadline.after' => "La date limite de livraison ne peut être antérieur à la date de demande de transfert.",
                'products.required' => "Vous devez ajouter au moins un produit.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $transferDemand->request_reason = $request->request_reason;
            $transferDemand->date_of_demand = $request->date_of_demand;
            $transferDemand->delivery_deadline = $request->delivery_deadline;
            $transferDemand->transmitter_id = $request->transmitter;
            $transferDemand->receiver_id = $request->receiver;
            $transferDemand->save();

            ProductTransferDemandLine::where('transfer_demand_id', $transferDemand->id)->delete();

            if (!empty($request->products) && sizeof($request->products) > 0) {
                foreach ($request->products as $key => $product) {
                    $transferDemandLine = new ProductTransferDemandLine();
                    $transferDemandLine->quantity = $request->quantities[$key];
                    $transferDemandLine->unit_price = $request->unit_prices[$key];
                    $transferDemandLine->product_id = $product;
                    $transferDemandLine->transfer_demand_id = $transferDemand->id;
                    $transferDemandLine->save();
                }
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            dd($e);
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function destroy($id)
    {
        $transferDemand = TransferDemand::findOrFail($id);
        // $productsTransfersDemandsLines = $transferDemand ? $transferDemand->productsTransfersDemandsLines : null;
        try {
            $transferDemand->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
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

    public function validateTransferDemand($id)
    {
        $transferDemand = TransferDemand::findOrFail($id);
        try {
            $transferDemand->state = 'S';
            $transferDemand->date_of_processing = date('Y-m-d', strtotime(now()));
            $transferDemand->save();

            $success = true;
            $message = "Demande de transfert validée avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la validation de la demande de transfert.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function cancelTransferDemand($id)
    {
        $transferDemand = TransferDemand::findOrFail($id);
        try {
            $transferDemand->state = 'A';
            $transferDemand->date_of_processing = date('Y-m-d', strtotime(now()));
            $transferDemand->save();

            $success = true;
            $message = "Demande de transfert validée avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la validation de la demande de transfert.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
