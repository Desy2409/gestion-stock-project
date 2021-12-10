@component('mail::message')
# Bonjour <br>

# <u>Objet</u> : Vente en attente de validation. <br><br>
# <u>Code</u> : {{ $sale->code }}</div>
# <u>Référence</u> : {{ $sale->reference }}</div>

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
        @foreach ($productSales as $productSale)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td style="margin-left: 50px">{{ $productSale->product->wording }}</td>
                <td class="text-center">{{ $productSale->unity->wording }}</td>
                <td class="text-center">{{ $productSale->unit_price }}</td>
                <td class="text-center">{{ $productSale->quantity }}</td>
                <td class="text-center">{{ $productSale->unit_price * $productSale->quantity }}</td>
            </tr>
        @endforeach
        {{-- <tr>
            <td colspan="4">Total</td>
            <td></td>
        </tr> --}}
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
