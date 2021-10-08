<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SubSubCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = Category::orderBy('wording')->get();
        $subCategories = SubCategory::orderBy('wording')->get();
        return [
            'categories' => $categories,
            'subCategories' => $subCategories
        ];
    }

    // Enregistrement d'une nouvelle sous-catégorie
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:categories|max:150',
                'description' => 'max:255',
                'category' => 'required'
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette sous-catégorie existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'category.required' => "La catégorie est obligatoire."
            ]
        );

        try {

            return SubCategory::create($request->all());
            // $subCategory = new SubCategory();
            // $subCategory->code = Str::random(10);
            // $subCategory->wording = $request->wording;
            // $subCategory->description = $request->description;
            // $subCategory->category_id = $request->category;
            // $subCategory->save();

            // Session::flash('success', "Enregistrement effectué avec succès.");
            // return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de l'enregistrement.");
        }
    }

    // Mise à jour d'une sous-catégorie
    public function update(Request $request, $id)
    {

        $subCategory = SubCategory::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:150',
                'description' => 'max:255',
                'category' => 'required'
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette sous-catégorie existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères.",
                'category.required' => "La catégorie est obligatoire."
            ]
        );

        try {

            $subCategory->update($request->all());
            return $subCategory;
            // $subCategory->wording = $request->wording;
            // $subCategory->description = $request->description;
            // $subCategory->category_id = $request->category;
            // $subCategory->save();

            Session::flash('success', "Modification effectuée avec succès.");
            return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la modification.");
        }
    }

    // Suppression d'une sous-catégorie
    public function destroy($id)
    {
        // $subCategory = SubCategory::findOrFail($id);
        try {

            return SubCategory::destroy($id);
            // $subCategory->delete();

            // Session::flash('destroy', 'Suppression effectuée avec succès');
            // return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppression.");
        }
    }
}
