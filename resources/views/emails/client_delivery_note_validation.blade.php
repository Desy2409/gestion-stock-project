@component('mail::message')
# Bonjour <br>

# <u>Objet</u> : Livraison en attente de validation. <br><br>
# <u>Code</u> : {{ $clientDeliveryNote->code }}</div>
# <u>Motif</u> : {{ $clientDeliveryNote->request_reason }}

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
            <th scope="col" class="text-center">Quantité</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($productClientDeliveryNotes as $productClientDeliveryNote)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td style="margin-left: 50px">{{ $productClientDeliveryNote->product->wording }}</td>
                <td class="text-center">{{ $productClientDeliveryNote->unity->wording }}</td>
                <td class="text-center">{{ $productClientDeliveryNote->quantity }}</td>
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
