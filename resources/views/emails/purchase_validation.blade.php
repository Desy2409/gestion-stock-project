@component('mail::message')
<div class="mb-2"><b>Bonjour</b></div>
<div class="border border-primary rounded">
    <div class="ml-2">
        <div class="mb-2"><u>Objet</u> : Bon d'achat en attente de validation</div>
        <div class="row mb-2">
            <div id="" class="col-5"><u>Code</u> : <b>{{ $purchase->code }}</b></div>
            <div id="" class="col-7"><u>Référence</u> : <b>{{ $purchase->reference }}</b></div>
        </div>
        <table class="table table-bordered table-hover w-75">
            <tr>
                <td rowspan="2" class="align-middle text-center"><b>Info commande</b></td>
                <td class="ml-5">&nbsp;&nbsp;<u>Code</u> : <b>{{ $purchase->order->code }}</b></td>
            </tr>
            <tr>
                <td class="ml-5">&nbsp;&nbsp;<u>Référence</u> : <b>{{ $purchase->order->reference }}</b></td>
            </tr>
        </table>
    </div>
</div>
@php
    $i = 1;
    $total = 0;
    $url_validate = route('validate_purchase', ['id' => $purchase->id]);
    $url_reject = route('reject_purchase', ['id' => $purchase->id]);
@endphp

@component('mail::table')
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th scope="col" class="text-center">N°</th>
            <th scope="col" class="text-center">Produit</th>
            <th scope="col" class="text-center">Unité</th>
            <th scope="col" class="text-center">Prix unitaire</th>
            <th scope="col" class="text-center">Quantité</th>
            <th scope="col" class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productPurchases as $productPurchase)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td class="ml-2">&nbsp;{{ $productPurchase->product->wording }}</td>
                <td class="text-center">{{ $productPurchase->unity->wording }}</td>
                <td class="text-center">{{ $productPurchase->unit_price }}</td>
                <td class="text-center">{{ $productPurchase->quantity }}</td>
                <td class="text-center">{{ $productPurchase->unit_price * $productPurchase->quantity }}</td>
            </tr>
            @php
                $total += $productPurchase->unit_price * $productPurchase->quantity;
            @endphp
        @endforeach
        <tr>
            <td colspan="5" class="text-center"><b>Total</b></td>
            <td class="text-center"><b>{{ $total }}</b></td>
        </tr>
    </tbody>
</table>

@endcomponent
@if ($purchase->state=="S")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Ce bon d'achat a déjà été validé</h2></span>
    </div>
    @elseif ($purchase->state=="A")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Ce bon d'achat a déjà été annulé</h2></span>
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
