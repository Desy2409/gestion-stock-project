<?php

namespace App\Http\Controllers;

use App\Models\Extension;
use App\Repositories\ExtensionRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExtensionController extends Controller
{

    public $extensionRepository;

    public function __construct(ExtensionRepository $extensionRepository)
    {
        $this->extensionRepository = $extensionRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_EXTENSION_READ', Extension::class);
        $extensions = Extension::orderBy('extension')->get();
        return new JsonResponse([
            'datas' => ['extensions' => $extensions]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_EXTENSION_CREATE', Extension::class);

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
                $extension = new Extension();
                $extension->code = Str::lower('.' . $request->extension);
                $extension->extension = strtoupper($request->extension);
                $extension->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'extension' => $extension,
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
        $this->authorize('ROLE_EXTENSION_UPDATE', Extension::class);
        $extension = Extension::findOrfail($id);
        $existingExtensions = Extension::where('extension', $request->extension)->get();
        if (!empty($existingExtensions) && sizeof($existingExtensions) >= 1) {
            return new JsonResponse([
                'success' => false,
                'existingExtension' => $existingExtensions[0],
                'message' => "L'extension " . $existingExtensions[0]->extension . " existe déjà"
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
                $extension->extension = strtoupper($request->extension);
                $extension->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'extension' => $extension,
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
        $this->authorize('ROLE_EXTENSION_DELETE', Extension::class);
        $extension = Extension::findOrFail($id);
        try {
            $extension->delete();
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'extension' => $extension,
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
        $this->authorize('ROLE_EXTENSION_READ', Extension::class);
        $extension = Extension::findOrFail($id);
        return new JsonResponse([
            'extension' => $extension
        ], 200);
    }

    public function extensionReports(Request $request)
    {
        $this->authorize('ROLE_EXTENSION_PRINT', Extension::class);
        try {
            $extensions = $this->extensionRepository->extensionReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['extensions' => $extensions]], 200);
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
                    'extension' => 'required|unique:extensions'
                ],
                [
                    'extension.required' => "L'extension est obligatoire.",
                    'extension.unique' => "Cette extension existe déjà.",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'extension' => 'required'
                ],
                [
                    'extension.required' => "L'extension est obligatoire.",
                ]
            );
        }
    }
}
