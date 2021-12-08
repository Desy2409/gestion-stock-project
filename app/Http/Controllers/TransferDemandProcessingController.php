<?php

namespace App\Http\Controllers;

use App\Models\ProductTransferDemandLine;
use App\Models\ProductTransferLine;
use App\Models\Transfer;
use App\Models\TransferDemand;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferDemandProcessingController extends Controller
{

    public function index()
    {
        // $user = Auth::user();
        $transfersDemands = TransferDemand::with('productsTransfersDemandsLines')->orderBy('date_of_demand', 'desc')->orderBy('request_reason')->get();
        // $issuedDemands = TransferDemand::whereIn('transmitter_id', $user->sale_points)->get();
        // $receivedDemands = TransferDemand::where('receiver_id', $user->sale_points)->get();

        // return new JsonResponse(['datas' => ['issuedDemands' => $issuedDemands, 'receivedDemands' => $receivedDemands]], 200);
        return new JsonResponse(['datas' => ['transfersDemands' => $transfersDemands]], 200);
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

    public function rejectTransferDemand($id)
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
