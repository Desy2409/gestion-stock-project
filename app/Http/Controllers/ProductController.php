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
        $products = Product::with('subCategory')->orderBy('wording')->get();
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
                'sub_category' => 'required',
                'reference' => 'required|unique:products',
                'wording' => 'required|unique:products',
                'description' => 'max:255',
            ],
            [
                'sub_category.required' => "La sous-catégorie du produit est obligatoire.",
                'reference.required' => "Le libellé du produit est obligatoire.",
                'reference.unique' => "Cette référence a déjà été attribuée.",
                'wording.required' => "La référence est obligatoire.",
                'wording.unique' => "Ce produit existe déjà.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
            ]
        );

        try {
            $products = Product::all();
            $product = new Product();
            $product->code = $this->formateNPosition('', sizeof($products) + 1, 8);
            $product->reference = $request->reference;
            $product->wording = $request->wording;
            $product->description = $request->description;
            $product->sub_category_id = $request->sub_category;
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
        $product = Product::with('subCategory')->findOrFail($id);
        return new JsonResponse(['product' => $product], 200);
    }

    public function edit($id)
    {
        $product = Product::with('subCategory')->findOrFail($id);
        $subCategories = SubCategory::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['product' => $product, 'subCategories' => $subCategories]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $product = Product::with('subCategory')->findOrFail($id);
        $request->validate(
            [
                'sub_category' => 'required',
                'reference' => 'required',
                'wording' => 'required',
                'description' => 'max:255',
            ],
            [
                'sub_category.required' => "La sous-catégorie du produit est obligatoire.",
                'reference.required' => "Le libellé du produit est obligatoire.",
                'wording.required' => "La référence est obligatoire.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
            ]
        );

        $existingProductsOnReference = Product::where('reference', $request->reference)->get();
        if (!empty($existingProductsOnReference) && sizeof($existingProductsOnReference) > 1) {
            $success = false;
            return new JsonResponse([
                'existingProductOnReference' => $existingProductsOnReference[0],
                'success' => $success,
                'message' => "La référence " . $existingProductsOnReference[0]->reference . " a déjà été attribuée."
            ], 400);
        }

        $existingProductsOnWording = Product::where('wording', $request->wording)->get();
        if (!empty($existingProductsOnWording) && sizeof($existingProductsOnWording) > 1) {
            $success = false;
            return new JsonResponse([
                'existingProduct' => $existingProductsOnWording[0],
                'success' => $success,
                'message' => "Le produit " . $existingProductsOnWording[0]->wording . " existe déjà."
            ], 400);
        }

        try {
            $product->reference = $request->reference;
            $product->wording = $request->wording;
            $product->description = $request->description;
            $product->sub_category_id = $request->sub_category;
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
        $product = Product::with('subCategory')->findOrFail($id);
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
