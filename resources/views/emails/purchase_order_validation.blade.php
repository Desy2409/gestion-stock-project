@component('mail::message')
# Bonjour <br>

# <u>Objet</u> : Bon de commande en attente de validation. <br><br>
# <u>Code</u> : {{ $purchaseOrder->code }}</div>
# <u>Référence</u> : {{ $purchaseOrder->reference }}</div>

@php
    $i = 1;
@endphp

@component('mail::table')
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th scope="col" class="text-center">N°</th>
            <th scope="col" class="text-center">Produit</th>
            <th scope="col" class="text-center">Unité</th>
            <th scope="col" class="text-center">PU</th>
            <th scope="col" class="text-center">Quantité</th>
            <th scope="col" class="text-center">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productPurchaseOrders as $productPurchaseOrder)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td style="margin-left: 50px">{{ $productPurchaseOrder->product->wording }}</td>
                <td class="text-center">{{ $productPurchaseOrder->unity->wording }}</td>
                <td class="text-center">{{ $productPurchaseOrder->unit_price }}</td>
                <td class="text-center">{{ $productPurchaseOrder->quantity }}</td>
                <td class="text-center">{{ $productPurchaseOrder->unit_price * $productPurchaseOrder->quantity }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


@endcomponent

<div class="row" style="margin-top: -20px;">
    <div class="col-md-6">
        @component('mail::button', ['url' => '','color'=>'error'])
            Rejeter
        @endcomponent
    </div>
    <div class="col-md-6">
        @component('mail::button', ['url' => '','color'=>'success'])
            Valider
        @endcomponent
    </div>
</div>


Cordialement,<br>
L'équipe de {{ config('app.name') }}
@endcomponent
