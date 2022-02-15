<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Repositories\SubCategoryRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $subCategories = SubCategory::orderBy('created_at','desc')->with('category')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['categories' => $categories, 'subCategories' => $subCategories]
        ], 200);
    }

    // Enregistrement d'une nouvelle sous-catégorie
    public function store(Request $request)
    {
        $this->authorize('ROLE_SUB_CATEGORY_CREATE', SubCategory::class);

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
                $subCategory = new SubCategory();
                $subCategory->reference = $request->reference;
                $subCategory->wording = $request->wording;
                $subCategory->description = $request->description;
                $subCategory->category_id = $request->category;
                $subCategory->save();
                // dd($request->category);

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'subCategory' => $subCategory,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    // Mise à jour d'une sous-catégorie
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_SUB_CATEGORY_UPDATE', SubCategory::class);
        $subCategory = SubCategory::findOrFail($id);

        $existingSubCategories = SubCategory::where('wording', $request->wording)->get();
        if (!empty($existingSubCategories) && sizeof($existingSubCategories) > 1) {
            return new JsonResponse([
                'success' => false,
                'existingSubCategory' => $existingSubCategories[0],
                'message' => "La sous-catégorie " . $existingSubCategories[0]->wording . " existe déjà"
            ], 200);
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
                $subCategory->reference = $request->reference;
                $subCategory->wording = $request->wording;
                $subCategory->description = $request->description;
                $subCategory->category_id = $request->category;
                $subCategory->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'subCategory' => $subCategory,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
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
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
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
            $subCategories = $this->subCategoryRepository->oneJoinReport(SubCategory::class, 'sub_categories', 'categories', 'sub', 'cat', 'category_id', $request->child_selected_fields, $request->parent_selected_fields);
            return new JsonResponse(['datas' => ['subCategories' => $subCategories]], 200);
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
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
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
        }
    }
}
