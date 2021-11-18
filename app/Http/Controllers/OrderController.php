<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Order;
use App\Models\OrderRegister;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Provider;
use App\Models\SalePoint;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $orders = Order::with('provider')->with('productOrders')->orderBy('purchase_date')->get();
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $unities = Unity::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();

        $lastOrderRegister = OrderRegister::latest()->first();

        $orderRegister = new OrderRegister();
        if ($lastOrderRegister) {
            $orderRegister->code = $this->formateNPosition('BC', $lastOrderRegister->id + 1, 8);
        } else {
            $orderRegister->code = $this->formateNPosition('BC', 1, 8);
        }
        $orderRegister->save();

        return new JsonResponse([
            'datas' => ['orders' => $orders, 'providers' => $providers, 'products' => $products, 'unities' => $unities, 'salePoints' => $salePoints]
        ], 200);
    }

    public function showNextCode()
    {
        $lastOrderRegister = OrderRegister::latest()->first();
        if ($lastOrderRegister) {
            $code = $this->formateNPosition('BC', $lastOrderRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition('BC', 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                // 'folder' => 'required',
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
                // 'upload_files' => 'required',
            ],
            [
                // 'folder.required' => "Le choix du dossier de destination des fichiers est obligatoire",
                'sale_point.required' => "Le choix du point de vente est obligatoire.",
                'provider.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon de commande est incorrecte.",
                'purchase_date.date_format' => "La date du bon de commande doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon de commande doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                // 'upload_files.required' => "Veuillez charger au moins un fichier lié au bon de commande.",
            ]
        );

        try {
            $lastOrder = Order::latest()->first();

            $order = new Order();
            if ($lastOrder) {
                $order->code = $this->formateNPosition('BC', $lastOrder->id + 1, 8);
            } else {
                $order->code = $this->formateNPosition('BC', 1, 8);
            }
            $order->reference = $request->reference;
            $order->purchase_date   = $request->purchase_date;
            $order->delivery_date   = $request->delivery_date;
            $order->total_amount = $request->total_amount;
            $order->observation = $request->observation;
            $order->provider_id = $request->provider;
            $order->sale_point_id = $request->sale_point;
            $order->save();

            $productsOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productOrder = new ProductOrder();
                $productOrder->quantity = $request->quantities[$key];
                $productOrder->unit_price = $request->unit_prices[$key];
                $productOrder->product_id = $product;
                $productOrder->order_id = $order->id;
                $productOrder->unity_id = $request->unities[$key];
                $productOrder->save();

                array_push($productsOrders, $productOrder);
            }

            $savedProductOrders = ProductOrder::where('order_id', $order->id)->get();
            if (empty($savedProductOrders)||sizeof($savedProductOrders)==0) {
                $order->delete();
            }




            // $folder = Folder::findOrFail($request->folder);

            // foreach ($request->upload_files as $key => $file) {
            //     $fileName = $currentFileType->wording . ' - ' . $postulant->last_name . ' ' . $postulant->first_name . '.' . $file->getClientOriginalExtension();
            //         $path = $file->storeAs($folder->path.'/' . $postulant->last_name . ' ' . $postulant->first_name, $fileName, 'public');
            //     $uploadFile = new UploadFile();
            //     $uploadFile->code = Str::random(10);
            //     $uploadFile->name = $path;
            //     $uploadFile->personalized_name = $request->personalized_name;
            //     $uploadFile->file_type_id = $request->$this->tankTruckAuthorizedFiles()->id;
            //     $uploadFile->folder_id = $folder->id;
            //     $uploadFile->save();
            // }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
            ], 200);
        } catch (Exception $e) {
            dd($e);
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
        $order = Order::with('provider')->with('productOrders')->findOrFail($id);
        $productsOrders = $order ? $order->productOrders : null; //ProductOrder::where('order_id', $order->id)->get();

        return new JsonResponse([
            'order' => $order,
            'datas' => ['productsOrders' => $productsOrders]
        ], 200);
    }

    public function edit($id)
    {
        $order = Order::with('provider')->with('productOrders')->findOrFail($id);
        $providers = Provider::with('person')->get();
        $products = Product::with('subCategory')->orderBy('wording')->get();
        $productsOrders = $order ? $order->productsOrders : null;

        return new JsonResponse([
            'order' => $order,
            'datas' => ['providers' => $providers, 'products' => $products, 'productsOrders' => $productsOrders]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $this->validate(
            $request,
            [
                // 'folder' => 'required',
                'sale_point' => 'required',
                'provider' => 'required',
                'reference' => 'required',
                'purchase_date' => 'required|date|date_format:Ymd|before:today',
                'delivery_date' => 'required|date|date_format:Ymd|after:purchase_date',
                'total_amount' => 'required',
                'observation' => 'max:255',
                'ordered_product' => 'required',
                'quantities' => 'required|min:0',
                'unit_prices' => 'required|min:0',
                'unities' => 'required',
                // 'upload_files' => 'required',
            ],
            [
                // 'folder.required' => "Le choix du dossier de destination des fichiers est obligatoire",
                'sale_point.required' => "Le choix du point de vente est obligatoire.",
                'provider.required' => "Le choix du fournisseur est obligatoire.",
                'reference.required' => "La référence du bon est obligatoire.",
                'purchase_date.required' => "La date du bon est obligatoire.",
                'purchase_date.date' => "La date du bon de commande est incorrecte.",
                'purchase_date.date_format' => "La date du bon de commande doit être sous le format : Année Mois Jour.",
                'purchase_date.before' => "La date du bon de commande doit être antérieure ou égale à aujourd'hui.",
                'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                'delivery_date.date' => "La date de livraison est incorrecte.",
                'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
                'total_amount.required' => "Le montant total est obligatoire.",
                'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                'ordered_product.required' => "Vous devez ajouter au moins un produit au panier.",
                'quantities.required' => "Les quantités sont obligatoires.",
                'quantities.min' => "Aucune des quantités ne peut être inférieur à 0.",
                'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                // 'upload_files.required' => "Veuillez charger au moins un fichier lié au bon de commande.",
            ]
        );

        try {
            // $order = new Order();
            $order->reference = $request->reference;
            $order->purchase_date   = $request->purchase_date;
            $order->delivery_date   = $request->delivery_date;
            $order->total_amount = $request->total_amount;
            $order->observation = $request->observation;
            $order->provider_id = $request->provider;
            $order->sale_point_id = $request->sale_point;
            $order->save();

            ProductOrder::where('order_id', $order->id)->delete();

            $productsOrders = [];
            foreach ($request->ordered_product as $key => $product) {
                $productOrder = new ProductOrder();
                $productOrder->quantity = $request->quantities[$key];
                $productOrder->unit_price = $request->unit_prices[$key];
                $productOrder->product_id = $product;
                $productOrder->order_id = $order->id;
                $productOrder->unity_id = $request->unities[$key];
                $productOrder->save();

                array_push($productsOrders, $productOrder);
            }

            $savedProductOrders = ProductOrder::where('order_id', $order->id)->get();
            if (empty($savedProductOrders)||sizeof($savedProductOrders)==0) {
                $order->delete();
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
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
        $order = Order::findOrFail($id);
        $productsOrders = $order ? $order->productsOrders : null;
        try {
            $order->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
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
