<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\EmailChannelParam;
use App\Models\Host;
use App\Models\Role;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    public function index()
    {
        // $this->authorize('ROLE_DRIVER_READ', Driver::class);
        $drivers = Driver::with('hosts')->orderBy('wording')->get();
        return new JsonResponse([
            'datas' => ['drivers' => $drivers],
        ], 200);
    }

    public function hostsOfDriver($id)
    {
        $this->authorize('ROLE_DRIVER_READ', Driver::class);
        $hosts = Host::where('driver_id', $id)->get();
        return new JsonResponse(['datas' => ['hosts' => $hosts]]);
    }

    public function emailChannelParamsOfDriver($id)
    {
        $this->authorize('ROLE_DRIVER_READ', Driver::class);
        $emailChannelParamss = EmailChannelParam::where('driver_id', $id)->get();
        return new JsonResponse(['datas' => ['emailChannelParamss' => $emailChannelParamss]]);
    }

    // Enregistrement d'une nouvelle donnée driver
    public function store(Request $request)
    {
        $this->authorize('ROLE_DRIVER_CREATE', Driver::class);
        $this->validate(
            $request,
            [
                'wording' => 'required|unique:drivers|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Ce driver existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $driver = new Driver();
            $driver->code = Str::random(10);
            $driver->wording = $request->wording;
            $driver->description = $request->description;
            $driver->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'driver' => $driver,
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


    // Mise à jour d'une donnée driver
    public function update(Request $request, $id)
    {
        $this->authorize('ROLE_DRIVER_UPDATE', Driver::class);
        $driver = Driver::findOrFail($id);
        $this->validate(
            $request,
            [
                'wording' => 'required|max:150',
                'description' => 'max:255',
            ],
            [
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        $existingDrivers = Driver::where('wording', $request->wording)->get();
        if (!empty($existingDrivers) && sizeof($existingDrivers) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingDriver' => $existingDrivers[0],
                'message' => "Le driver " . $existingDrivers[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $driver->wording = $request->wording;
            $driver->description = $request->description;
            $driver->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'driver' => $driver,
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


    // Suppression d'une donnée driver
    public function destroy($id)
    {
        $this->authorize('ROLE_DRIVER_DELETE', Driver::class);
        $driver = Driver::findOrFail($id);
        try {
            $success = false;
            $message = "";
            if (empty($driver->hosts) || sizeof($driver->hosts) == 0 && empty($driver->emailChannelParams) || sizeof($driver->emailChannelParams) == 0) {
                // dd('delete');
                $driver->delete();
                $success = true;
                $message = "Suppression effectuée avec succès.";
            } else {
                // dd('not delete');
                $message = "Ce driver ne peut être supprimé car il a servi dans des traitements.";
            }

            return new JsonResponse([
                'driver' => $driver,
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
        $this->authorize('ROLE_DRIVER_READ', Driver::class);
        $driver = Driver::findOrFail($id);
        return new JsonResponse([
            'driver' => $driver
        ], 200);
    }
}
