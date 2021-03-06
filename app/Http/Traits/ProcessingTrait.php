<?php

namespace App\Http\Traits;

trait ProcessingTrait
{

    private $firstLevelOfStates = ["Order", "Purchase", "DeliveryNote", "PurchaseOrder", "Sale", "ClientDeliveryNote", "TransferDemand"];
    private $secondLevelOfStates = ["Tourn"];

    function processing($model, $id, $action)
    {
        // dd($model, $id, $action,str_replace("App\Models\\",'',$model));
        if (in_array(str_replace("App\Models\\",'',$model), $this->firstLevelOfStates)) {
            if ($action == "validate") {
                // dd('validate');
                $element = $model::findOrFail($id);
                $element->state = 'S';
                $element->date_of_processing = date('Y-m-d', strtotime(now()));
                $element->save();
            }
            if ($action == "reject") {
                // dd('reject');
                $element = $model::findOrFail($id);
                $element->state = 'A';
                $element->date_of_processing = date('Y-m-d', strtotime(now()));
                $element->save();
            }
        }
        if (in_array($model, $this->secondLevelOfStates)) {
            if ($action == "close") {
                // $element = $model::findOrFail($id);
                // $order->state = 'S';
                // $order->date_of_processing = date('Y-m-d', strtotime(now()));
                // $order->save();
            }
        }
    }
}
