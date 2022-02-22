<?php

namespace App\Http\Traits;

use App\Models\Client;
use App\Models\ClientDeliveryNote;
use App\Models\DeliveryNote;
use App\Models\Institution;
use App\Models\Order;
use App\Models\PageOperation;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\RemovalOrder;
use App\Models\TableSetting;
use App\Models\Tourn;
use App\Models\Transfer;
use App\Models\TransferDemand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

trait UtilityTrait
{

    public $modelsConcernBySetting = [
        Product::class, Client::class, Provider::class,
        Order::class, Purchase::class, DeliveryNote::class, PurchaseOrder::class,
        Sale::class, ClientDeliveryNote::class, TransferDemand::class, //Transfer::class,
        RemovalOrder::class, Tourn::class
    ];

    public $modelsConcernByValidationNumber = [Order::class, PurchaseOrder::class, TransferDemand::class];

    function formateNPosition_old($prefix, $suffixe, $length)
    {
        $valueString = $prefix;
        $valueLength = strlen($valueString . $suffixe);
        while ($valueLength < $length) {
            $valueString = $valueString . '0';
            $valueLength = strlen($valueString . $suffixe);
        }
        return $valueString . $suffixe;
    }

    function formateNPosition($model, $suffixe)
    {
        $tableSetting = TableSetting::where('table_name', '=', $model)->first();
        $valueString = $model::$code;
        $valueLength = strlen($valueString . $suffixe);
        $length = $tableSetting ? $tableSetting->code_min_length : env('DEFAULT_CODE_MIN_LENGTH');
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
                    // 'validation_level' => $tableSetting->validation_level,
                ];
            }
            //$this->tableRelatedSettings($tableName);
            $settings = array_merge($settings, $othersSettings);
        }

        return $settings;
    }

    function saveTableSetting(Request $request)
    {
        if (!empty($request->newTableSettings) && sizeof($request->newTableSettings) > 0) {
            foreach ($request->newTableSettings as $key => $newTableSetting) {
                // dd($newTableSetting['validation_number']);
                $oldTableSetting = TableSetting::where('table_name', '=', $newTableSetting['table_name'])->first();
                if (!$oldTableSetting) {
                    $tableSetting = new TableSetting();
                    $tableSetting->table_name = $newTableSetting['table_name'];
                    $tableSetting->code_min_length = $newTableSetting['code_min_length'] ? $newTableSetting['code_min_length'] : env('DEFAULT_CODE_MIN_LENGTH');
                    if (in_array($newTableSetting['table_name'], $this->modelsConcernByValidationNumber)) {
                        $tableSetting->validation_number = $newTableSetting['validation_number'];
                        $tableSetting->validation_reminder = $newTableSetting['validation_reminder'];
                        $this->createPageOperationBasedOnValidationLevel($newTableSetting['table_name'], $newTableSetting['validation_number']);
                    }
                    $tableSetting->save();
                } else {
                    $oldTableSetting->table_name = $newTableSetting['table_name'];
                    $oldTableSetting->code_min_length = $newTableSetting['code_min_length'] ? $newTableSetting['code_min_length'] : env('DEFAULT_CODE_MIN_LENGTH');
                    if (in_array($newTableSetting['table_name'], $this->modelsConcernByValidationNumber)) {
                        $oldTableSetting->validation_number = $newTableSetting['validation_number'];
                        $oldTableSetting->validation_reminder = $newTableSetting['validation_reminder'];
                    }
                    $oldTableSetting->save();
                }
            }
        }
    }

    function createPageOperationBasedOnValidationLevel($model, $validationNumber)
    {
        // dd("createPageOperationBasedOnValidationLevel");
        for ($i = 0; $i < $validationNumber; $i++) {
            // $role = "VALIDATION_" . $model::$code . "_LEVEL_" . $i;
            $pageOperation = new PageOperation();
            $pageOperation->code = "INSTITUTION_VALIDATION_LEVEL_" . $i;
            $pageOperation->title = "Niveau de validation " . $i;
            $pageOperation->role = "ROLE_VALIDATION_" . $model::$code . "_LEVEL_" . $i;
            $pageOperation->save();
        }
    }

    function getAllModels(): array
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $modelsWithDefaultPath = [];
        foreach ((array)data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            $modelsWithDefaultPath = array_merge(collect(File::allFiles(base_path($path)))
                ->map(function ($item) use ($namespace) {
                    $path = $item->getRelativePathName();
                    return sprintf(
                        '\%s%s',
                        $namespace,
                        strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                    );
                })
                ->filter(function ($class) {
                    $valid = false;
                    if (class_exists($class)) {
                        $reflection = new \ReflectionClass($class);
                        $valid = $reflection->isSubclassOf(\Illuminate\Database\Eloquent\Model::class) &&
                            !$reflection->isAbstract();
                    }
                    return $valid;
                })
                ->values()
                ->toArray(), $modelsWithDefaultPath);
        }

        $models = [];

        foreach ($modelsWithDefaultPath as $key => $model) {
            array_push($models, substr($model, 12));
        }

        dd($models);
        return $models;
    }
}
