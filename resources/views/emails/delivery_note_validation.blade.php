@component('mail::message')
<div class="mb-2"><b>Bonjour</b></div>
<div class="border border-primary rounded">
    <div class="ml-2">
        <div class="mb-2"><u>Objet</u> : Livraison en attente de validation</div>
        <div class="row mb-2">
            <div id="" class="col-5"><u>Code</u> : <b>{{ $deliveryNote->code }}</b></div>
            <div id="" class="col-7"><u>Référence</u> : <b>{{ $deliveryNote->reference }}</b></div>
        </div>
        <table class="table table-bordered table-hover w-75">
            <tr>
                <td rowspan="2" class="align-middle text-center"><b>Info achat</b></td>
                <td class="ml-5">&nbsp;&nbsp;<u>Code</u> : <b>{{ $deliveryNote->purchase->code }}</b></td>
            </tr>
            <tr>
                <td class="ml-5">&nbsp;&nbsp;<u>Référence</u> : <b>{{ $deliveryNote->purchase->reference }}</b></td>
            </tr>
        </table>
    </div>
</div>
@php
    $i = 1;
    $total = 0;
    $url_validate = route('validate_delivery_note', ['id' => $deliveryNote->id]);
    $url_reject = route('reject_delivery_note', ['id' => $deliveryNote->id]);
@endphp

@component('mail::table')
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th scope="col" class="text-center">N°</th>
            <th scope="col" class="text-center">Produit</th>
            <th scope="col" class="text-center">Unité</th>
            <th scope="col" class="text-center">Quantité</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productDeliveryNotes as $productDeliveryNote)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td class="ml-2">&nbsp;&nbsp;{{ $productDeliveryNote->product->wording }}</td>
                <td class="text-center">{{ $productDeliveryNote->unity->wording }}</td>
                <td class="text-center">{{ $productDeliveryNote->quantity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@endcomponent
@if ($deliveryNote->state=="S")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Cette livraison a déjà été validée</h2></span>
    </div>
    @elseif ($deliveryNote->state=="A")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Cette livraison a déjà été annulée</h2></span>
    </div>
@else
<div class="row float-right" style="margin-top: -25px;">
    <div class="col-md-6 float-sm-left">
        @component('mail::button', ['url' => $url_reject,'color'=>'error'])
            Rejeter
        @endcomponent
    </div>
    <div class="col-md-6 float-sm-left">
        @component('mail::button', ['url' => $url_validate,'color'=>'success'])
            Valider
        @endcomponent
    </div>
</div>    
@endif
<br><br><br><br>

Cordialement,<br>
L'équipe de {{ config('app.name') }}
@endcomponent
