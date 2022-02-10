<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Host;
use App\Repositories\HostRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HostController extends Controller
{
    public $hostRepository;

    public function __construct(HostRepository $hostRepository)
    {
        $this->hostRepository = $hostRepository;
    }

    public function index()
    {
        $this->authorize('ROLE_HOST_READ', Host::class);
        $hosts = Host::with('driver')->orderBy('provider')->get();
        return new JsonResponse([
            'datas' => ['hosts' => $hosts]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_HOST_CREATE', Host::class);

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
                $host = new Host();
                $host->code = Str::random(10);
                $host->provider = $request->provider;
                $host->url = $request->url;
                $host->host_name = $request->host_name;
                $host->driver_id = $request->driver;
                $host->save();

                $success = true;
                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'host' => $host,
                    'success' => $success,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            $success = false;
            $message = "Erreur survenue lors de l'enregistrement.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_HOST_UPDATE', Host::class);
        $host = Host::findOrFail($id);

        $existingHosts = Host::where('host_name', $request->host_name)->get();
        if (!empty($existingHosts) && sizeof($existingHosts) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingHost' => $existingHosts[0],
                'message' => "L'hôte " . $existingHosts[0]->host_name . " existe déjà"
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
                $host->provider = $request->provider;
                $host->url = $request->url;
                $host->host_name = $request->host_name;
                $host->driver_id = $request->driver;
                $host->save();

                $success = true;
                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'host' => $host,
                    'success' => $success,
                    'message' => $message,
                ], 200);
            }
        } catch (Exception $e) {
            // dd($e);
            $success = false;
            $message = "Erreur survenue lors de la modification.";
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
            ], 200);
        }
    }

    public function destroy($id)
    {
        $this->authorize('ROLE_HOST_DELETE', Host::class);
        $host = Host::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($host->emailChannelParams) || sizeof($host->emailChannelParams) == 0) {
                // dd('delete');
                $host->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Cet hôte ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'host' => $host,
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
        $this->authorize('ROLE_HOST_READ', Host::class);
        $host = Host::findOrFail($id);
        return new JsonResponse([
            'host' => $host
        ], 200);
    }

    public function hostReports(Request $request)
    {
        $this->authorize('ROLE_HOST_PRINT', Host::class);
        try {
            $hosts = $this->hostRepository->oneJoinReport(Host::class, 'hosts', 'drivers',  'host', 'driv', 'driver_id', $request->child_selected_fields, $request->parent_selected_fields);
            return new JsonResponse(['datas' => ['hosts' => $hosts]], 200);
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
                    'driver' => 'required',
                    'host_name' => 'required|unique:hosts|max:20',
                    'provider' => 'required',
                    'url' => 'required'
                ],
                [
                    'driver.required' => "Le driver est obligatoire.",
                    'host_name.required' => "Le nom d'hôte est obligatoire.",
                    'host_name.unique' => "Cet hôte existe déjà.",
                    'host_name.max' => "Le nom d'hôte ne doit pas dépasser 20 caractères.",
                    'provider.required' => "Le fournisseur de service est obligatoire.",
                    'url.required' => "L'url est obligatoire."
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'driver' => 'required',
                    'host_name' => 'required|max:20',
                    'provider' => 'required',
                    'url' => 'required'
                ],
                [
                    'driver.required' => "Le driver est obligatoire.",
                    'host_name.required' => "Le nom d'hôte est obligatoire.",
                    'host_name.max' => "Le nom d'hôte ne doit pas dépasser 20 caractères.",
                    'provider.required' => "Le fournisseur de service est obligatoire.",
                    'url.required' => "L'url est obligatoire."
                ]
            );
        }
    }
}
