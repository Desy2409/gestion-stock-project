<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Repositories\SubCategoryRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public $subCategoryRepository;

    public function __construct(SubCategoryRepository $subCategoryRepository)
    {
        $this->subCategoryRepository = $subCategoryRepository;
    }
    public function index()
    {
        $this->authorize('ROLE_SUB_CATEGORY_READ', SubCategory::class);
        $categories = Category::orderBy('wording')->get();
        $subCategories = SubCategory::with('category')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['categories' => $categories, 'subCategories' => $subCategories]
        ], 200);
    }

    // Enregistrement d'une nouvelle sous-catégorie
    public function store(Request $request)
    {
        $this->authorize('ROLE_SUB_CATEGORY_CREATE', SubCategory::class);
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
            // dd($request->category);
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
        $this->authorize('ROLE_SUB_CATEGORY_UPDATE', SubCategory::class);
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

        $existingSubCategories = SubCategory::where('wording', $request->wording)->get();
        if (!empty($existingSubCategories) && sizeof($existingSubCategories) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingSubCategory' => $existingSubCategories[0],
                'message' => "La sous-catégorie " . $existingSubCategories[0]->wording . " existe déjà"
            ], 200);
        }

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
        $this->authorize('ROLE_SUB_CATEGORY_DELETE', SubCategory::class);
        $subCategory = SubCategory::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($subCategory->products) || sizeof($subCategory->products) == 0) {
                // dd('delete');
                $subCategory->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette sous-catégorie ne peut être supprimée car elle a servi dans des traitements.";
            }

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

    public function show($id)
    {
        $this->authorize('ROLE_SUB_CATEGORY_READ', SubCategory::class);
        $subCategory = SubCategory::findOrFail($id);
        return new JsonResponse([
            'subCategory' => $subCategory
        ], 200);
    }


    public function subCategoryReports(Request $request)
    {
        $this->authorize('ROLE_SUB_CATEGORY_PRINT', SubCategory::class);
        try {
            $subCategories = $this->subCategoryRepository->subCategoryReport($request->selected_default_fields,$request->selected_parent_fields);
            // $subCategories = $this->subCategoryRepository->subCategoryReport(["id"],['id','wording']);
            return new JsonResponse(['datas' => ['subCategories' => $subCategories]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
