<?php

namespace App\Http\Controllers;

use App\Models\SmsChannelParam;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SmsChannelParamController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        $this->authorize('ROLE_SMS_CHANNEL_PARAM_READ', SmsChannelParam::class);

        $smsChannelParams = SmsChannelParam::orderBy('created_at', 'desc')->get();

        return new JsonResponse(['datas' => ['smsChannelParams' => $smsChannelParams]], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_SMS_CHANNEL_PARAM_CREATE', SmsChannelParam::class);

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
                $smsChannelParam = new SmsChannelParam();
                $smsChannelParam->url = $request->url;
                $smsChannelParam->user = $request->user;
                $smsChannelParam->password = Hash::make($request->password);
                $smsChannelParam->sender = $request->sender;
                $smsChannelParam->type = [
                    'simple_http' => $request->simple_http,
                    'json_body_server' => $request->json_body_server,
                    'xml_body_server' => $request->xml_body_server,
                ];
                $smsChannelParam->sms_header_type = [
                    'basic' => $request->basic,
                    'bearer' => $request->bearer,
                    'none' => $request->none,
                    'api_key' => $request->api_key
                ];
                $smsChannelParam->save();

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse(['success' => true, 'message' => $message, 'smsChannelParam' => $smsChannelParam], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur est survenue lors de l'enregistrement.";
            return new JsonResponse(['success' => false, 'message' => $message], 200);
        }
    }

    public function show($id)
    {
        $this->authorize('ROLE_SMS_CHANNEL_READ', SmsChannelParam::class);
        $smsChannelParam = SmsChannelParam::findOrFail($id);
        return new JsonResponse([
            'smsCha$smsChannelParam' => $smsChannelParam
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_SMS_CHANNEL_READ', SmsChannelParam::class);
        $smsChannelParam = SmsChannelParam::findOrFail($id);
        return new JsonResponse([
            'smsCha$smsChannelParam' => $smsChannelParam,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_SMS_CHANNEL_PARAM_', SmsChannelParam::class);
        $smsChannelParam = SmsChannelParam::findOrFail($id);

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
                $smsChannelParam->url = $request->url;
                $smsChannelParam->user = $request->user;
                $smsChannelParam->password = Hash::make($request->password);
                $smsChannelParam->sender = $request->sender;
                $smsChannelParam->type = [
                    'simple_http' => $request->simple_http,
                    'json_body_server' => $request->json_body_server,
                    'xml_body_server' => $request->xml_body_server,
                ];
                $smsChannelParam->sms_header_type = [
                    'basic' => $request->basic,
                    'bearer' => $request->bearer,
                    'none' => $request->none,
                    'api_key' => $request->api_key
                ];
                $smsChannelParam->save();

                $message = "Modification effectuée avec succès.";
                return new JsonResponse(['success' => true, 'message' => $message, 'smsChannelParam' => $smsChannelParam], 200);
            }
        } catch (Exception $e) {
            $message = "Erreur est survenue lors de la modification.";
            return new JsonResponse(['success' => false, 'message' => $message], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_SMS_CHANNEL_PARAM_', SmsChannelParam::class);
    }

    public function smsChannelParamReports()
    {
        $this->authorize('ROLE_SMS_CHANNEL_PARAM_', SmsChannelParam::class);
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'url' => 'required',
                    'user' => 'required',
                    'password' => 'required',
                    'sender' => 'required',
                ],
                [
                    'url.required' => "L'url est obligatoire.",
                    'user.required' => "L'utilisateur est obligatoire.",
                    'password.required' => "Le mot de passe est obligatoire.",
                    'sender.required' => "L'expéditeur est obligatoire."
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'url' => 'required',
                    'user' => 'required',
                    'password' => 'required',
                    'sender' => 'required',
                ],
                [
                    'url.required' => "L'url est obligatoire.",
                    'user.required' => "L'utilisateur est obligatoire.",
                    'password.required' => "Le mot de passe est obligatoire.",
                    'sender.required' => "L'expéditeur est obligatoire."
                ]
            );
        }
    }
}
