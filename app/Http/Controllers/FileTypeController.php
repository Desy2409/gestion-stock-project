<?php

namespace App\Http\Controllers;

use App\Models\FileType;
use App\Repositories\FileTypeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FileTypeController extends Controller
{

    public $fileTypeRepository;

    public function __construct(FileTypeRepository $fileTypeRepository)
    {
        $this->fileTypeRepository = $fileTypeRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_FILE_TYPE_READ', FileType::class);
        $fileTypes = FileType::orderBy('wording')->get();
        $extensions = FileType::orderBy('extension')->get();
        return new JsonResponse(['datas' => ['fileTypes' => $fileTypes, 'extensions' => $extensions]], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_FILE_TYPE_CREATE', FileType::class);

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
                $fileType = new FileType();
                $fileType->code = Str::random(10);
                $fileType->wording = $request->wording;
                $fileType->description = $request->description;
                $fileType->max_size = $request->max_size;
                $fileType->authorized_files = implode(',', $request->authorized_files);
                $fileType->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'fileType' => $fileType,
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

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_FILE_TYPE_UPDATE', FileType::class);
        $fileType = FileType::findOrFail($id);
        $existingFileTypes = FileType::where('wording', $request->wording)->get();
        if (!empty($existingFileTypes) && sizeof($existingFileTypes) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingFileType' => $existingFileTypes[0],
                'message' => "Le type de fichier " . $existingFileTypes[0]->wording . " existe déjà"
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
                $fileType->wording = $request->wording;
                $fileType->description = $request->description;
                $fileType->max_size = $request->max_size;
                $fileType->authorized_files = implode(',', $request->authorized_files);
                $fileType->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'fileType' => $fileType,
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

    public function destroy($id)
    {
        $this->authorize('ROLE_FILE_TYPE_DELETE', FileType::class);
        $fileType = FileType::findOrFail($id);
        try {
            $fileType->delete();
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'fileType' => $fileType,
                'success' => true,
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
        $this->authorize('ROLE_FILE_TYPE_READ', FileType::class);
        $fileType = FileType::findOrFail($id);
        return new JsonResponse([
            'fileType' => $fileType
        ], 200);
    }

    public function fileTypeReports(Request $request)
    {
        $this->authorize('ROLE_FILE_TYPE_PRINT', FileType::class);
        try {
            $fileTypes = $this->fileTypeRepository->oneJoinReport(FileType::class, 'file_types', 'extensions',  'filT', 'ext', 'extension_id', $request->child_selected_fields, $request->parent_selected_fields);
            return new JsonResponse(['datas' => ['fileTypes' => $fileTypes]], 200);
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
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
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
        }
    }
}
