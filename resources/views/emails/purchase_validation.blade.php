@component('mail::message')
# Bonjour <br>

# <u>Objet</u> : Bon d'achat en attente de validation. <br><br>
# <u>Code</u> : {{ $purchase->code }}</div>
# <u>Référence</u> : {{ $purchase->reference }}</div>

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
        @foreach ($productPurchases as $productPurchase)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td style="margin-left: 50px">{{ $productPurchase->product->wording }}</td>
                <td class="text-center">{{ $productPurchase->unity->wording }}</td>
                <td class="text-center">{{ $productPurchase->unit_price }}</td>
                <td class="text-center">{{ $productPurchase->quantity }}</td>
                <td class="text-center">{{ $productPurchase->unit_price * $productPurchase->quantity }}</td>
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
