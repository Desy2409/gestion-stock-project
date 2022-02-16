<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileTrait;
use App\Http\Traits\ProcessingTrait;
use App\Http\Traits\UtilityTrait;
use App\Mail\OrderValidationMail;
use App\Models\Category;
use App\Models\Folder;
use App\Models\Institution;
use App\Models\Order;
use App\Models\OrderRegister;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\SalePoint;
use App\Models\Unity;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Utils\FileUtil;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use UtilityTrait;
    use ProcessingTrait;
    use FileTrait;

    public $orderRepository;
    public $productRepository;
    protected $prefix;

    public function __construct(OrderRepository $orderRepository, ProductRepository $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->user = Auth::user();
        $this->prefix = Order::$code;
        $this->fileUtil = new FileUtil('Orders');
    }

    public function index()
    {
        // dd("OrderController");
        $this->authorize('ROLE_ORDER_READ', Order::class);
        $orders = Order::orderBy('created_at','desc')->get();
        // $orders = Order::orderBy('order_date')->with('')->get();
        $providers = Provider::with('person')->get();

        $categories = Category::orderBy('wording')->get();
        // $products = Product::orderBy('wording')->get();
        $unities = Unity::orderBy('wording')->get();
        $salePoints = SalePoint::orderBy('social_reason')->get();

        $lastOrderRegister = OrderRegister::latest()->first();

        $orderRegister = new OrderRegister();
        if ($lastOrderRegister) {
            $orderRegister->code = $this->formateNPosition($this->prefix, $lastOrderRegister->id + 1, 8);
        } else {
            $orderRegister->code = $this->formateNPosition($this->prefix, 1, 8);
        }
        $orderRegister->save();

        return new JsonResponse([
            'datas' => ['orders' => $orders, 'providers' => $providers, 'categories' => $categories, 'unities' => $unities, 'salePoints' => $salePoints]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_ORDER_READ', Order::class);
        $lastOrderRegister = OrderRegister::latest()->first();
        if ($lastOrderRegister) {
            $code = $this->formateNPosition($this->prefix, $lastOrderRegister->id + 1, 8);
        } else {
            $code = $this->formateNPosition($this->prefix, 1, 8);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function productsOfSelectedCategory($id)
    {
        $category = Category::findOrFail($id);
        $products = $this->productRepository->productsOfCategory($category->id);

        return new JsonResponse(['datas' => ['products' => $products]], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_ORDER_CREATE', Order::class);

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
                // dd($request->productOrders);
                $lastOrder = Order::latest()->first();

                $order = new Order();
                if ($lastOrder) {
                    $order->code = $this->formateNPosition($this->prefix, $lastOrder->id + 1, 8);
                } else {
                    $order->code = $this->formateNPosition($this->prefix, 1, 8);
                }
                $order->reference = $request->reference;
                $order->order_date = $request->order_date;
                $order->delivery_date = $request->delivery_date;
                $order->total_amount = $request->total_amount;
                $order->observation = $request->observation;
                $order->provider_id = $request->provider;
                $order->sale_point_id = $request->sale_point;
                $order->save();


                $productsOrders = [];
                foreach ($request->productOrders as $key => $productOrderLine) {
                    // dd($productOrderLine);
                    $productOrder = new ProductOrder();
                    $productOrder->quantity = $productOrderLine['quantity'];
                    $productOrder->unit_price = $productOrderLine['unit_price'];
                    $productOrder->product_id = $productOrderLine['product_id'];
                    $productOrder->order_id = $order->id;
                    $productOrder->unity_id = $productOrderLine['unity_id'];
                    $productOrder->save();

                    array_push($productsOrders, $productOrder);
                }
                // dd($productsOrders);

                $savedProductOrders = ProductOrder::where('order_id', $order->id)->get();
                if (empty($savedProductOrders) || sizeof($savedProductOrders) == 0) {
                    $order->delete();
                }

                // $folder = Folder::findOrFail($request->folder);

                // $check = $this->checkFileType($order);
                // if (!$check) {
                //     $success = false;
                //     $message = "Les formats de fichiers autorisés sont : pdf,docx et xls";
                //     return new JsonResponse(['success' => $success, 'message' => $message], 400);
                // } else {
                //     $this->storeFile($this->user, $order, $folder, $request->upload_files);
                // }

                foreach ($request->files as $key => $file) {
                    $fileUpload = $this->fileUtil->createFile($order, $file, $request->personalized_filename);
                }

                $file = $request->file('gauging_certificate');

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'order' => $order,
                    'success' => true,
                    'message' => $message,
                    'datas' => ['productsOrders' => $productsOrders],
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_ORDER_READ', Order::class);
        $order = Order::with('provider')->with('productOrders')->findOrFail($id);
        $productsOrders = $order ? $order->productOrders : null; //ProductOrder::where('order_id', $order->id)->get();

        $email = 'admin@admin.com';
        // $user = User::where('email', $email)->first();
        // Auth::login($user);
        // $credentials = ['email' => $user->email, 'password' => $user->password];

        // if (Auth::attempt($credentials)) {
        Mail::to($email)->send(new OrderValidationMail($order, $productsOrders));
        //     # code...
        // }

        return new JsonResponse([
            'order' => $order,
            'datas' => ['productsOrders' => $productsOrders]
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_ORDER_READ', Order::class);
        $order = Order::with('productOrders')->findOrFail($id);
        // $productOrders = ProductOrder::where('order_id', $order->id)->get();
        return new JsonResponse([
            'order' => $order,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_ORDER_UPDATE', Order::class);
        $order = Order::findOrFail($id);

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
                // $order = new Order();
                $order->reference = $request->reference;
                $order->order_date   = $request->order_date;
                $order->delivery_date   = $request->delivery_date;
                $order->total_amount = $request->total_amount;
                $order->observation = $request->observation;
                $order->provider_id = $request->provider;
                $order->sale_point_id = $request->sale_point;
                $order->save();

                ProductOrder::where('order_id', $order->id)->delete();

                $productsOrders = [];
                foreach ($request->productOrders as $key => $product) {
                    $productOrder = new ProductOrder();
                    $productOrder->quantity = $product["quantity"];
                    $productOrder->unit_price = $product["unit_price"];
                    $productOrder->product_id = $product["product_id"];
                    $productOrder->order_id = $order->id;
                    $productOrder->unity_id = $product["unity_id"];
                    $productOrder->save();

                    array_push($productsOrders, $productOrder);
                }

                $savedProductOrders = ProductOrder::where('order_id', $order->id)->get();
                if (empty($savedProductOrders) || sizeof($savedProductOrders) == 0) {
                    $order->delete();
                }

                foreach ($request->files as $key => $file) {
                    $fileUpload = $this->fileUtil->createFile($order, $file, $request->personalized_filename);
                }

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'order' => $order,
                    'success' => true,
                    'message' => $message,
                    'datas' => ['productsOrders' => $productsOrders],
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_ORDER_DELETE', Order::class);
        $order = Order::findOrFail($id);
        $productsOrders = $order ? $order->productsOrders : null;
        try {
            $success = false;
            $message = "";
            if (
                empty($productsOrders) || sizeof($productsOrders) == 0 &&
                empty($order->purchases) || sizeof($order->purchases) == 0
            ) {
                // dd('delete');
                $order->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette commande ne peut être supprimée car elle a servi dans des traitements.";
            }

            return new JsonResponse([
                'order' => $order,
                'success' => $success,
                'message' => $message,
                'datas' => ['productsOrders' => $productsOrders],
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function orderProcessing($id, $action)
    {
        try {
            $this->processing(Order::class, $id, $action);

            if ($action == 'validate') {
                $message = "Commande validée avec succès.";
            }
            if ($action == 'reject') {
                $message = "Commande rejetée avec succès.";
            }
            return new JsonResponse([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            if ($action == 'validate') {
                $message = "Erreur survenue lors de la validation de la commande.";
            }
            if ($action == 'reject') {
                $message = "Erreur survenue lors de l'annulation de la commande.";
            }
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function orderReports(Request $request)
    {
        $this->authorize('ROLE_ORDER_PRINT', Order::class);
        try {
            $orders = $this->orderRepository->orderReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['orders' => $orders]], 200);
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
                    // 'folder' => 'required',
                    'sale_point' => 'required',
                    'provider' => 'required',
                    'reference' => 'required',
                    'order_date' => 'required|date|before:today', //|date_format:Ymd
                    'delivery_date' => 'required|date|after:order_date', //|date_format:Ymd
                    // 'total_amount' => 'required',
                    'observation' => 'max:255',
                    'productOrders' => 'required',
                    // 'upload_files' => 'required',
                ],
                [
                    // 'folder.required' => "Le choix du dossier de destination des fichiers est obligatoire",
                    'sale_point.required' => "Le choix du point de vente est obligatoire.",
                    'provider.required' => "Le choix du fournisseur est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'order_date.required' => "La date du bon est obligatoire.",
                    'order_date.date' => "La date du bon de commande est incorrecte.",
                    // 'order_date.date_format' => "La date du bon de commande doit être sous le format : Année Mois Jour.",
                    'order_date.before' => "La date du bon de commande doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison est incorrecte.",
                    // 'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
                    'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'productOrders.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'upload_files.required' => "Veuillez charger au moins un fichier lié au bon de commande.", Ok testons Amdi !!! tu parles trop !! Viens tester !! eeeeeh Dieu h
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    // 'folder' => 'required',
                    'sale_point' => 'required',
                    'provider' => 'required',
                    'reference' => 'required',
                    'order_date' => 'required|date|before:today',
                    'delivery_date' => 'required|date|after:order_date',
                    'total_amount' => 'required',
                    'observation' => 'max:255',
                    'productOrders' => 'required',
                    // 'quantity' => 'required|min:0',
                    // 'unit_prices' => 'required|min:0',
                    // 'unities' => 'required',
                    // 'upload_files' => 'required',
                ],
                [
                    // 'folder.required' => "Le choix du dossier de destination des fichiers est obligatoire",
                    'sale_point.required' => "Le choix du point de vente est obligatoire.",
                    'provider.required' => "Le choix du fournisseur est obligatoire.",
                    'reference.required' => "La référence du bon est obligatoire.",
                    'order_date.required' => "La date du bon est obligatoire.",
                    'order_date.date' => "La date du bon de commande est incorrecte.",
                    // 'order_date.date_format' => "La date du bon de commande doit être sous le format : Année Mois Jour.",
                    'order_date.before' => "La date du bon de commande doit être antérieure ou égale à aujourd'hui.",
                    'delivery_date.required' => "La date de livraison prévue est obligatoire.",
                    'delivery_date.date' => "La date de livraison est incorrecte.",
                    // 'delivery_date.date_format' => "La date livraison doit être sous le format : Année Mois Jour.",
                    'delivery_date.after' => "La date livraison doit être ultérieure à la date du bon de commande.",
                    'total_amount.required' => "Le montant total est obligatoire.",
                    'observation.max' => "L'observation ne doit pas dépasser 255 caractères.",
                    'productOrders.required' => "Vous devez ajouter au moins un produit au panier.",
                    // 'quantity.required' => "Les quantités sont obligatoires.",
                    // 'quantity.min' => "Aucune des quantités ne peut être inférieur à 0.",
                    // 'unit_prices.required' => "Les prix unitaires sont obligatoires.",
                    // 'unit_prices.min' => "Aucun des prix unitaires ne peut être inférieur à 0.",
                    // 'unities.required' => "Veuillez définir des unités à tous les produits ajoutés.",
                    // 'upload_files.required' => "Veuillez charger au moins un fichier lié au bon de commande.",
                ]
            );
        }
    }
}
