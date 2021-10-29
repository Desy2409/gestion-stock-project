<?php

namespace App\Http\Controllers;

use App\Http\Traits\UtilityTrait;
use App\Models\Product;
use App\Models\StockType;
use App\Models\SubCategory;
use App\Models\Unity;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use UtilityTrait;

    public function index()
    {
        $products = Product::with('subCategory')->with('unity')->with('stockType')->orderBy('wording')->get();
        $unities = Unity::orderBy('wording')->get();
        $subCategories = SubCategory::orderBy('wording')->get();
        $stockTypes = StockType::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['products' => $products, 'unities' => $unities, 'subCategories' => $subCategories, 'stockTypes' => $stockTypes]
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'unity' => 'required',
                'sub_category' => 'required',
                'reference' => 'required|unique:products',
                'wording' => 'required|unique:products',
                'description' => 'max:255',
                'price' => 'required|min:0',
            ],
            [
                'unity.required' => "L'unité est obligatoire.",
                'sub_category.required' => "La sous-catégorie du produit est obligatoire.",
                'reference.required' => "Le libellé du produit est obligatoire.",
                'reference.unique' => "Cette référence a déjà été attribuée.",
                'wording.required' => "La référence est obligatoire.",
                'wording.unique' => "Ce produit existe déjà.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'price.required' => "Le prix du produit est obligatoire.",
                'price.min' => "Le prix du produit ne peut être inférieur à 0.",
            ]
        );

        try {
            $products = Product::all();
            $product = new Product();
            $product->code = $this->formateNPosition('', sizeof($products) + 1, 8);
            $product->reference = $request->reference;
            $product->wording = $request->wording;
            $product->price = $request->price;
            $product->description = $request->description;
            $product->unity_id = $request->unity;
            $product->sub_category_id = $request->sub_category;
            $product->stock_type_id = $request->stock_type;
            $product->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'product' => $product,
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
        $product = Product::with('subCategory')->with('unity')->with('stockType')->findOrFail($id);
        return new JsonResponse(['product' => $product], 200);
    }

    public function edit($id)
    {
        $product = Product::with('subCategory')->with('unity')->with('stockType')->findOrFail($id);
        $unities = Unity::orderBy('wording')->get();
        $subCategories = SubCategory::orderBy('wording')->get();
        $stockTypes = StockType::orderBy('wording')->get();        return new JsonResponse([
            'datas' => ['product' => $product, 'unities' => $unities, 'subCategories' => $subCategories, 'stockTypes' => $stockTypes]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $product = Product::with('subCategory')->with('unity')->with('stockType')->findOrFail($id);
        $request->validate(
            [
                'unity' => 'required',
                'sub_category' => 'required',
                'reference' => 'required|unique:products',
                'wording' => 'required|unique:products',
                'description' => 'max:255',
                'price' => 'required|min:0',
            ],
            [
                'unity.required' => "L'unité est obligatoire.",
                'sub_category.required' => "La sous-catégorie du produit est obligatoire.",
                'reference.required' => "Le libellé du produit est obligatoire.",
                'reference.unique' => "Cette référence a déjà été attribuée.",
                'wording.required' => "La référence est obligatoire.",
                'wording.unique' => "Ce produit existe déjà.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'price.required' => "Le prix du produit est obligatoire.",
                'price.min' => "Le prix du produit ne peut être inférieur à 0.",
            ]
        );

        try {
            $product->reference = $request->reference;
            $product->wording = $request->wording;
            $product->price = $request->price;
            $product->description = $request->description;
            $product->unity_id = $request->unity;
            $product->sub_category_id = $request->sub_category;
            $product->stock_type_id = $request->stock_type;
            $product->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'product' => $product,
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
        $product = Product::with('subCategory')->with('unity')->with('stockType')->findOrFail($id);
        try {
            $product->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'product' => $product,
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
}
