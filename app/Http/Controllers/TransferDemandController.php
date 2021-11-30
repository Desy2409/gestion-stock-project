<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductTransferDemandLine;
use App\Models\ProductTransferLine;
use App\Models\SalePoint;
use App\Models\Transfer;
use App\Models\TransferDemand;
use App\Models\TransferDemandRegister;
use App\Models\Unity;
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
        $this->authorize('ROLE_TRANSFER_DEMAND_READ', TransferDemand::class);
        $salesPoints = SalePoint::orderBy('social_reason')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        // $transfersDemands = TransferDemand::with('salePoint')->with('productsTransfersDemandsLines')->orderBy('date_of_demand', 'desc')->orderBy('request_reason')->get();
        $transfersDemands = TransferDemand::with('productsTransfersDemandsLines')->orderBy('date_of_demand', 'desc')->orderBy('request_reason')->get();
        $unities = Unity::orderBy('wording')->get();

        $lastTransferDemandRegister = TransferDemandRegister::latest()->first();

        $transferDemandRegister = new TransferDemandRegister();
        if ($lastTransferDemandRegister) {
            $transferDemandRegister->code = $this->formateNPosition('DT', $lastTransferDemandRegister->id + 1, 8);
        } else {
            $transferDemandRegister->code = $this->formateNPosition('DT', 1, 8);
        }
        $transferDemandRegister->save();

        return new JsonResponse([
            'datas' => ['transfersDemands' => $transfersDemands, 'salesPoints' => $salesPoints, 'products' => $products, 'unities' => $unities]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_TRANSFER_DEMAND_READ', TransferDemand::class);
        $lastTransferDemandRegister = TransferDemandRegister::latest()->first();
        if ($lastTransferDemandRegister) {
            $code = $this->formateNPosition('DT', $lastTransferDemandRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('DT', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }
    public function store(Request $request)
    {
        $this->authorize('ROLE_TRANSFER_DEMAND_CREATE', TransferDemand::class);
        $this->validate(
            $request,
            [
                'transmitter' => 'required',
                'receiver' => 'required',
                'request_reason' => 'required',
                'date_of_demand' => 'required|date|date_equals:today', //|date_format:Ymd
                'delivery_deadline' => 'required|date|after:date_of_demand', //|date_format:Ymd
                'date_of_processing' => 'date|after:date_of_demand', //|date_format:Ymd
                'transferDemandProducts' => 'required',
                // 'quantities' => 'required|min:0',
                // 'unit_prices' => 'required|min:0',
            ],
            [
                'transmitter.required' => "Le point de vente source est obligatoire.",
                'receiver.required' => "Le point de vente destination est obligatoire.",
                'request_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_demand.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_demand.date' => "La date de la demande de transfert est invalide.",
                // 'date_of_demand.date_format' => "La date de la demande de transfert doit être sous le format : Année Mois Jour.",
                'date_of_demand.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'delivery_deadline.required' => "La date limite de livraison est obligatoire.",
                'delivery_deadline.date' => "La date limite de livraison est invalide.",
                // 'delivery_deadline.date_format' => "La date limite de livraison doit être sous le format : Année Mois Jour.",
                'delivery_deadline.after' => "La date limite de livraison ne peut être antérieur à la date de demande de transfert.",
                'transferDemandProducts.required' => "Vous devez ajouter au moins un produit.",
                // 'quantities.required' => "Les quantités sont obligatoires.",
                // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            // dd($request->transferDemandProducts);
            $lastTransferDemand = TransferDemand::latest()->first();

            $transferDemand = new TransferDemand();
            if ($lastTransferDemand) {
                $transferDemand->code = $this->formateNPosition('DT', $lastTransferDemand->id + 1, 8);
            } else {
                $transferDemand->code = $this->formateNPosition('DT', 1, 8);
            }
            $transferDemand->request_reason = $request->request_reason;
            $transferDemand->date_of_demand = $request->date_of_demand;
            $transferDemand->delivery_deadline = $request->delivery_deadline;
            $transferDemand->transmitter_id = $request->transmitter;
            $transferDemand->receiver_id = $request->receiver;
            $transferDemand->state = 'P';
            $transferDemand->save();

            $productTansferDemands = [];
            foreach ($request->transferDemandProducts as $key => $product) {
                $transferDemandLine = new ProductTransferDemandLine();
                $transferDemandLine->quantity = $product["quantity"];
                $transferDemandLine->unity_id = $product['unity'];
                $transferDemandLine->product_id = $product['product'];
                $transferDemandLine->transfer_demand_id = $transferDemand->id;
                $transferDemandLine->save();

                array_push($productTansferDemands, $transferDemandLine);
            }


            // $savedProductTransferDemandLine = ProductTransferDemandLine::where('transfer_demand_id', $transferDemand->id)->get();
            // if (empty($savedProductTransferDemandLine) || sizeof($savedProductTransferDemandLine) == 0) {
            //     $transferDemand->delete();
            // }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'success' => $success,
                'message' => $message,
                'datas' => ['productTansferDemands' => $productTansferDemands]
            ], 200);
        } catch (Exception $e) {
            // dd($e);
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
        $this->authorize('ROLE_TRANSFER_DEMAND_READ', TransferDemand::class);
        $transferDemand = TransferDemand::with('salePoint')->with('productsTransfersDemandsLines')->findOrFail($id);
        $productsTransferDemands = $transferDemand ? $transferDemand->productsTransfersDemandsLines : null;

        return new JsonResponse([
            'transferDemand' => $transferDemand,
            'datas' => ['productsTransferDemands' => $productsTransferDemands]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_TRANSFER_DEMAND_READ', TransferDemand::class);
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
        $this->authorize('ROLE_TRANSFER_DEMAND_UPDATE', TransferDemand::class);
        $transferDemand = TransferDemand::findOrFail($id);
        $this->validate(
            $request,
            [
                'transmitter' => 'required',
                'receiver' => 'required',
                'request_reason' => 'required',
                'date_of_demand' => 'required|date|date_format:Ymd|date_equals:today',
                'delivery_deadline' => 'required|date|date_format:Ymd|after:date_of_demand',
                'date_of_processing' => 'date|date_format:Ymd|after:date_of_demand',
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
                'date_of_demand.date_format' => "La date de la demande de transfert doit être sous le format : Année Mois Jour.",
                'date_of_demand.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'delivery_deadline.required' => "La date limite de livraison est obligatoire.",
                'delivery_deadline.date' => "La date limite de livraison est invalide.",
                'delivery_deadline.date_format' => "La date limite de livraison doit être sous le format : Année Mois Jour.",
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

            $productTansferDemands = [];
            foreach ($request->transferDemandProducts as $key => $product) {
                $transferDemandLine = new ProductTransferDemandLine();
                $transferDemandLine->quantity = $product["quantity"];
                $transferDemandLine->unity_id = $product['unity'];
                $transferDemandLine->product_id = $product;
                $transferDemandLine->transfer_demand_id = $transferDemand->id;
                $transferDemandLine->save();

                array_push($productTansferDemands, $transferDemandLine);
            }

            // $savedProductTransferDemandLine = ProductTransferDemandLine::where('transfer_demand_id', $transferDemand->id)->get();
            // if (empty($savedProductTransferDemandLine) || sizeof($savedProductTransferDemandLine) == 0) {
            //     $transferDemand->delete();
            // }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'success' => $success,
                'message' => $message,
                'datas' => ['productTansferDemands' => $productTansferDemands]
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
        $this->authorize('ROLE_TRANSFER_DEMAND_DELETE', TransferDemand::class);
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
        $this->authorize('ROLE_TRANSFER_DEMAND_VALIDATE', TransferDemand::class);
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
        $this->authorize('ROLE_TRANSFER_DEMAND_REJECT', TransferDemand::class);
        $transferDemand = TransferDemand::findOrFail($id);
        try {
            $transferDemand->state = 'A';
            $transferDemand->date_of_processing = date('Y-m-d', strtotime(now()));
            $transferDemand->save();

            $success = true;
            $message = "Demande de transfert annulée avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'annulation de la demande de transfert.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function transformDemandToTransfer($id)
    {
        $this->authorize('ROLE_TRANSFER_DEMAND_TRANSFORM', TransferDemand::class);
        $transferDemand = TransferDemand::findOrFail($id);

        try {
            $lastTransfer = Transfer::latest()->first();

            $transfer = new Transfer();
            if ($lastTransfer) {
                $transfer->code = $this->formateNPosition('TF', $lastTransfer->id + 1, 8);
            } else {
                $transfer->code = $this->formateNPosition('TF', 1, 8);
            }
            $transfer->transfer_reason = $transferDemand->request_reason;
            $transfer->date_of_transfer = date('Ymd');
            // $transfer->date_of_receipt = $request->date_of_receipt;
            $transfer->transmitter_id = $transferDemand->receiver_id;
            $transfer->receiver_id = $transferDemand->transmitter_id;
            // $transfer->save();

            $productTransferDemandLines = ProductTransferDemandLine::where('transfer_demand_id', $transferDemand->id)->get();

            $productTransferLines = [];
            if (!empty($productTransferDemandLines) && sizeof($productTransferDemandLines) > 0) {
                foreach ($productTransferDemandLines as $key => $productTransferDemandLine) {
                    $productTransferLine = new ProductTransferLine();
                    $productTransferLine->quantity = $productTransferDemandLine->quantity;
                    $productTransferLine->unit_price = $productTransferDemandLine->unit_price;
                    $productTransferLine->product_id = $productTransferDemandLine->product_id;
                    // $productTransferLine->transfer_id = $transfer->id;

                    array_push($productTransferLines, $productTransferLine);
                }
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'transferDemand' => $transferDemand,
                'transfer' => $transfer,
                'success' => $success,
                'message' => $message,
                'datas' => ['productTransferLines' => $productTransferLines]
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la transformation de la demande de transfert en transfert.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }
}
