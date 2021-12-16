@component('mail::message')
<div class="mb-2"><b>Bonjour</b></div>
<div class="border border-primary rounded">
    <div class="ml-2">
        <div class="mb-2"><u>Objet</u> : Vente en attente de validation</div>
        <div class="row mb-2">
            <div id="" class="col-5"><u>Code</u> : <b>{{ $sale->code }}</b></div>
            <div id="" class="col-7"><u>Référence</u> : <b>{{ $sale->reference }}</b></div>
        </div>
        <table class="table table-bordered table-hover w-75">
            <tr>
                <td rowspan="2" class="align-middle text-center"><b>Info commande</b></td>
                <td class="ml-5">&nbsp;&nbsp;<u>Code</u> : <b>{{ $sale->purchaseOrder->code }}</b></td>
            </tr>
            <tr>
                <td class="ml-5">&nbsp;&nbsp;<u>Référence</u> : <b>{{ $sale->purchaseOrder->reference }}</b></td>
            </tr>
        </table>
    </div>
</div>
@php
    $i = 1;
    $total = 0;
    $url_validate = route('validate_sale', ['id' => $sale->id]);
    $url_reject = route('reject_sale', ['id' => $sale->id]);
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
        @foreach ($productSales as $productSale)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td class="ml-2">&nbsp;&nbsp;{{ $productSale->product->wording }}</td>
                <td class="text-center">{{ $productSale->unity->wording }}</td>
                <td class="text-center">{{ $productSale->unit_price }}</td>
                <td class="text-center">{{ $productSale->quantity }}</td>
                <td class="text-center">{{ $productSale->unit_price * $productSale->quantity }}</td>
            </tr>
            @php
                $total += $productSale->unit_price * $productSale->quantity;
            @endphp
        @endforeach
        <tr>
            <td colspan="5" class="text-center"><b>Total</b></td>
            <td class="text-center"><b>{{ $total }}</b></td>
        </tr>
    </tbody>
</table>

@endcomponent
@if ($sale->state=="S")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Cette vente a déjà été validée</h2></span>
    </div>
    @elseif ($sale->state=="A")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Cette vente a déjà été annulée</h2></span>
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
