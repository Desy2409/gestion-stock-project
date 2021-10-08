<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPurchaseOrder;
use App\Models\PurchaseOrder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::orderBy('purchase_date')->orderBy('order_number')->get('');
        return [
            'purchaseOrders' => $purchaseOrders
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::orderBy('wording')->get();
        return [
            'products' => $products
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'quantity' => 'required|min:0',
                'purchase_date' => 'required|date',
                'delivery_date' => 'required|date',
                'observation' => 'max:255',
            ],
            [
                'quantity.required' => "La quatité est obligatoire.",
                'quantity.min' => "La quatité ne peut être inférieur à 0.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "Format de date incorrect.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "Format de date incorrect.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->reference = $request->reference;
            // $purchaseOrder->order_number   
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->save();

            $productsPurchaseOrders = $request->ordered_product;
            foreach ($productsPurchaseOrders as $key => $item) {
                $productsPurchaseOrder = new ProductPurchaseOrder();
                $productsPurchaseOrder->quantity = $item['quantity'];
                $productsPurchaseOrder->total_price = $item['total_price'];
                $productsPurchaseOrder->product_id = $item['product'];
                $productsPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productsPurchaseOrder->save();

                return $productsPurchaseOrder;
            }

            return $purchaseOrder;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de l'enregistrement.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productPurchaseOrders = $purchaseOrder->productPurchaseOrders;
        return [
            'purchaseOrder' => $purchaseOrder,
            'productPurchaseOrders' => $productPurchaseOrders,
        ];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productPurchaseOrders = $purchaseOrder->productPurchaseOrders;
        return [
            'purchaseOrder' => $purchaseOrder,
            'productPurchaseOrders' => $productPurchaseOrders,
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $this->validate(
            $request,
            [
                'quantity' => 'required|min:0',
                'purchase_date' => 'required|date',
                'delivery_date' => 'required|date',
                'observation' => 'max:255',
            ],
            [
                'quantity.required' => "La quatité est obligatoire.",
                'quantity.min' => "La quatité ne peut être inférieur à 0.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "Format de date incorrect.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "Format de date incorrect.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $purchaseOrder->reference = $request->reference;
            // $purchaseOrder->order_number   
            $purchaseOrder->purchase_date   = $request->purchase_date;
            $purchaseOrder->delivery_date   = $request->delivery_date;
            $purchaseOrder->total_amount = $request->total_amount;
            $purchaseOrder->observation = $request->observation;
            $purchaseOrder->save();

            $oldProductsPurchaseOrders = $purchaseOrder->productPurchaseOrders;
            foreach ($oldProductsPurchaseOrders as $key => $productsPurchaseOrder) {
                $productsPurchaseOrder->delete();
            }

            $productsPurchaseOrders = $request->ordered_product;
            foreach ($productsPurchaseOrders as $key => $item) {
                $productsPurchaseOrder = new ProductPurchaseOrder();
                $productsPurchaseOrder->quantity = $item['quantity'];
                $productsPurchaseOrder->total_price = $item['total_price'];
                $productsPurchaseOrder->product_id = $item['product'];
                $productsPurchaseOrder->purchase_order_id = $purchaseOrder->id;
                $productsPurchaseOrder->save();

                return $productsPurchaseOrder;
            }

            return $purchaseOrder;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la modification.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $productsPurchaseOrders = $purchaseOrder->productPurchaseOrders;
        try {
            foreach ($productsPurchaseOrders as $key => $productsPurchaseOrder) {
                $productsPurchaseOrder->delete();
                return $productsPurchaseOrder;
            }
            $purchaseOrder->delete();
            return $purchaseOrder;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppression.");
        }
    }
}
