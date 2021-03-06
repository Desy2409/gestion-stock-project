<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\ProductPricing;
use App\Models\ProductRegister;
use App\Models\StockType;
use App\Models\SubCategory;
use App\Models\Unity;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use UtilityTrait;

    public $productRepository;
    protected $prefix;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        // $this->prefix = Product::$code;
    }

    public function index()
    {
        $this->authorize('ROLE_PRODUCT_READ', Product::class);
        $products = Product::orderBy('created_at', 'desc')->with('subCategory')->orderBy('wording')->get();
        $subCategories = SubCategory::orderBy('wording')->get();

        $lastProductRegister = ProductRegister::latest()->first();

        $productRegister = new ProductRegister();
        if ($lastProductRegister) {
            $productRegister->code = $this->formateNPosition(ProductRegister::class, $lastProductRegister->id+1);
        } else {
            $productRegister->code = $this->formateNPosition(ProductRegister::class, 1);
        }
        $productRegister->save();

        return new JsonResponse([
            'datas' => ['products' => $products, 'subCategories' => $subCategories]
        ], 200);
    }

    public function showNextCode()
    {
        $this->authorize('ROLE_PRODUCT_READ', Product::class);
        $lastProductRegister = ProductRegister::latest()->first();
        if ($lastProductRegister) {
            $code = $this->formateNPosition(ProductRegister::class, $lastProductRegister->id + 1);
        } else {
            $code = $this->formateNPosition(ProductRegister::class, 1);
        }

        return new JsonResponse([
            'code' => $code
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_PRODUCT_CREATE', Product::class);

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
                $lastProduct = Product::latest()->first();

                $product = new Product();
                if ($lastProduct) {
                    $product->code = $this->formateNPosition(Product::class, $lastProduct->id + 1);
                } else {
                    $product->code = $this->formateNPosition(Product::class, 1);
                }
                $product->reference = $request->reference;
                $product->wording = $request->wording;
                $product->description = $request->description;
                $product->sub_category_id = $request->sub_category;
                $product->save();

                $message = "Enregistrement effectu?? avec succ??s.";
                return new JsonResponse([
                    'product' => $product,
                    'success' => true,
                    'message' => $message,
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
        $this->authorize('ROLE_PRODUCT_READ', Product::class);
        $product = Product::with('subCategory')->findOrFail($id);
        return new JsonResponse(['product' => $product], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_PRODUCT_READ', Product::class);
        $product = Product::with('subCategory')->findOrFail($id);
        $subCategories = SubCategory::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['product' => $product, 'subCategories' => $subCategories]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_PRODUCT_UPDATE', Product::class);
        $product = Product::with('subCategory')->findOrFail($id);

        $existingProductsOnReference = Product::where('reference', $request->reference)->get();
        if (!empty($existingProductsOnReference) && sizeof($existingProductsOnReference) > 1) {
            $success = false;
            return new JsonResponse([
                'existingProductOnReference' => $existingProductsOnReference[0],
                'success' => $success,
                'message' => "La r??f??rence " . $existingProductsOnReference[0]->reference . " a d??j?? ??t?? attribu??e."
            ], 400);
        }

        $existingProductsOnWording = Product::where('wording', $request->wording)->get();
        if (!empty($existingProductsOnWording) && sizeof($existingProductsOnWording) > 1) {
            $success = false;
            return new JsonResponse([
                'existingProduct' => $existingProductsOnWording[0],
                'success' => $success,
                'message' => "Le produit " . $existingProductsOnWording[0]->wording . " existe d??j??."
            ], 400);
        }

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
                $product->reference = $request->reference;
                $product->wording = $request->wording;
                $product->description = $request->description;
                $product->sub_category_id = $request->sub_category;
                $product->save();

                $message = "Modification effectu??e avec succ??s.";
                return new JsonResponse([
                    'product' => $product,
                    'success' => true,
                    'message' => $message,
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
        $this->authorize('ROLE_PRODUCT_DELETE', Product::class);
        $product = Product::with('subCategory')->findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (
                empty($product->productPurchaseOrders) || sizeof($product->productPurchaseOrders) == 0 &&
                empty($product->productOrders) || sizeof($product->productOrders) == 0 &&
                empty($product->productPurchases) || sizeof($product->productPurchases) == 0 &&
                empty($product->productDeliveryNotes) || sizeof($product->productDeliveryNotes) == 0 &&
                empty($product->productPurchaseOrders) || sizeof($product->productPurchaseOrders) == 0 &&
                empty($product->productSales) || sizeof($product->productSales) == 0 &&
                empty($product->productClientDeliveryNotes) || sizeof($product->productClientDeliveryNotes) == 0 &&
                empty($product->productsTransfersDemandsLines) || sizeof($product->productsTransfersDemandsLines) == 0 &&
                empty($product->productsTransfersLines) || sizeof($product->productsTransfersLines) == 0
            ) {
                // dd('delete');
                $product->delete();
                $success = true;
                $message = "Suppression effectu??e avec succ??s.";
            } else {
                // dd('not delete');
                $message = "Ce produit ne peut ??tre supprim?? car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'product' => $product,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    /**
     * Search for a wording.
     *
     * @param  str  $wording
     * @return \Illuminate\Http\Response
     */
    public function search($wording)
    {
        return Product::where('wording', 'like', '%' . $wording . '%')->get();
    }


    public function pricing(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->validate(
            $request,
            [
                'price' => 'required'
            ],
            [
                'price.required' => "Le prix du produit est obligatoire."
            ]
        );

        try {
            $productPricing = new ProductPricing();
            $productPricing->price = $request->price;
            $productPricing->product_id = $product->id;
            $productPricing->save();

            $message = "Prix d??fini avec succ??s.";
            return new JsonResponse([
                'product' => $product,
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la d??finition de prix.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 400);
        }
    }

    public function productReports(Request $request)
    {
        $this->authorize('ROLE_PRODUCT_PRINT', Product::class);
        try {
            $products = $this->productRepository->oneJoinReport(Product::class, 'products', 'sub_categories',  'prod', 'sub', 'sub_category_id', $request->child_selected_fields, $request->parent_selected_fields);
            return new JsonResponse(['datas' => ['products' => $products]], 200);
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
                    'sub_category' => 'required',
                    'reference' => 'required|unique:products',
                    'wording' => 'required|unique:products',
                    'description' => 'max:255',
                ],
                [
                    'sub_category.required' => "La sous-cat??gorie du produit est obligatoire.",
                    'reference.required' => "Le libell?? du produit est obligatoire.",
                    'reference.unique' => "Cette r??f??rence a d??j?? ??t?? attribu??e.",
                    'wording.required' => "La r??f??rence est obligatoire.",
                    'wording.unique' => "Ce produit existe d??j??.",
                    'description.max' => "La description ne doit pas d??passer 255 caract??res.",
                ]
            );
        }
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'sub_category' => 'required',
                    'reference' => 'required',
                    'wording' => 'required',
                    'description' => 'max:255',
                ],
                [
                    'sub_category.required' => "La sous-cat??gorie du produit est obligatoire.",
                    'reference.required' => "Le libell?? du produit est obligatoire.",
                    'wording.required' => "La r??f??rence est obligatoire.",
                    'description.max' => "La description ne doit pas d??passer 255 caract??res.",
                ]
            );
        }
    }
}
