<?php

namespace App\Http\Controllers;

use App\Models\ApiService;
use App\Models\ApiServiceHeader;
use App\Models\ApiServiceResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiServiceController extends Controller
{
    public function index()
    {
        $apiServices = ApiService::orderBy('created_at')->with('apiServices')->with('apiServiceResponses')->with('apiServiceHeaders')->get();

        return new JsonResponse(['datas' => ['apiServices' => $apiServices]], 200);
    }

    public function store(Request $request)
    {
        try {
            // $validation = $this->apiServiceValidator($request->all());

            // if ($validation->fails()) {
            //     $messages = $validation->errors()->all();
            //     $messages = implode('<br/>', $messages);
            //     return new JsonResponse([
            //         'success' => false,
            //         'message' => $messages,
            //     ], 200);
            // } else {
                $apiService = new ApiService();
                $apiService->reference = $request->reference;
                $apiService->wording = $request->wording;
                $apiService->description = $request->description;
                $apiService->authorization_type = $request->authorization_type;
                $apiService->authorization_user = $request->authorization_user;
                $apiService->authorization_password = Hash::make($request->authorization_password);
                $apiService->authorization_token = $request->authorization_token;
                $apiService->authorization_prefix = $request->authorization_prefix;
                $apiService->authorization_key = $request->authorization_key;
                $apiService->authorization_value = $request->authorization_value;
                $apiService->api_service_id = $request->api_service;
                $apiService->token_attribute = $request->token_attribute;
                $apiService->body_type = $request->body_type;
                $apiService->body_content = $request->body_content;
                $apiService->save();

                $this->storeApiServiceResponse($request, $apiService);
                $this->storeApiServiceHeader($request, $apiService);

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'success' => true,
                    'message' => $message,
                    'apiService' => $apiService
                ], 200);
            // }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        $apiService = ApiService::findOrFail($id);
        // dd($apiService);

        // dd($request->all());
        try {
            $validation = $this->apiServiceValidator($request->all());

            if ($validation->fails()) {
                $messages = $validation->errors()->all();
                $messages = implode('<br/>', $messages);
                return new JsonResponse([
                    'success' => false,
                    'message' => $messages,
                ], 200);
            } else {
                $apiService->reference = $request->reference;
                $apiService->wording = $request->wording;
                $apiService->description = $request->description;
                $apiService->authorization_type = $request->authorization_type;
                $apiService->authorization_user = $request->authorization_user;
                $apiService->authorization_password = Hash::make($request->authorization_password);
                $apiService->authorization_token = $request->authorization_token;
                $apiService->authorization_prefix = $request->authorization_prefix;
                $apiService->authorization_key = $request->authorization_key;
                $apiService->authorization_value = $request->authorization_value;
                $apiService->api_service_id = $request->api_service;
                $apiService->token_attribute = $request->token_attribute;
                $apiService->body_type = $request->body_type;
                $apiService->body_content = $request->body_content;
                $apiService->save();

                ApiServiceResponse::where("api_service_id", $apiService->id)->delete();
                $this->storeApiServiceResponse($request, $apiService);

                ApiServiceHeader::where("api_service_id", $apiService->id)->delete();
                $this->storeApiServiceHeader($request, $apiService);

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'success' => true,
                    'message' => $message,
                    'apiService' => $apiService
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function show($id)
    {
        $apiService = ApiService::with('apiService')->with('apiServiceResponse')->with('apiServiceHeaders')->where('id', $id)->first();
        return new JsonResponse(['apiService' => $apiService], 200);
    }

    public function edit($id)
    {
        $apiService = ApiService::with('apiService')->with('apiServiceResponses')->with('apiServiceHeaders')->where('id', $id)->first();
        return new JsonResponse(['apiService' => $apiService], 200);
    }

    public function destroy($id)
    {
        $apiService = ApiService::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (
                empty($apiService->apiServiceResponses) || sizeof($apiService->apiServiceResponses) == 0 &&
                empty($apiService->apiServiceHeaders) || sizeof($apiService->apiServiceHeaders) == 0
            ) {
                // dd('delete');
                $apiService->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce service d'api ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'apiService' => $apiService,
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

    public function storeApiServiceResponse(Request $request, ApiService $apiService)
    {
        try {
            ApiServiceResponse::where('api_service_id', $apiService->id)->delete();
            if (!empty($request->api_service_responses) && sizeof($request->api_service_responses) > 0) {
                foreach ($request->api_service_responses as $key => $response) {
                    $apiServiceResponse = new ApiServiceResponse();
                    $apiServiceResponse->response_type = array_key_exists('response_type', $response) ?  $response['response_type'] : null;
                    $apiServiceResponse->response_content = array_key_exists('response_content', $response) ? $response['response_content'] : null;
                    $apiServiceResponse->response_state = array_key_exists('response_state', $response) ? $response['response_state'] : null;
                    $apiServiceResponse->api_service_id = $apiService->id;
                    $apiServiceResponse->save();
                }
            }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    public function storeApiServiceHeader(Request $request, ApiService $apiService)
    {
        try {
            ApiServiceHeader::where('api_service_id', $apiService->id)->delete();
            if (!empty($request->api_service_headers) && sizeof($request->api_service_headers) > 0) {
                foreach ($request->api_service_headers as $key => $header) {
                    $apiServiceHeader = new ApiServiceHeader();
                    $apiServiceHeader->key = array_key_exists('key', $header) ? $header['key'] : null;
                    $apiServiceHeader->value = array_key_exists('value', $header) ? $header['value'] : null;
                    $apiServiceHeader->api_service_id = $apiService->id;
                    $apiServiceHeader->save();
                }
                // }
            }
        } catch (Exception $e) {
            dd($e);
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => false,
                'message' => $message,
            ], 200);
        }
    }

    protected function apiServiceValidator($data)
    {
        return Validator::make(
            $data,
            [
                'reference' => 'required',
                'wording' => 'required',
                // 'description' => 'required',
                // 'authorization_type' => 'required',
                // 'authorization_user' => 'required',
                // 'authorization_password' => 'required',
                // 'authorization_token' => 'required',
                // 'authorization_prefix' => 'required',
                // 'authorization_key' => 'required',
                // 'authorization_value' => 'required',
                // 'body_type' => 'required',
                // 'body_content' => 'required',
            ],
            [
                'reference.required' => "La référence est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                // 'description.required' => "La description est obligatoire.",
                // 'authorization_type.required' => "Le type du service est obligatoire.",
                // 'authorization_user.required' => "L'utilisateur est obligatoire.",
                // 'authorization_password.required' => "Le mot de passe est obligatoire.",
                // 'authorization_token.required' => "Le token est obligatoire.",
                // 'authorization_prefix.required' => "Le préfixe est obligatoire.",
                // 'authorization_key.required' => "La clé de l'api_key est obligatoire.",
                // 'authorization_value.required' => "La valeur de l'api_key est obligatoire.",
                // 'body_type.required' => "Le type du corps est obligatoire.",
                // 'body_content.required' => "Le contenu du corps est obligatoire.",
            ]
        );
    }

    protected function responseValidator($data)
    {
        return Validator::make(
            $data,
            [
                'response_type' => 'required',
                'response_content' => 'required',
            ],
            [
                'response_type.required' => "Le type de réponse est obligatoire.",
                'response_content.required' => "Le contenu de la réponse est obligatoire.",
            ]
        );
    }

    protected function headerValidator($data)
    {
        return Validator::make(
            $data,
            [
                'key' => 'required',
                'value' => 'required'
            ],
            [
                'key.required' => "La clé est obligatoire.",
                'key.required' => "La valeur est obligatoire.",
            ]
        );
    }
}
