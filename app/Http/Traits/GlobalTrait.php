<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Validator;

trait GlobalTrait
{


    // Validation section
    function staticModelValidatonBasedOnCode($mode, $model, $data)
    {
        if ($mode == "store") {
            return Validator::make(
                $data,
                [
                    'code' => 'required|unique:' . $model,
                    'wording' => 'required|unique:' . $model . '|max:150',
                    'description' => 'max:255',
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'code.unique' => "Ce code a déjà été attribué.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Ce libellé existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
        if ($mode == "update") {
            return Validator::make(
                $data,
                [
                    'code' => 'required',
                    'wording' => 'required|max:150',
                    'description' => 'max:255',
                ],
                [
                    'code.required' => "Le code est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
    }

    function staticModelValidatonBasedOnReference($mode, $model, $data)
    {
        if ($mode == "store") {
            return Validator::make(
                $data,
                [
                    'reference' => 'required|unique:' . $model,
                    'wording' => 'required|unique:' . $model . '|max:150',
                    'description' => 'max:255',
                ],
                [
                    'reference.required' => "La référence est obligatoire.",
                    'reference.unique' => "Cette réference a déjà été attribuée.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.unique' => "Ce libellé existe déjà.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
        if ($mode == "update") {
            return Validator::make(
                $data,
                [
                    'reference' => 'required',
                    'wording' => 'required|max:150',
                    'description' => 'max:255',
                ],
                [
                    'reference.required' => "La référence est obligatoire.",
                    'wording.required' => "Le libellé est obligatoire.",
                    'wording.max' => "Le libellé ne doit pas dépasser 150 caractères.",
                    'description.max' => "La description ne doit pas dépasser 255 caractères."
                ]
            );
        }
    }

    function orderValidation()
    {
    }

    function purchaseValidation()
    {
    }

    function deliveryNoteValidation()
    {
    }

    function purchaseOrderValidation()
    {
    }

    function saleValidation()
    {
    }

    function clientDeliveryNoteValidation()
    {
    }
    // End of validation section
}
