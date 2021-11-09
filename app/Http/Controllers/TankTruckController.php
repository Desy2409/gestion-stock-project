<?php

namespace App\Http\Controllers;

use App\Models\FileType;
use App\Models\Tank;
use App\Models\TankTruck;
use App\Models\Truck;
use App\Models\UploadFile;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TankTruckController extends Controller
{
    private function tankTruckAuthorizedFiles()
    {
        // dd('enter');
        $tankTruckAuthorizedFiles = FileType::where('code', 'CJ')->first();
        return $tankTruckAuthorizedFiles;
    }

    public function index()
    {
        $tankTrucks = TankTruck::orderBy('validity_date', 'desc')->get();
        $tanks = Tank::orderBy('tank_registration')->get();
        $trucks = Truck::orderBy('truck_registration')->get();
        return new JsonResponse(['datas' => ['tankTrucks' => $tankTrucks, 'tanks' => $tanks, 'trucks' => $trucks]], 200);
    }

    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'truck' => 'required',
                'tank' => 'required',
                'validity_date' => 'required|date|date_format:d-m-Y|before:today',
                'gauging_certificate' => 'required|file|size:' . $this->tankTruckAuthorizedFiles()->max_size . '|mimes:' . $this->tankTruckAuthorizedFiles()->authorized_files,
            ],
            [
                'truck.required' => "Le choix d'un camion est obligatoire.",
                'tank.required' => "Le choix d'une citerne est obligatoire.",
                'validity_date.required' => "La date de validité est obligatoire.",
                'validity_date.date' => "La date de validité saisie est incorrecte.",
                'validity_date.date_format' => "La date de validité doit être au format : JJ/MM/AAAA.",
                'validity_date.before' => "La date de validité doit être antérieure ou égale à aujourd'hui.",
                'gauging_certificate.required' => "Le certificat de jaugeage est obligatoire.",
                'gauging_certificate.file' => "Le certificat de jaugeage doit être un fichier.",
                'gauging_certificate.mimes' => "Les formats de fichier autorisés sont : " . $this->tankTruckAuthorizedFiles()->authorized_files,
                'gauging_certificate.size' => "La taille maximale du fichier est de : " . $this->tankTruckAuthorizedFiles()->max_size . 'Ko',
                // 'validity_date.required'=>"",
            ]
        );

        try {
            $tankTruck = new TankTruck();
            $tankTruck->gauging_certificate = $request->gauging_certificate;
            $tankTruck->validity_date = $request->validity_date;
            $tankTruck->file_type_id = $this->tankTruckAuthorizedFiles()->id;
            $tankTruck->save();

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'tankTruck' => $tankTruck,
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
        $tankTruck = TankTruck::findOrFail($id);
        $this->validate(
            $request,
            [
                'truck' => 'required',
                'tank' => 'required',
                'validity_date' => 'required|date|date_format:d-m-Y|before:today',
                'gauging_certificate' => 'required|file|size:' . $this->tankTruckAuthorizedFiles()->max_size . '|mimes:' . $this->tankTruckAuthorizedFiles()->authorized_files,
            ],
            [
                'truck.required' => "Le choix d'un camion est obligatoire.",
                'tank.required' => "Le choix d'une citerne est obligatoire.",
                'validity_date.required' => "La date de validité est obligatoire.",
                'validity_date.date' => "La date de validité saisie est incorrecte.",
                'validity_date.date_format' => "La date de validité doit être au format : JJ/MM/AAAA.",
                'validity_date.before' => "La date de validité doit être antérieure ou égale à aujourd'hui.",
                'gauging_certificate.required' => "Le certificat de jaugeage est obligatoire.",
                'gauging_certificate.file' => "Le certificat de jaugeage doit être un fichier.",
                'gauging_certificate.mimes' => "Les formats de fichier autorisés sont : " . $this->tankTruckAuthorizedFiles()->authorized_files,
                'gauging_certificate.size' => "La taille maximale du fichier est de : " . $this->tankTruckAuthorizedFiles()->max_size . 'Ko',
                // 'validity_date.required'=>"",
            ]
        );

        try {
            // $tankTruck->gauging_certificate = $request->gauging_certificate;
            $tankTruck->validity_date = $request->validity_date;
            $fileName = $this->tankTruckAuthorizedFiles()->wording . $request->gauging_certificate->getClientOriginalExtension();
            $path = $tankTruck->gauging_certificate->storeAs($this->tankTruckAuthorizedFiles()->wording . '/' , $fileName, 'public');
            $tankTruck->save();

            $uploadFile = new UploadFile();
            $uploadFile->code = Str::random(10);
            $uploadFile->name = $path;
            $uploadFile->personalized_name = $request->personalized_name;
            $uploadFile->file_type_id = $request->$this->tankTruckAuthorizedFiles()->id;
            $uploadFile->tank_truck_id = $tankTruck->id;
            $uploadFile->save();

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'tankTruck' => $tankTruck,
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
        $tankTruck = TankTruck::findOrFail($id);
        try {
            $tankTruck->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'tankTruck' => $tankTruck,
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
}