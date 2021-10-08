<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SubCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::orderBy('wording')->get();
        return [
            'products' => $products,
        ];
    }

   /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         $subCategories = SubCategory::orderBy('wording')->get();
        return [
            'subCategories' => $subCategories,
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
        $request->validate(
            [
                'sub_category'=>'required',
                'wording' => 'required|unique:products',
                'description' => 'max:255',
                'price' => 'required|min:0',
                'unity' => 'required',
            ],
            [
                'sub_category.required' => "La sous-catégorie du produit est obligatoire.",
                'wording.required' => "Le libellé du produit est obligatoire.",
                'wording.unique' => "Ce produit existe déjà.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'price.required' => "Le prix du produit est obligatoire.",
                'price.min' => "Le prix du produit ne peut être inférieur à 0.",
                'unity.required' => "L'unité du produit est obligatoire.",
            ]
        );
        try {
            $product = new Product();
            $product->code = Str::random(10);
            $product->reference = '000001';
            $product->wording = $request->wording;
            $product->price = $request->price;
            $product->unity = $request->unity;
            $product->description = $request->description;
            $product->sub_category_id = $request->sub_category;

            // dd($product);
            $product->save();

            return $product;
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
        return Product::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $subCategories = SubCategory::orderBy('wording')->get();
        return [
            'subCategories' => $subCategories,
            'product' => $product,
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
        $product = Product::findOrFail($id);
        $request->validate(
            [
                'sub_category'=>'required',
                'wording' => 'required',
                'description' => 'max:255',
                'price' => 'required|min:0',
                'unity' => 'required',
            ],
            [
                'sub_category.required' => "La sous-catégorie du produit est obligatoire.",
                'wording.required' => "Le libellé du produit est obligatoire.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'price.required' => "Le prix du produit est obligatoire.",
                'price.min' => "Le prix du produit ne peut être inférieur à 0.",
                'unity.required' => "L'unité du produit est obligatoire.",
            ]
        );
        try {
            $product->wording = $request->wording;
            $product->prise = $request->prise;
            $product->unity = $request->unity;
            $product->description = $request->description;
            $product->sub_category_id = $request->sub_category;
            $product->save();
            
            return $product;
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
        $product = Product::findOrFail($id);
        try {
            $product->delete();
            return $product;
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppression.");
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
