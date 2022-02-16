<?php

namespace App\Http\Controllers;

use App\Models\FileType;
use App\Models\Tank;
use App\Models\TankTruck;
use App\Models\Truck;
use App\Models\UploadFile;
use App\Utils\FileUtil;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TankTruckController extends Controller
{

    protected $fileUtil;

    public function __construct()
    {
        $this->fileUtil = new FileUtil('TankTrucks');
    }

    private function tankTruckAuthorizedFiles()
    {
        $this->authorize('ROLE_TANK_TRUCK_READ', TankTruck::class);
        $tankTruckAuthorizedFiles = FileType::where('code', 'CJ')->first();
        return $tankTruckAuthorizedFiles;
    }

    public function index($param, $id)
    {
        $this->authorize('ROLE_TANK_TRUCK_READ', TankTruck::class);
        if ($param == 'truck') {
            $tankTrucks = TankTruck::orderBy('created_at','desc')->where('truck_id', $id)->with('truck')->orderBy('created_at', 'desc')->get();
        } else {
            $tankTrucks = TankTruck::orderBy('created_at','desc')->where('tank_id', $id)->with('tank')->orderBy('created_at', 'desc')->get();
        }
        $tanks = Tank::orderBy('tank_registration')->get();
        $trucks = Truck::orderBy('truck_registration')->get();
        return new JsonResponse(['datas' => ['tankTrucks' => $tankTrucks, 'tanks' => $tanks, 'trucks' => $trucks]], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_TANK_TRUCK_CREATE', TankTruck::class);

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
                $tankTruck = new TankTruck();
                $tankTruck->gauging_certificate = $request->gauging_certificate;
                $tankTruck->validity_date = $request->validity_date;
                $tankTruck->gauging_certificate_number = $request->gauging_certificate_number;
                $tankTruck->tank_id = $request->tank;
                $tankTruck->truck_id = $request->truck;
                // $tankTruck->file_type_id = $this->tankTruckAuthorizedFiles()->id;
                $tankTruck->save();

                $file = $request->file('gauging_certificate');

                $fileUpload = $this->fileUtil->createFile($tankTruck, $file, $request->personalized_filename);

                $message = "Enregistrement effectué avec succès.";
                return new JsonResponse([
                    'tankTruck' => $tankTruck,
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
        $this->authorize('ROLE_TANK_TRUCK_UPDATE', TankTruck::class);
        $tankTruck = TankTruck::findOrFail($id);

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
                $tankTruck->gauging_certificate = $request->gauging_certificate;
                $tankTruck->validity_date = $request->validity_date;
                $tankTruck->gauging_certificate_number = $request->gauging_certificate_number;
                // $fileName = $this->tankTruckAuthorizedFiles()->wording . $request->gauging_certificate->getClientOriginalExtension();
                // $path = $tankTruck->gauging_certificate->storeAs($this->tankTruckAuthorizedFiles()->wording . '/', $fileName, 'public');
                $tankTruck->tank_id = $request->tank;
                $tankTruck->truck_id = $request->truck;
                $tankTruck->save();

                $file = $request->file('gauging_certificate');
                $fileUpload = $this->fileUtil->createFile($tankTruck, $file, $request->personalized_filename);

                $message = "Modification effectuée avec succès.";
                return new JsonResponse([
                    'tankTruck' => $tankTruck,
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
        $this->authorize('ROLE_TANK_TRUCK_DELETE', TankTruck::class);
        $tankTruck = TankTruck::findOrFail($id);
        try {
            $tankTruck->delete();
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'tankTruck' => $tankTruck,
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
        $this->authorize('ROLE_TANK_TRUCK_READ', TankTruck::class);
        $tankTruck = TankTruck::with('truck')->with('tank')->findOrFail($id);

        return new JsonResponse([
            'tankTruck' => $tankTruck
        ], 200);
    }

    public function edit($id)
    {
        $this->authorize('ROLE_TANK_TRUCK_READ', TankTruck::class);
        $tankTruck = TankTruck::with('truck')->with('tank')->findOrFail($id);
        return new JsonResponse([
            'tankTruck' => $tankTruck
        ], 200);
    }

    protected function validator($mode, $data)
    {
        if ($mode == 'store') {
            return Validator::make(
                $data,
                [
                    'truck' => 'required',
                    'tank' => 'required',
                    'gauging_certificate_number' => 'required',
                    'validity_date' => 'required|date|before:today',
                    'gauging_certificate.*' => 'required|file|size:' . $this->tankTruckAuthorizedFiles()->max_size . '|mimes:' . $this->tankTruckAuthorizedFiles()->authorized_files,
                ],
                [
                    'truck.required' => "Le choix d'un camion est obligatoire.",
                    'tank.required' => "Le choix d'une citerne est obligatoire.",
                    'gauging_certificate_number.required' => "Le numéro certificat de jaugeage est obligatoire.",
                    'validity_date.required' => "La date de validité est obligatoire.",
                    'validity_date.date' => "La date de validité saisie est incorrecte.",
                    'validity_date.date_format' => "La date de validité doit être au format : Année Mois Jour.",
                    'validity_date.before' => "La date de validité doit être antérieure ou égale à aujourd'hui.",
                    'gauging_certificate.required' => "Le certificat de jaugeage est obligatoire.",
                    'gauging_certificate.file' => "Le certificat de jaugeage doit être un fichier.",
                    'gauging_certificate.mimes' => "Les formats de fichier autorisés sont : " . $this->tankTruckAuthorizedFiles()->authorized_files,
                    'gauging_certificate.size' => "La taille maximale du fichier est de : " . $this->tankTruckAuthorizedFiles()->max_size . 'Ko',
                    // 'validity_date.required'=>"",
                ]
            );
        }
        if ($mode == 'update') {
            return Validator::make(
                $data,
                [
                    'truck' => 'required',
                    'tank' => 'required',
                    'gauging_certificate_number' => 'required',
                    'validity_date' => 'required|date|date_format:Ymd|before:today',
                    'gauging_certificate.*' => 'required|file|size:' . $this->tankTruckAuthorizedFiles()->max_size . '|mimes:' . $this->tankTruckAuthorizedFiles()->authorized_files,
                ],
                [
                    'truck.required' => "Le choix d'un camion est obligatoire.",
                    'tank.required' => "Le choix d'une citerne est obligatoire.",
                    'gauging_certificate_number.required' => "Le numéro certificat de jaugeage est obligatoire.",
                    'validity_date.required' => "La date de validité est obligatoire.",
                    'validity_date.date' => "La date de validité saisie est incorrecte.",
                    'validity_date.date_format' => "La date de validité doit être au format : Année Mois Jour.",
                    'validity_date.before' => "La date de validité doit être antérieure ou égale à aujourd'hui.",
                    'gauging_certificate.required' => "Le certificat de jaugeage est obligatoire.",
                    'gauging_certificate.file' => "Le certificat de jaugeage doit être un fichier.",
                    'gauging_certificate.mimes' => "Les formats de fichier autorisés sont : " . $this->tankTruckAuthorizedFiles()->authorized_files,
                    'gauging_certificate.size' => "La taille maximale du fichier est de : " . $this->tankTruckAuthorizedFiles()->max_size . 'Ko',
                    // 'validity_date.required'=>"",
                ]
            );
        }
    }
}
