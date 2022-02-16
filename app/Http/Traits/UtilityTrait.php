<?php

namespace App\Http\Traits;

use App\Models\Institution;
use App\Models\TableSetting;
use Illuminate\Http\Request;

trait UtilityTrait
{

    function formateNPosition($prefix, $suffixe, $length)
    {
        $valueString = $prefix;
        $valueLength = strlen($valueString . $suffixe);
        while ($valueLength < $length) {
            $valueString = $valueString . '0';
            $valueLength = strlen($valueString . $suffixe);
        }
        return $valueString . $suffixe;
    }

    function getInstitutionSettings($id)
    {
        // dd(Institution::class);
        $tableNames = [
            "App\Models\Poduct", "App\Models\Client", "App\Models\Provider",
            "App\Models\Order", "App\Models\Purchase", "App\Models\DeliveryNote", "App\Models\PurchaseOrder",
            "App\Models\Sale", "App\Models\ClientDeliveryNote", "App\Models\TransferDemand", //"App\Models\Transfer",
            "App\Models\RemovalOrder", "App\Models\Tourn"
        ];
        $institution = Institution::findOrFail($id);
        $settings = $institution->settings;

        foreach ($tableNames as $key => $tableName) {
            $tableSetting = TableSetting::where('table_name', '=', $tableName)->first();
            $othersSettings = [];
            if ($tableSetting) {
                $othersSettings = [
                    'validation_number' => $tableSetting->validation_number,
                    'code_min_length' => $tableSetting->code_min_length,
                    'validation_level' => $tableSetting->validation_level,
                ]; # code...
            }
            //$this->tableRelatedSettings($tableName);
            $settings = array_merge($settings, $othersSettings);
        }

        return $settings;
    }

    function saveSettings(Request $request, $mode, $tableName)
    {
        if ($mode == 'store') {
            $tableSetting = new TableSetting();
        }
        if ($mode == 'update') {
            $tableSetting = TableSetting::where('table_name', '=', $tableName)->first();
        }
        switch ($tableName) {
            case "App\Models\Poduct":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_prd;
                break;

            case "App\Models\Client":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_clt;
                break;

            case "App\Models\Provider":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_prv;
                break;

            case "App\Models\Order":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_or;
                $tableSetting->validation_number = $request->validation_number_or;
                break;

            case "App\Models\Purchase":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_pu;
                break;

            case "App\Models\DeliveryNote":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_dn;
                break;

            case "App\Models\PurchaseOrder":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_po;
                $tableSetting->validation_number = $request->validation_number_po;
                break;

            case "App\Models\Sale":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_sa;
                break;

            case "App\Models\ClientDeliveryNote":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_cdn;
                break;

            case "App\Models\TransferDemand":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_td;
                $tableSetting->validation_number = $request->validation_number_td;
                break;

            case "App\Models\RemovalOrder":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_ro;
                break;

            case "App\Models\Tourn":
                $tableSetting->table_name = $tableName;
                $tableSetting->code_min_length = $request->code_min_length_to;
                break;

            default:

                break;
        }
        $tableSetting->save();

        return $tableSetting;
    }

    // function updateSettings($tableName, $codeMinLength, $validationNumber, $validationLevel)
    // {
    //     $tableSetting = TableSetting::where('table_name', '=', $tableName)->first();
    //     $oldTableSetting = $tableSetting;
    //     $tableSetting->code_min_length = $codeMinLength;
    //     $tableSetting->validation_number = $validationNumber;
    //     $tableSetting->validation_level = $validationLevel;
    //     $tableSetting->save();

    //     if ($oldTableSetting->validation_level != $validationLevel) {
    //     }

    //     return $tableSetting;
    // }

    function createPageOperationBasedOnValidationLevel($model, $validationLevel)
    {
        for ($i = 0; $i < $validationLevel; $i++) {
            $role = "VALIDATION_" . $model::$code . "_LEVEL_" . $i;
        }
    }

    // function saveSettings($ùode,$tableName){
    //     // $tableSetting = TableSetting::where('table_name', '=', $tableName)->first();

    //     $tableSetting = new TableSetting();
    //     switch ($tableName) {
    //         case "App\Models\Poduct":

    //             break;

    //         case "App\Models\Client":

    //             break;

    //         case "App\Models\Provider":

    //             break;

    //         case "App\Models\Order":

    //             break;

    //         case "App\Models\Purchase":

    //             break;

    //         case "App\Models\DeliveryNote":

    //             break;

    //         case "App\Models\PurchaseOrder":

    //             break;

    //         case "App\Models\Sale":

    //             break;

    //         case "App\Models\ClientDeliveryNote":

    //             break;

    //         case "App\Models\TransferDemand":

    //             break;

    //         case "App\Models\Transfer":

    //             break;

    //         case "App\Models\RemovalOrder":

    //             break;

    //         case "App\Models\Tourn":

    //             break;

    //         default:

    //             break;
    //     }
    // }
}
