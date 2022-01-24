<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductTransferDemandLine;
use App\Models\ProductTransferLine;
use App\Models\SalePoint;
use App\Models\Transfer;
use App\Models\TransferDemand;
use App\Models\TransferRegister;
use App\Repositories\TransferRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    use UtilityTrait;


    public $transferRepository;

    public function __construct(TransferRepository $transferRepository)
    {
        $this->transferRepository = $transferRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_TRANSFER_READ', Transfer::class);
        $salesPoints = SalePoint::with('institution')->orderBy('social_reason')->get();
        // $products = Product::with('subCategory')->orderBy('wording')->get();
        $transfers = Transfer::with('transferDemand')->orderBy('date_of_transfer', 'desc')->orderBy('transfer_reason')->get();
        // $transfers = Transfer::with('transferDemand')->with('productsTransfersLines')->with('getTransmitterAttribute')->with('getReceiverAttribute')->orderBy('date_of_transfer', 'desc')->orderBy('transfer_reason')->get();
        // $transferDemands = TransferDemand::with('productsTransfersDemandsLines')->where('state', 'S')->orderBy('date_of_demand', 'desc')->orderBy('request_reason')->get();
        $transferDemands = TransferDemand::with('productsTransfersDemandsLines')->orderBy('date_of_demand', 'desc')->orderBy('request_reason')->get();

        $lastTransferRegister = TransferRegister::latest()->first();

        $transferRegister = new TransferRegister();
        if ($lastTransferRegister) {
            $transferRegister->code = $this->formateNPosition('TF', $lastTransferRegister->id + 1, 8);
        } else {
            $transferRegister->code = $this->formateNPosition('TF', 1, 8);
        }
        $transferRegister->save();

        return new JsonResponse([
            'datas' => ['transfers' => $transfers, 'transferDemands' => $transferDemands, 'salesPoints' => $salesPoints]
        ], 200);
    }

    public function datasOnSelectTransferDemand($id)
    {
        $this->authorize('ROLE_DELIVERY_NOTE_READ', DeliveryNote::class);
        $transferDemand = TransferDemand::findOrFail($id);
        $transmitter = SalePoint::where('id', $transferDemand->transmitter_id)->first();
        $receiver = SalePoint::where('id', $transferDemand->receiver_id)->first();
        $transferDemandProducts = ProductTransferDemandLine::with('product')->with('unity')->where('transfer_demand_id', $transferDemand->id)->get();
        return new JsonResponse([
            'transferDemand' => $transferDemand, 'transmitter' => $transmitter, 'receiver' => $receiver, 'datas' => ['transferDemandProducts' => $transferDemandProducts]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_TRANSFER_READ', Transfer::class);
        $lastTransferRegister = TransferRegister::latest()->first();
        if ($lastTransferRegister) {
            $code = $this->formateNPosition('TF', $lastTransferRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('TF', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_TRANSFER_CREATE', Transfer::class);
        $this->validate(
            $request,
            [
                'transfer_demand' => 'required',
                // 'transmitter' => 'required',
                // 'receiver' => 'required',
                'transfer_reason' => 'required',
                'date_of_transfer' => 'required|date|date_equals:today', //|date_format:Ymd
                'date_of_receipt' => 'date|after:date_of_transfer', //|date_format:Ymd
                'transferProducts' => 'required',
                // 'quantities' => 'required|min:0',
                // 'unit_prices' => 'required|min:0',
            ],
            [
                'transfer_demand.required' => "Le choix d'une demande de transfert est obligatoire.",
                // 'transmitter.required' => "Le point de vente source est obligatoire.",
                // 'receiver.required' => "Le point de vente destination est obligatoire.",
                'transfer_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_transfer.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_transfer.date' => "La date de la demande de transfert est invalide.",
                // 'date_of_transfer.date_format' => "La date de la demande de transfert doit être sous le format : Année Mois Jour.",
                'date_of_transfer.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'date_of_receipt.date' => "La date limite de livraison est invalide.",
                // 'date_of_receipt.date_format' => "La date limite de livraison doit être sous le format : Année Mois Jour.",
                'date_of_receipt.after' => "La date limite de livraison ne peut être antérieur à la date de transfert.",
                'transferProducts.required' => "Vous devez ajouter au moins un produit.",
                // 'quantities.required' => "Les quantités sont obligatoires.",
                // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $lastTransfer = Transfer::latest()->first();

            $transfer = new Transfer();
            if ($lastTransfer) {
                $transfer->code = $this->formateNPosition('TF', $lastTransfer->id + 1, 8);
            } else {
                $transfer->code = $this->formateNPosition('TF', 1, 8);
            }
            $transfer->transfer_reason = $request->transfer_reason;
            $transfer->date_of_transfer = $request->date_of_transfer;
            $transfer->date_of_receipt = $request->date_of_receipt;
            $transfer->transmitter_id = $request->transmitter;
            $transfer->receiver_id = $request->receiver;
            $transfer->transfer_demand_id = $request->transfer_demand;
            $transfer->save();

            $productTansfers = [];
            foreach ($request->transferProducts as $key => $product) {
                $transferLine = new ProductTransferLine();
                $transferLine->quantity = $product["quantity"];
                $transferLine->unity_id = $product['unity']["id"];
                $transferLine->product_id = $product['product']["id"];
                $transferLine->transfer_id = $transfer->id;
                $transferLine->save();

                array_push($productTansfers, $transferLine);
            }

            // $savedProductTransferLines = ProductTransferLine::where('transfer_id', $transfer->id)->get();
            // if (empty($savedProductTransferLines) || sizeof($savedProductTransferLines) == 0) {
            //     $transfer->delete();
            // }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'transfer' => $transfer,
                'success' => $success,
                'message' => $message,
                'datas' => ['productTansfers' => $productTansfers]
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
        $this->authorize('ROLE_TRANSFER_READ', Transfer::class);
        $transfer = Transfer::with('productsTransfersLines')->findOrFail($id);
        $productsTransfersLines = $transfer ? $transfer->productsTransfersLines : null;

        return new JsonResponse([
            'transfer' => $transfer,
            'datas' => ['productsTransfersLines' => $productsTransfersLines]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_TRANSFER_READ', Transfer::class);
        $transfer = Transfer::with('productsTransfersLines')->with('transferDemand')->findOrFail($id);
        $productsTransfersLines = ProductTransferLine::where('transfer_id',$transfer->id)->with('product')->with('unity')->get();
        // $productsTransfersLines = $transfer ? $transfer->productsTransfersLines : null;

        return new JsonResponse([
            'transfer' => $transfer,
            'datas' => ['productsTransfersLines' => $productsTransfersLines]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_TRANSFER_UPDATE', Transfer::class);
        $transfer = Transfer::findOrFail($id);
        $this->validate(
            $request,
            [
                'transfer_demand' => 'required',
                // 'transmitter' => 'required',
                // 'receiver' => 'required',
                'transfer_reason' => 'required',
                'date_of_transfer' => 'required|date', //|date_format:Ymd
                'date_of_receipt' => 'date|after:date_of_transfer', //|date_format:Ymd
                'transferProducts' => 'required',
                // 'quantities' => 'required|min:0',
                // 'unit_prices' => 'required|min:0',
            ],
            [
                'transfer_demand.required' => "Le choix d'une demande de transfert est obligatoire.",
                // 'transmitter.required' => "Le point de vente source est obligatoire.",
                // 'receiver.required' => "Le point de vente destination est obligatoire.",
                'transfer_reason.required' => "Le motif de la demande de transfert est obligatoire.",
                'date_of_transfer.required' => "La date de la demande de transfert est obligatoire.",
                'date_of_transfer.date' => "La date de la demande de transfert est invalide.",
                // 'date_of_transfer.date_format' => "La date de la demande de transfert doit être sous le format : Année Mois Jour.",
                'date_of_transfer.date_equals' => "La date de la demande de transfert ne peut être qu'aujourd'hui.",
                'date_of_receipt.date' => "La date limite de livraison est invalide.",
                // 'date_of_receipt.date_format' => "La date limite de livraison doit être sous le format : Année Mois Jour.",
                'date_of_receipt.after' => "La date limite de livraison ne peut être antérieur à la date de transfert.",
                'transferProducts.required' => "Vous devez ajouter au moins un produit.",
                // 'quantities.required' => "Les quantités sont obligatoires.",
                // 'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
            ]
        );

        try {
            $transfer->transfer_reason = $request->transfer_reason;
            $transfer->date_of_transfer = $request->date_of_transfer;
            $transfer->date_of_receipt = $request->date_of_receipt;
            $transfer->transmitter_id = $request->transmitter;
            $transfer->receiver_id = $request->receiver;
            $transfer->transfer_demand_id = $request->transfer_demand;
            $transfer->save();

            ProductTransferLine::where('transfer_id', $transfer->id)->delete();

            $productTansfers = [];
            foreach ($request->transferProducts as $key => $product) {
                $transferLine = new ProductTransferLine();
                $transferLine->quantity = $product["quantity"];
                $transferLine->unity_id = $product['unity']["id"];
                $transferLine->product_id = $product['product']["id"];
                $transferLine->transfer_id = $transfer->id;
                $transferLine->save();

                array_push($productTansfers, $transferLine);
            }

            $savedProductTransferLines = ProductTransferLine::where('transfer_id', $transfer->id)->get();
            if (empty($savedProductTransferLines) || sizeof($savedProductTransferLines) == 0) {
                $transfer->delete();
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'transfer' => $transfer,
                'success' => $success,
                'message' => $message,
                'datas' => ['productTansfers' => $productTansfers]
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
        $this->authorize('ROLE_TRANSFER_DELETE', Transfer::class);
        $transfer = Transfer::findOrFail($id);
        // $productsTransfersLines = $transfer ? $transfer->productsTransfersLines : null;
        try {
            $success = false;
            $message = "";
            if (empty($transfer->productsTransfersLines) || sizeof($transfer->productsTransfersLines) == 0) {
                // dd('delete');
                $transfer->delete();

                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce transfert ne peut être supprimé car il a servi dans des traitements.";
            }
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


    public function transferReports(Request $request)
    {
        $this->authorize('ROLE_TRANSFER_DEMAND_PRINT', Tourn::class);
        try {
            $transfers = $this->transferRepository->transferReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['transfers' => $transfers]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
