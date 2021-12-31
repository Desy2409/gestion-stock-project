<?php

namespace App\Http\Controllers;

use App\Models\CorrespondenceChannel;
use App\Models\Driver;
use App\Models\EmailChannelParam;
use App\Repositories\EmailChannelParamRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailChannelParamController extends Controller
{

    public $emailChannelParamRepository;

    public function __construct(EmailChannelParamRepository $emailChannelParamRepository)
    {
        $this->emailChannelParamRepository = $emailChannelParamRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_READ', EmailChannelParam::class);
        $emailChannelParams = EmailChannelParam::with('correspondenceChannel')->with('driver')->orderBy('is_active', 'DESC')->orderBy('created_at', 'DESC')->get();
        return new JsonResponse([
            'datas' => ['emailChannelParams' => $emailChannelParams]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_CREATE', EmailChannelParam::class);
        $this->validate(
            $request,
            [
                'driver' => 'required',
                'description' => 'max:255'
            ],
            [
                'driver.required' => "Le driver est obligatoire.",
                'description' => 'La description ne doit dépasser 255 caractères.'
            ],
        );

        if ($request->driver != null) {
            switch ($request->driver) {
                case 1:
                    // SMTP
                    $this->validate(
                        $request,
                        [
                            'host' => 'required',
                            'port' => 'required|numeric|unique:email_channel_params',
                            'username' => 'max:20',//|unique:email_channel_params',
                            'password' => 'min:8|confirmed',
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'host.required' => "L'hôte est obligatoire.",
                            'port.required' => "Le port est obligatoire.",
                            'port.numeric' => "Le port doit être un nombre positif sans virgule.",
                            'port.unique' => "Ce port est déjà utilisé.",
                            'username.max' => "Le nom d'utilisateur ne doit pas dépasser 20 caractères.",
                            // 'username.unique' => "Ce nom d'utilisateur existe déjà.",
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 2:
                    // Mailgun
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );

                    break;

                case 3:
                    // Postmark
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 4:
                    // Amazon SES
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 5:
                    // Sendmail
                    $this->validate(
                        $request,
                        [
                            'path' => 'required',
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'path.required' => 'Le chemin est obligatoire.',
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 6:
                    // Log
                    // dd('cas 6');
                    $this->validate(
                        $request,
                        [
                            'channel' => 'required',
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'channel.required' => 'Le canal est obligatoire.',
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 7:
                    // Array
                    // dd('cas 7');
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 8:
                    // Failover

                    break;

                default:

                    break;
            }
        }

        try {
            $emailChannelParam = new EmailChannelParam();
            $emailChannelParam->port = $request->port;
            $emailChannelParam->username = $request->username;
            $emailChannelParam->password = Hash::make($request->password);
            $emailChannelParam->encryption = $request->encryption;
            $emailChannelParam->path = $request->path;
            $emailChannelParam->channel = $request->channel;
            $emailChannelParam->from_address = $request->from_address;
            $emailChannelParam->from_name = $request->from_name;
            $emailChannelParam->is_active = false;
            $emailChannelParam->driver_id = $request->driver;
            $emailChannelParam->host_id = $request->host;
            $emailChannelParam->save();

            $correspondenceChannel = new CorrespondenceChannel();
            $correspondenceChannel->name = $request->name;
            $correspondenceChannel->description = $request->description;
            $correspondenceChannel->channelable_id = $emailChannelParam->id;
            $correspondenceChannel->channelable_type = $emailChannelParam::class;
            $correspondenceChannel->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'emailChannelParam' => $emailChannelParam,
                'correspondenceChannel' => $correspondenceChannel,
                'success' => $success,
                'message' => $message,
            ], 200);
        } catch (Exception $e) {
            // dd($e);
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 400);
        }
    }

    public function edit($id)
    {
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_READ', EmailChannelParam::class);
        $emailChannelParam = EmailChannelParam::findOrFail($id);
        $correspondenceChannel = $emailChannelParam->correspondenceChannel;
        $drivers = Driver::orderBy('wording')->get();

        return new JsonResponse([
            'emailChannelParam' => $emailChannelParam,
            'correspondenceChannel' => $correspondenceChannel,
            'datas' => ['drivers' => $drivers]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_UPDATE', EmailChannelParam::class);
        $emailChannelParam = EmailChannelParam::with('correspondenceChannel')->findOrFail($id);
        $correspondenceChannel = CorrespondenceChannel::where(['channelable_id' => $id, 'channelable_type' => $emailChannelParam::class])->first();
        
        $this->validate(
            $request,
            [
                'driver' => 'required',
                'description' => 'max:255'
            ],
            [
                'driver.required' => "Le driver est obligatoire.",
                'description' => 'La description ne doit dépasser 255 caractères.'
            ],
        );

        if ($request->driver != null) {
            switch ($request->driver) {
                case 1:
                    // SMTP
                    $this->validate(
                        $request,
                        [
                            'host' => 'required',
                            'port' => 'required|numeric',
                            'username' => 'max:20',//|unique:email_channel_params',
                            'password' => 'min:8|confirmed',
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'host.required' => "L'hôte est obligatoire.",
                            'port.required' => "Le port est obligatoire.",
                            'port.numeric' => "Le port doit être un nombre positif sans virgule.",
                            'username.max' => "Le nom d'utilisateur ne doit pas dépasser 20 caractères.",
                            // 'username.unique' => "Ce nom d'utilisateur existe déjà.",
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 2:
                    // Mailgun
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );

                    break;

                case 3:
                    // Postmark
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 4:
                    // Amazon SES
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 5:
                    // Sendmail
                    $this->validate(
                        $request,
                        [
                            'path' => 'required',
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'path.required' => 'Le chemin est obligatoire.',
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 6:
                    // Log
                    // dd('cas 6');
                    $this->validate(
                        $request,
                        [
                            'channel' => 'required',
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'channel.required' => 'Le canal est obligatoire.',
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 7:
                    // Array
                    // dd('cas 7');
                    $this->validate(
                        $request,
                        [
                            'from_address' => 'required|email',
                            'from_name' => 'required',
                        ],
                        [
                            'from_address.required' => "L'adresse email de l'expéditeur est obligatoire.",
                            'from_address.email' => "L'adresse email de l'expéditeur est incorrecte.",
                            'from_name.required' => "Le nom à afficher est obligatoire.",
                        ]
                    );
                    break;

                case 8:
                    // Failover

                    break;

                default:

                    break;
            }
        }

        $existingEmailChannelParamsOnPort = EmailChannelParam::where('port', $request->port)->get();
        if (!empty($existingEmailChannelParamsOnPort) && sizeof($existingEmailChannelParamsOnPort) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingEmailChannelParam' => $existingEmailChannelParamsOnPort[0],
                'message' => "Le port " . $existingEmailChannelParamsOnPort[0]->port . " est déjà utilisé"
            ]);
        }

        try {
            $emailChannelParam->port = $request->port;
            $emailChannelParam->username = $request->username;
            $emailChannelParam->password = Hash::make($request->password);
            $emailChannelParam->encryption = $request->encryption;
            $emailChannelParam->path = $request->path;
            $emailChannelParam->channel = $request->channel;
            $emailChannelParam->from_address = $request->from_address;
            $emailChannelParam->from_name = $request->from_name;
            $emailChannelParam->is_active = false;
            $emailChannelParam->driver_id = $request->driver;
            $emailChannelParam->host_id = $request->host;
            $emailChannelParam->save();

            $correspondenceChannel->name = $request->name;
            $correspondenceChannel->description = $request->description;
            $correspondenceChannel->channelable_id = $emailChannelParam->id;
            $correspondenceChannel->channelable_type = $emailChannelParam::class;
            $correspondenceChannel->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'emailChannelParam' => $emailChannelParam,
                'correspondenceChannel' => $correspondenceChannel,
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
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_DELETE', EmailChannelParam::class);
        $emailChannelParam = EmailChannelParam::findOrFail($id);
        
        try {
            $emailChannelParam->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'emailChannelParam' => $emailChannelParam,
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
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_READ', EmailChannelParam::class);
        $emailChannelParam = EmailChannelParam::findOrFail($id);
        return new JsonResponse([
            'emailChannelParam' => $emailChannelParam
        ], 200);
    }

    public function emailChannelParamReports(Request $request)
    {
        $this->authorize('ROLE_EMAIL_CHANNEL_PARAM_PRINT', EmailChannelParam::class);
        try {
            $emailChannelParams = $this->emailChannelParamRepository->emailChannelParamReport($request->selected_default_fields);
            return new JsonResponse(['datas' => ['emailChannelParams' => $emailChannelParams]], 200);
        } catch (Exception $e) {
            dd($e);
        }
    }
}
