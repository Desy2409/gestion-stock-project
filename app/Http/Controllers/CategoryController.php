<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = Category::orderBy('wording')->get();
        return [
            'categories' => $categories,
        ];
    }

    // Enregistrement d'une nouvelle catégorie
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:categories|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette catégorie existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {

            return Category::create($request->all());
            // $category = new Category();
            // $category->code = Str::random(10);
            // $category->wording = $request->wording;
            // $category->description = $request->description;
            // $category->save();

            // Session::flash('success', "Enregistrement effectué avec succès.");
            // return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de l'enregistrement.");
        }
    }

    // Mise à jour d'une catégorie
    public function update(Request $request, $id)
    {

        $category = Category::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cette catégorie existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {

            $category->update($request->all());
            return $category;
            // $category->wording = $request->wording;
            // $category->description = $request->description;
            // $category->save();

            // Session::flash('success', "Modification effectuée avec succès.");
            // return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la modification.");
        }
    }

    // Suppression d'une catégorie
    public function destroy($id)
    {
        // $category = Category::findOrFail($id);
        try {

            return Category::destroy($id);
            // $category->delete();

            // Session::flash('destroy', 'Suppression effectuée avec succès');
            // return back();
        } catch (Exception $e) {
            Session::flash('danger', "Erreur survenue lors de la suppression.");
        }
    }
}
