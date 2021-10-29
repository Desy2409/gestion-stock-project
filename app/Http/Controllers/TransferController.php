<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductTransferLine;
use App\Models\SalePoint;
use App\Models\Transfer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $salesPoints = SalePoint::with('institution')->orderBy('social_reason')->get();
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $transfers = Transfer::with('productsTransfersLines')->orderBy('date_of_transfer', 'desc')->orderBy('transfer_reason')->get();
        return new JsonResponse([
            'datas' => ['transfers' => $transfers, 'salesPoints' => $salesPoints, 'products' => $products]
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
                'transfer_reason' => 'required',
                'date_of_transfer' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'date_of_receipt' => 'date|date_format:Y-m-d|after:date_of_transfer',
                'products' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'transmitter.required' => "Le point de vente source est obligatoire.",
                'receiver.required' => "Le point de vente destination est obligatoire.",
                'transfer_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_transfer.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_transfer.date' => "La date de la demande de transfert est invalide.",
                'date_of_transfer.date_format' => "La date de la demande de transfert doit être sous le format : AAAA-MM-JJ.",
                'date_of_transfer.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'date_of_receipt.date' => "La date limite de livraison est invalide.",
                'date_of_receipt.date_format' => "La date limite de livraison doit être sous le format : AAAA-MM-JJ.",
                'date_of_receipt.after' => "La date limite de livraison ne peut être antérieur à la date de transfert.",
                'products.required' => "Vous devez ajouter au moins un produit.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $transfers = Transfer::all();
            $transfer = new Transfer();
            $transfer->code = $this->formateNPosition('TF', sizeof($transfers) + 1, 8);
            $transfer->transfer_reason = $request->transfer_reason;
            $transfer->date_of_transfer = $request->date_of_transfer;
            $transfer->date_of_receipt = $request->date_of_receipt;
            $transfer->transmitter_id = $request->transmitter;
            $transfer->receiver_id = $request->receiver;
            $transfer->save();

            if (!empty($request->products) && sizeof($request->products) > 0) {
                foreach ($request->products as $key => $product) {
                    $productTransferLine = new ProductTransferLine();
                    $productTransferLine->quantity = $request->quantities[$key];
                    $productTransferLine->unit_price = $request->unit_prices[$key];
                    $productTransferLine->product_id = $product;
                    $productTransferLine->transfer_id = $transfer->id;
                    $productTransferLine->save();
                }
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'transfer' => $transfer,
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
        $transfer = Transfer::with('productsTransfersLines')->findOrFail($id);
        $productsTransfersLines = $transfer ? $transfer->productsTransfersLines : null;

        return new JsonResponse([
            'transfer' => $transfer,
            'datas' => ['productsTransfersLines' => $productsTransfersLines]
        ], 200);
    }

    public function edit($id)
    {
        $transfer = Transfer::with('productsTransfersLines')->findOrFail($id);
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $productsTransfersLines = $transfer ? $transfer->productsTransfersLines : null;

        return new JsonResponse([
            'transfer' => $transfer,
            'datas' => ['products' => $products, 'productsTransfersLines' => $productsTransfersLines]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $transfer = Transfer::findOrFail($id);
        $currentDate = date('Y-m-d', strtotime(now()));
        $this->validate(
            $request,
            [
                'transmitter' => 'required',
                'receiver' => 'required',
                'transfer_reason' => 'required',
                'date_of_transfer' => 'required|date|date_format:Y-m-d|date_equals:' . $currentDate,
                'date_of_receipt' => 'date|date_format:Y-m-d|after:date_of_transfer',
                'products' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
            ],
            [
                'transmitter.required' => "Le point de vente source est obligatoire.",
                'receiver.required' => "Le point de vente destination est obligatoire.",
                'transfer_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_transfer.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_transfer.date' => "La date de la demande de transfert est invalide.",
                'date_of_transfer.date_format' => "La date de la demande de transfert doit être sous le format : AAAA-MM-JJ.",
                'date_of_transfer.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'date_of_receipt.date' => "La date limite de livraison est invalide.",
                'date_of_receipt.date_format' => "La date limite de livraison doit être sous le format : AAAA-MM-JJ.",
                'date_of_receipt.after' => "La date limite de livraison ne peut être antérieur à la date de transfert.",
                'products.required' => "Vous devez ajouter au moins un produit.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $transfer->transfer_reason = $request->transfer_reason;
            $transfer->date_of_transfer = $request->date_of_transfer;
            $transfer->date_of_receipt = $request->date_of_receipt;
            $transfer->transmitter_id = $request->transmitter;
            $transfer->receiver_id = $request->receiver;
            $transfer->save();

            ProductTransferLine::where('transfer_id', $transfer->id)->delete();

            if (!empty($request->products) && sizeof($request->products) > 0) {
                foreach ($request->products as $key => $product) {
                    $productTransferLine = new ProductTransferLine();
                    $productTransferLine->quantity = $request->quantities[$key];
                    $productTransferLine->unit_price = $request->unit_prices[$key];
                    $productTransferLine->product_id = $product;
                    $productTransferLine->transfer_id = $transfer->id;
                    $productTransferLine->save();
                }
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'transfer' => $transfer,
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
        $transfer = Transfer::findOrFail($id);
        // $productsTransfersLines = $transfer ? $transfer->productsTransfersLines : null;
        try {
            $transfer->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'transfer' => $transfer,
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
}
