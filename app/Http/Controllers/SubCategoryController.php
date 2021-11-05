<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('wording')->get();
        $subCategories = SubCategory::with('category')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['categories' => $categories, 'subCategories' => $subCategories]
        ], 200);
    }

    // Enregistrement d'une nouvelle sous-catégorie
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'category' => 'required',
                'reference' => 'required|unique:sub_categories',
                'wording' => 'required|unique:sub_categories|max:150',
                'description' => 'max:255'
            ],
            [
                'category.required' => "La catégorie est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'reference.unique' => "Cette réference a déjà été attribuée déjà.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette sous-catégorie existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $subCategory = new SubCategory();
            $subCategory->reference = $request->reference;
            $subCategory->wording = $request->wording;
            $subCategory->description = $request->description;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'subCategory' => $subCategory,
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

    // Mise à jour d'une sous-catégorie
    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $this->validate(
            $request,
            [
                'category' => 'required',
                'reference' => 'required',
                'wording' => 'required|max:150',
                'description' => 'max:255'
            ],
            [
                'category.required' => "La catégorie est obligatoire.",
                'reference.required' => "La référence est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $subCategory->reference = $request->reference;
            $subCategory->wording = $request->wording;
            $subCategory->description = $request->description;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'subCategory' => $subCategory,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    // Suppression d'une sous-catégorie
    public function destroy($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        try {
            $subCategory->delete();

            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'subCategory' => $subCategory,
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        return new JsonResponse([
            'subCategory' => $subCategory
        ], 200);
    }
}
