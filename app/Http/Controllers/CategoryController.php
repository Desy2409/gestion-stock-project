<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Repositories\CategoryRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public $categoryRepository;
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_CATEGORY_READ', Category::class);
        $categories = Category::orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['categories' => $categories]
        ], 200);
    }

    public function subCategoriesOfCategory($id)
    {
        $this->authorize('ROLE_CATEGORY_READ', Category::class);
        $subCategories = SubCategory::where('category_id', $id)->get();
        return new JsonResponse(['subCategories' => $subCategories]);
    }

    // Enregistrement d'une nouvelle catégorie
    public function store(Request $request)
    {
        $this->authorize('ROLE_CATEGORY_CREATE', Category::class);


        try {

            $validation = $this->validator('store', $request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                    //'message' => 'Des donnees sont invalides',
                ], 200);
            } else {
                $category = new Category();
                $category->reference = $request->reference;
                $category->wording = $request->wording;
                $category->description = $request->description;
                $category->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'category' => $category,
                    'success' => true,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => "Erreur survenue lors de l'enregistrement.",
            ], 200);
        }
    }

    // Mise à jour d'une catégorie
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_CATEGORY_UPDATE', Category::class);
        $category = Category::findOrFail($id);

        $existingCategories = Category::where('wording', $request->wording)->get();
        if (!empty($existingCategories) && sizeof($existingCategories) > 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingCategory' => $existingCategories[0],
                'message' => "La catégorie " . $existingCategories[0]->wording . " existe déjà"
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
                $category->reference = $request->reference;
                $category->wording = $request->wording;
                $category->description = $request->description;
                $category->save();

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'category' => $category,
                    'success' => $success,
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

    // Suppression d'une catégorie
    public function destroy($id)
    {
        $this->authorize('ROLE_CATEGORY_DELETE', Category::class);
        $category = Category::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($category->subCategories) || sizeof($category->subCategories) == 0) {
                // dd('delete');
                $category->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cette catégorie ne peut être supprimée car elle a servi dans des traitements.";
            }

            return new JsonResponse([
                'category' => $category,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de la suppression.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_CATEGORY_READ', Category::class);
        $category = Category::with('subCategories')->findOrFail($id);
        return new JsonResponse([
            'category' => $category
        ], 200);
    }

    public function categoryReports(Request $request)
    {
        $this->authorize('ROLE_CATEGORY_PRINT', Category::class);
        try {
            $categories = $this->categoryRepository->reportIncludeReference(Category::class, $request->selected_default_fields);
            return new JsonResponse(['datas' => ['categories' => $categories]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }

    protected function validator($mode, $data)
    {
        if ($mode == "store") {
            $errors = Validator::make(
                $data,
                [
                    'reference' => 'required|unique:categories',
                    'wording' => 'required|unique:categories|max:150',
                    'description' => 'max:255',
                ],
                [
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette réference a déjà été attribuée.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Cette catégorie existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            )->customMessages;
            // dd(array_values($errors));

            return array_values($errors);
        }
        if ($mode == "update") {
            return Validator::make(
                $data,
                [
                    'reference' => 'required',
                    'wording' => 'required|max:150',
                    'description' => 'max:255',
                ],
                [
                    'reference.required' => "La référence est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
    }
}
