<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\PhoneOperator;
use App\Models\StartNumber;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhoneOperatorController extends Controller
{
    public function index()
    {
        $this->authorize('ROLE_PHONE_OPERATOR_READ', PhoneOperator::class);
        $phoneOperators = PhoneOperator::with('startNumbers')->orderBy('wording')->get();
        $countries = Country::orderBy('name_fr')->get();
        return new JsonResponse([
            'datas' => ['phoneOperators' => $phoneOperators, 'countries' => $countries]
        ], 200);
    }

    public function store(Request $request)
    {
        $this->authorize('ROLE_PHONE_OPERATOR_CREATE', PhoneOperator::class);
        $this->validate(
            $request,
            [
                'country' => 'required',
                'wording' => 'required|unique:phone_operators|max:150',
                'description' => 'max:255',
            ],
            [
                'country.required' => "Le choix d'un pays est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.unique' => "Cet opérateur téléphonique existe déjà.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        try {
            $phoneOperator = new PhoneOperator();
            $phoneOperator->code = Str::random(10);
            $phoneOperator->wording = $request->wording;
            $phoneOperator->description = $request->description;
            $phoneOperator->country_id = $request->country;
            $phoneOperator->save();

            $startNumbers = [];
            if ($request->startNumbers) {
                foreach ($request->startNumbers as $key => $number) {
                    $startNumber = new StartNumber();
                    $startNumber->number = $number;
                    $startNumber->phone_operator_id = $phoneOperator->id;
                    $startNumber->save();

                    array_push($startNumbers, $startNumber);
                }
            }

            $success = true;
            $message = "Enregistrement effectué avec succès.";
            return new JsonResponse([
                'phoneOperator' => $phoneOperator,
                'success' => $success,
                'message' => $message,
                'datas' => ['startNumbers' => $startNumbers]
            ], 200);
        } catch (Exception $e) {
            dd($e);
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
        $this->authorize('ROLE_PHONE_OPERATOR_UPDATE', PhoneOperator::class);
        $phoneOperator = PhoneOperator::findOrFail($id);
        $this->validate(
            $request,
            [
                'country' => 'required',
                'wording' => 'required|max:150',
                'description' => 'max:255',
            ],
            [
                'country.required' => "Le choix d'un pays est obligatoire.",
                'wording.required' => "Le libellé est obligatoire.",
                'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                'description.max' => "La description ne doit pas dépasser 255 caractères."
            ]
        );

        $existingPhoneOperators = PhoneOperator::where('wording', $request->wording)->get();
        if (!empty($existingPhoneOperators) && sizeof($existingPhoneOperators) >= 1) {
            $success = false;
            return new JsonResponse([
                'success' => $success,
                'existingPhoneOperator' => $existingPhoneOperators[0],
                'message' => "L'opérateur téléphonique " . $existingPhoneOperators[0]->wording . " existe déjà"
            ], 200);
        }

        try {
            $phoneOperator->wording = $request->wording;
            $phoneOperator->description = $request->description;
            $phoneOperator->country_id = $request->country;
            $phoneOperator->save();

            StartNumber::where('phone_operator_id', $phoneOperator->id)->delete();

            $startNumbers = [];
            if ($request->startNumbers) {
                foreach ($request->startNumbers as $key => $number) {
                    $startNumber = new StartNumber();
                    $startNumber->number = $number;
                    $startNumber->phone_operator_id = $phoneOperator->id;
                    $startNumber->save();

                    array_push($startNumbers, $startNumber);
                }
            }

            $success = true;
            $message = "Modification effectuée avec succès.";
            return new JsonResponse([
                'phoneOperator' => $phoneOperator,
                'success' => $success,
                'message' => $message,
                'datas' => ['startNumbers' => $startNumbers]
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
        $this->authorize('ROLE_PHONE_OPERATOR_DELETE', PhoneOperator::class);
        $phoneOperator = PhoneOperator::findOrFail($id);
        try {
            $phoneOperator->delete();
            $success = true;
            $message = "Suppression effectuée avec succès.";
            return new JsonResponse([
                'phoneOperator' => $phoneOperator,
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
        $this->authorize('ROLE_PHONE_OPERATOR_READ', PhoneOperator::class);
        $phoneOperator = PhoneOperator::with('startNumbers')->findOrFail($id);
        return new JsonResponse([
            'phoneOperator' => $phoneOperator
        ], 200);
    }
}
