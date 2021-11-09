<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Client;
use App\Models\GoodToRemove;
use App\Models\Provider;
use App\Models\ProviderType;
use App\Models\SalePoint;
use App\Models\StockType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoodToRemoveController extends Controller
{

    use UtilityTrait;

    private $voucherTypes = ["Externe", "Interne"];
    private $customsRegimes = ["HT", "TTC"];

    public function index()
    {

        $idOfProviderTypeStorageUnits = ProviderType::where('type', "Unité de stockage")->pluck('id')->toArray();
        $idOfProviderTypeCarriers = ProviderType::where('type', "Transporteur")->pluck('id')->toArray();

        $goodToRemoves = GoodToRemove::orderBy('voucher_date')->orderBy('reference')->get();
        $storageUnits = Provider::whereIn('provider_type_id', $idOfProviderTypeStorageUnits)->with('person')->get();
        $carriers = Provider::whereIn('provider_type_id', $idOfProviderTypeCarriers)->with('person')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();
        $stockTypes = StockType::orderBy('wording')->get();
        $clients = Client::with('person.address')->get();
        return new JsonResponse([
            'datas' => [
                'goodToRemoves' => $goodToRemoves, 'voucherTypes' => $this->voucherTypes,
                'storageUnits' => $storageUnits, 'carriers' => $carriers,
                'customsRegimes' => $this->customsRegimes, 'salePoints' => $salePoints,
                'stockTypes' => $stockTypes, 'clients' => $clients
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $currentDate = date('d-m-Y', strtotime(now()));
        $this->validate(
            $request,
            [
                'client'=>'required',
                'stock_type'=>'required',
                'reference' => 'required|unique:good_to_removes',
                'voucher_date' => 'required|date|date_format:d-m-Y|date_equals:' . $currentDate,
                'delivery_date_wished' => 'required|date|date_format:d-m-Y|after:voucher_date',
                'voucher_type' => 'required',
                'customs_regime' => 'required',
                'storage_unit' => 'required',
                'carrier' => 'required',
            ],
            [
                'stock_type'=>"Le choix du client est obligatoire.",
                'stock_type'=>"Le choix du type de stock est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'voucher_date.required' => "La date du bon à enlever est obligatoire.",
                'voucher_date.date' => "La date du bon à enlever est incorrecte.",
                'voucher_date.date_format' => "La date du bon à enlever doit être sous le format : JJ/MM/AAAA.",
                'voucher_date.date_equals' => "La date du bon à enlever ne peut être qu'aujourd'hui.",
                'delivery_date_wished.required' => "La date de livraison souhaitée prévue est obligatoire.",
                'delivery_date_wished.date' => "La date de livraison souhaitée est incorrecte.",
                'delivery_date_wished.date_format' => "La date de livraison souhaitée doit être sous le format : JJ/MM/AAAA.",
                'delivery_date_wished.after' => "La date de livraison souhaitée doit être ultérieure à la date du bon à enlever.",
                'voucher_type.required' => "Le type de bon est obligatoire.",
                'customs_regime.required' => "Le régime douanier est obligatoire.",
                'storage_unit.required' => "L'unité de stockage est obligatoire.",
                'carrier.required' => "Le transporteur est obligatoire.",
            ],
        );

        try {
            $goods = GoodToRemove::all();
            $goodToRemove = new GoodToRemove();
            $goodToRemove->code =  $this->formateNPosition('BE', sizeof($goods) + 1, 8);
            $goodToRemove->reference = $request->reference;
            $goodToRemove->voucher_date = $request->voucher_date;
            $goodToRemove->delivery_date_wished = $request->delivery_date_wished;
            $goodToRemove->place_of_delivery = $request->place_of_delivery;
            $goodToRemove->voucher_type = $request->voucher_type;
            $goodToRemove->customs_regime = $request->customs_regime;
            $goodToRemove->storage_unit_id = $request->storage_unit;
            $goodToRemove->carrier_id = $request->carrier;
            $goodToRemove->transmitter_id = $request->transmitter;
            $goodToRemove->receiver_id = $request->receiver;
            $goodToRemove->client_id = $request->client;
            $goodToRemove->stock_type_id = $request->stock_type;
            $goodToRemove->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'goodToRemove' => $goodToRemove,
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
        $goodToRemove = GoodToRemove::with('salePoint')->findOrFail($id);
        return new JsonResponse([
            'goodToRemove' => $goodToRemove
        ], 200);
    }

    public function edit($id)
    {
        $goodToRemove = GoodToRemove::with('salePoint')->findOrFail($id);
        return new JsonResponse([
            'goodToRemove' => $goodToRemove,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $goodToRemove = GoodToRemove::findOrFail($id);
        $this->validate(
            $request,
            [
                'client'=>'required',
                'stock_type'=>'required',
                'reference' => 'required|unique:good_to_removes',
                'voucher_date' => 'required|date|date_format:d-m-Y',
                'delivery_date_wished' => 'required|date|date_format:d-m-Y|after:voucher_date',
                'voucher_type' => 'required',
                'customs_regime' => 'required',
                'storage_unit' => 'required',
                'carrier' => 'required',
            ],
            [
                'stock_type'=>"Le choix du client est obligatoire.",
                'stock_type'=>"Le choix du type de stock est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette référence existe déjà.",
                'voucher_date.required' => "La date du bon à enlever est obligatoire.",
                'voucher_date.date' => "La date du bon à enlever est incorrecte.",
                'voucher_date.date_format' => "La date du bon à enlever doit être sous le format : JJ/MM/AAAA.",
                'delivery_date_wished.required' => "La date de livraison souhaitée prévue est obligatoire.",
                'delivery_date_wished.date' => "La date de livraison souhaitée est incorrecte.",
                'delivery_date_wished.date_format' => "La date de livraison souhaitée doit être sous le format : JJ/MM/AAAA.",
                'delivery_date_wished.after' => "La date de livraison souhaitée doit être ultérieure à la date du bon à enlever.",
                'voucher_type.required' => "Le type de bon est obligatoire.",
                'customs_regime.required' => "Le régime douanier est obligatoire.",
                'storage_unit.required' => "L'unité de stockage est obligatoire.",
                'carrier.required' => "Le transporteur est obligatoire.",
            ],
        );

        $existingGoodToRemoves = GoodToRemove::where('reference', $request->reference)->get();
        if (!empty($existingGoodToRemoves) && sizeof($existingGoodToRemoves) > 1) {
            $success = false;
            return new JsonResponse([
                'existingGoodToRemove' => $existingGoodToRemoves[0],
                'success' => $success,
                'message' => "Le bon à enlever portant la référence " . $existingGoodToRemoves[0]->reference . " existe déjà."
            ], 200);
        }

        try {
            $goodToRemove->reference = $request->reference;
            $goodToRemove->voucher_date = $request->voucher_date;
            $goodToRemove->delivery_date_wished = $request->delivery_date_wished;
            $goodToRemove->place_of_delivery = $request->place_of_delivery;
            $goodToRemove->voucher_type = $request->voucher_type;
            $goodToRemove->customs_regime = $request->customs_regime;
            $goodToRemove->storage_unit_id = $request->storage_unit;
            $goodToRemove->carrier_id = $request->carrier;
            $goodToRemove->transmitter_id = $request->transmitter;
            $goodToRemove->receiver_id = $request->receiver;
            $goodToRemove->client_id = $request->client;
            $goodToRemove->stock_type_id = $request->stock_type;
            $goodToRemove->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'goodToRemove' => $goodToRemove,
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
        $goodToRemove = GoodToRemove::findOrFail($id);
        try {
            $goodToRemove->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'goodToRemove' => $goodToRemove,
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
