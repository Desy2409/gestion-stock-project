<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Host;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostController extends Controller
{
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
        $this->validate(
            $request,
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

        try {
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
        $this->authorize('ROLE_HOST_UPDATE', Host::class);
        $host = Host::findOrFail($id);
        $this->validate(
            $request,
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
        } catch (Exception $e) {
            // dd($e);
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
        $this->authorize('ROLE_HOST_DELETE', Host::class);
        $host = Host::findOrFail($id);
        try {
            $host->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
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
}
