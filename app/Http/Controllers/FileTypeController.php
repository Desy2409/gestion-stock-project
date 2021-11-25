<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use App\Models\FileType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileTypeController extends Controller
{
    public function index()
    {
        $this->authorize('ROLE_FILE_TYPE_READ', FileType::class);
        $fileTypes = FileType::orderBy('wording')->get();
        $extensions = Extension::orderBy('extension')->get();
        return new JsonResponse(['datas' => ['fileTypes' => $fileTypes, 'extensions' => $extensions]], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_FILE_TYPE_CREATE', FileType::class);
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:file_types|max:50',
                'max_size' => 'numeric',
                'authorized_files' => 'required',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce type de fichier existe déjà.",
                'wording.max' => "Le lbellé ne doit pas dépasser 50 caractères.",
                'authorized_files.required' => "Vous devez choisir au moins une extension autorisée.",
                'max_size.numeric' => "La taille autorisée doit être un entier supérieur à 0."
            ]
        );

        try {
            $fileType = new FileType();
            $fileType->code = Str::random(10);
            $fileType->wording = $request->wording;
            $fileType->description = $request->description;
            $fileType->max_size = $request->max_size;
            $fileType->authorized_files = implode(',', $request->authorized_files);
            $fileType->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'fileType' => $fileType,
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

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_FILE_TYPE_UPDATE', FileType::class);
        $fileType = FileType::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:50',
                'max_size' => 'numeric',
                'authorized_files' => 'required',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le lbellé ne doit pas dépasser 50 caractères.",
                'authorized_files.required' => "Vous devez choisir au moins une extension autorisée.",
                'max_size.numeric' => "La taille autorisée doit être un entier supérieur à 0."
            ]
        );

        try {
            $fileType->wording = $request->wording;
            $fileType->description = $request->description;
            $fileType->max_size = $request->max_size;
            $fileType->authorized_files = implode(',', $request->authorized_files);
            $fileType->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'fileType' => $fileType,
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

    public function destroy($id)
    {
        $this->authorize('ROLE_FILE_TYPE_DELETE', FileType::class);
        $fileType = FileType::findOrFail($id);
        try {
            $fileType->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'fileType' => $fileType,
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
        $this->authorize('ROLE_FILE_TYPE_READ', FileType::class);
        $fileType = FileType::findOrFail($id);
        return new JsonResponse([
            'fileType' => $fileType
        ], 200);
    }
}
