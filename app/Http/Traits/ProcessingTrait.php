<?php

namespace App\Http\Traits;

trait UtilityTrait
{

    private $stateFirstLevels = ["Order", "Purchase", "DeliveryNote", "PurchaseOrder", "Sale", "ClientDeliveryNote", "TransferDemand"];
    private $stateSecondLevels = [""];

    function processing($model, $id, $action)
    {
        if (in_array($model, $this->stateFirstLevels)) {
            if ($action == "validate") {
                $element = $model::findOrFail($id);
                $element->state = 'S';
                $element->date_of_processing = date('Y-m-d', strtotime(now()));
                $element->save();
            }
            if ($action == "reject") {
                $element = $model::findOrFail($id);
                $element->state = 'A';
                $element->date_of_processing = date('Y-m-d', strtotime(now()));
                $element->save();
            }
        }
        if (in_array($model, $this->stateSecondLevels)) {
            if ($action == "clode") {
                // $element = $model::findOrFail($id);
                // $order->state = 'S';
                // $order->date_of_processing = date('Y-m-d', strtotime(now()));
                // $order->save();
            }
        }
    }
}
