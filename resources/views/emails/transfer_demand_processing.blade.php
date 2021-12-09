@component('mail::message')
# Bonjour <br>

# <u>Objet</u> : Demande de transfert en attente de validation. <br><br>
# <u>Code</u> : {{ $transferDemand->code }}</div>
# <u>Motif</u> : {{ $transferDemand->request_reason }}

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
        @foreach ($productsTransfersDemandsLines as $productTransferDemandLine)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td style="margin-left: 50px">{{ $productTransferDemandLine->product->wording }}</td>
                <td class="text-center">{{ $productTransferDemandLine->unity->wording }}</td>
                <td class="text-center">{{ $productTransferDemandLine->quantity }}</td>
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
