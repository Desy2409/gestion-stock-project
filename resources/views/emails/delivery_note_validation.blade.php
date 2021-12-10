@component('mail::message')
    # Bonjour <br>

    # <u>Objet</u> : Livraison en attente de validation. <br><br>
    # <u>Code</u> : {{ $deliveryNote->code }}</div>

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
                @foreach ($productDeliveryNotes as $productDeliveryNote)
                    <tr>
                        <th scope="row" class="text-center">{{ $i++ }}</th>
                        <td style="margin-left: 50px">{{ $productDeliveryNote->product->wording }}</td>
                        <td class="text-center">{{ $productDeliveryNote->unity->wording }}</td>
                        <td class="text-center">{{ $productDeliveryNote->unit_price }}</td>
                        <td class="text-center">{{ $productDeliveryNote->quantity }}</td>
                        <td class="text-center">{{ $productDeliveryNote->quantity * $productDeliveryNote->unit_price }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


    @endcomponent

    <div class="row" style="margin-top: -20px;">
        <div class="col-md-6">
            @component('mail::button', ['url' => '', 'color' => 'error'])
                Rejeter
            @endcomponent
        </div>
        <div class="col-md-6">
            @component('mail::button', ['url' => '', 'color' => 'success'])
                Valider
            @endcomponent
        </div>
    </div>


    Cordialement,<br>
    L'équipe de {{ config('app.name') }}
@endcomponent
