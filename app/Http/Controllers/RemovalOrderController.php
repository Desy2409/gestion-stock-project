<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\Order;
use App\Models\RemovalOrder;
use App\Models\RemovalOrderRegister;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\PurchaseOrder;
use App\Models\SalePoint;
use App\Models\StockType;
use App\Models\Transfer;
use App\Repositories\PurchaseOrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemovalOrderController extends Controller
{

    use UtilityTrait;

    private $voucherTypes = ["Externe", "Interne"];
    private $customsRegimes = ["HT", "TTC"];

    public $purchaseOrderDeliveredProducts;

    public function __construct(PurchaseOrderRepository $purchaseOrderDeliveredProducts)
    {
        $this->purchaseOrderDeliveredProducts = $purchaseOrderDeliveredProducts;
    }

    public function index()
    {

        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $idOfProviderTypeStorageUnits = ProviderType::where('type', "Unité de stockage")->pluck('id')->toArray();
        $idOfProviderTypeCarriers = ProviderType::where('type', "Transporteur")->pluck('id')->toArray();

        $removalOrders = RemovalOrder::orderBy('voucher_date')->orderBy('reference')->get();
        $storageUnits = Provider::whereIn('provider_type_id', $idOfProviderTypeStorageUnits)->with('person')->get();
        $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        // $stockTypes = StockType::orderBy('wording')->get();
        $clients = Client::with('person.address')->get();
        // $transfers = Transfer::orderBy('code')->get();

        $lastRemovalOrderRegister = RemovalOrderRegister::latest()->first();

        $removalOrderRegister = new RemovalOrderRegister();
        if ($lastRemovalOrderRegister) {
            $removalOrderRegister->code = $this->formateNPosition('BE', $lastRemovalOrderRegister->id + 1, 8);
        } else {
            $removalOrderRegister->code = $this->formateNPosition('BE', 1, 8);
        }
        $removalOrderRegister->save();

        return new JsonResponse([
            'datas' => [
                'removalOrders' => $removalOrders, 'voucherTypes' => $this->voucherTypes,
                'storageUnits' => $storageUnits, 'carriers' => $carriers,
                'customsRegimes' => $this->customsRegimes, 'salePoints' => $salePoints,
                // 'stockTypes' => $stockTypes, 
                'clients' => $clients,
                // 'transfers' => $transfers
            ]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $lastRemovalOrderRegister = RemovalOrderRegister::latest()->first();
        if ($lastRemovalOrderRegister) {
            $code = $this->formateNPosition('BE', $lastRemovalOrderRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BE', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function salePointsFromTransfer($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $transfer = Transfer::findOrFail($id);
        $transmitter = SalePoint::findOrFail($transfer->transmitter_id);
        $receiver = SalePoint::findOrFail($transfer->receiver_id);

        return new JsonResponse(['transmitter' => $transmitter, 'receiver' => $receiver]);
    }

    public function datasOnPurchaseOrderSelect($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productClientDeliveryNotes = $this->orderRepository->purchaseOrderDeliveredProducts($purchaseOrder);
        return new JsonResponse(['datas' => ['productClientDeliveryNotes' => $productClientDeliveryNotes]], 200);
    }

    public function onClientSelect($id)
    {
        $client = Client::findOrFail($id);
        return new JsonResponse(['exemption_reference' => $client->exemption_reference], 200);
    }

    public function onCarrierSelect($id)
    {
        $provider = Provider::with('person')->with('trucks')->findOrFail($id);
        return new JsonResponse(['provider' => $provider], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_CREATE', RemovalOrder::class);
        $this->validate(
            $request,
            [
                'client' => 'required',
                'stock_type' => 'required',
                'reference' => 'required|unique:good_to_removes',
                'voucher_date' => 'required|date|date_equals:today', //|date_format:Ymd
                'delivery_date_wished' => 'required|date|after:voucher_date', //|date_format:Ymd
                'voucher_type' => 'required',
                'customs_regime' => 'required',
                'storage_unit' => 'required',
                'carrier' => 'required',
            ],
            [
                'stock_type' => "Le choix du client est obligatoire.",
                'stock_type' => "Le choix du type de stock est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'voucher_date.required' => "La date du bon à enlever est obligatoire.",
                'voucher_date.date' => "La date du bon à enlever est incorrecte.",
                // 'voucher_date.date_format' => "La date du bon à enlever doit être sous le format : Année Mois Jour.",
                'voucher_date.date_equals' => "La date du bon à enlever ne peut être qu'aujourd'hui.",
                'delivery_date_wished.required' => "La date de livraison souhaitée prévue est obligatoire.",
                'delivery_date_wished.date' => "La date de livraison souhaitée est incorrecte.",
                // 'delivery_date_wished.date_format' => "La date de livraison souhaitée doit être sous le format : Année Mois Jour.",
                'delivery_date_wished.after' => "La date de livraison souhaitée doit être ultérieure à la date du bon à enlever.",
                'voucher_type.required' => "Le type de bon est obligatoire.",
                'customs_regime.required' => "Le régime douanier est obligatoire.",
                'storage_unit.required' => "L'unité de stockage est obligatoire.",
                'carrier.required' => "Le transporteur est obligatoire.",
            ],
        );

        try {
            $lastRemovalOrder = RemovalOrder::latest()->first();

            $removalOrder = new RemovalOrder();
            if ($lastRemovalOrder) {
                $removalOrder->code = $this->formateNPosition('BE', $lastRemovalOrder->id + 1, 8);
            } else {
                $removalOrder->code = $this->formateNPosition('BE', 1, 8);
            }
            $removalOrder->reference = $request->reference;
            $removalOrder->voucher_date = $request->voucher_date;
            $removalOrder->delivery_date_wished = $request->delivery_date_wished;
            $removalOrder->place_of_delivery = $request->place_of_delivery;
            $removalOrder->voucher_type = $request->voucher_type;
            $removalOrder->customs_regime = $request->customs_regime;
            $removalOrder->storage_unit_id = $request->storage_unit;
            $removalOrder->carrier_id = $request->carrier;
            $removalOrder->transmitter_id = $request->transmitter;
            $removalOrder->receiver_id = $request->receiver;
            $removalOrder->client_id = $request->client;
            $removalOrder->stock_type_id = $request->stock_type;
            $removalOrder->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'removalOrder' => $removalOrder,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $removalOrder = RemovalOrder::with('salePoint')->findOrFail($id);
        return new JsonResponse([
            'removalOrder' => $removalOrder
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_READ', RemovalOrder::class);
        $removalOrder = RemovalOrder::with('salePoint')->findOrFail($id);
        return new JsonResponse([
            'removalOrder' => $removalOrder,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_UPDATE', RemovalOrder::class);
        $removalOrder = RemovalOrder::findOrFail($id);
        $this->validate(
            $request,
            [
                'client' => 'required',
                'stock_type' => 'required',
                'reference' => 'required|unique:good_to_removes',
                'voucher_date' => 'required|date|date_format:Ymd|date_equals:today',
                'delivery_date_wished' => 'required|date|date_format:Ymd|after:voucher_date',
                'voucher_type' => 'required',
                'customs_regime' => 'required',
                'storage_unit' => 'required',
                'carrier' => 'required',
            ],
            [
                'stock_type' => "Le choix du client est obligatoire.",
                'stock_type' => "Le choix du type de stock est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'voucher_date.required' => "La date du bon à enlever est obligatoire.",
                'voucher_date.date' => "La date du bon à enlever est incorrecte.",
                'voucher_date.date_format' => "La date du bon à enlever doit être sous le format : Année Mois Jour.",
                'voucher_date.date_equals' => "La date du bon à enlever ne peut être qu'aujourd'hui.",
                'delivery_date_wished.required' => "La date de livraison souhaitée prévue est obligatoire.",
                'delivery_date_wished.date' => "La date de livraison souhaitée est incorrecte.",
                'delivery_date_wished.date_format' => "La date de livraison souhaitée doit être sous le format : Année Mois Jour.",
                'delivery_date_wished.after' => "La date de livraison souhaitée doit être ultérieure à la date du bon à enlever.",
                'voucher_type.required' => "Le type de bon est obligatoire.",
                'customs_regime.required' => "Le régime douanier est obligatoire.",
                'storage_unit.required' => "L'unité de stockage est obligatoire.",
                'carrier.required' => "Le transporteur est obligatoire.",
            ],
        );

        $existingRemovalOrders = RemovalOrder::where('reference', $request->reference)->get();
        if (!empty($existingRemovalOrders) && sizeof($existingRemovalOrders) > 1) {
            $success = false;
            return new JsonResponse([
                'existingRemovalOrder' => $existingRemovalOrders[0],
                'success' => $success,
                'message' => "Le bon à enlever portant la référence " . $existingRemovalOrders[0]->reference . " existe déjà."
            ], 200);
        }

        try {
            $removalOrder->reference = $request->reference;
            $removalOrder->voucher_date = $request->voucher_date;
            $removalOrder->delivery_date_wished = $request->delivery_date_wished;
            $removalOrder->place_of_delivery = $request->place_of_delivery;
            $removalOrder->voucher_type = $request->voucher_type;
            $removalOrder->customs_regime = $request->customs_regime;
            $removalOrder->storage_unit_id = $request->storage_unit;
            $removalOrder->carrier_id = $request->carrier;
            $removalOrder->transmitter_id = $request->transmitter;
            $removalOrder->receiver_id = $request->receiver;
            $removalOrder->client_id = $request->client;
            $removalOrder->stock_type_id = $request->stock_type;
            $removalOrder->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'removalOrder' => $removalOrder,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_REMOVAL_ORDER_DELETE', RemovalOrder::class);
        $removalOrder = RemovalOrder::findOrFail($id);
        try {
            $removalOrder->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'removalOrder' => $removalOrder,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }
}
