@component('mail::message')
<div class="mb-2"><b>Bonjour</b></div>
<div class="mb-2">
    <u>Objet</u> : Bon de commande en attente de validation
</div>
<div class="row">
    <div id="" class="col-5"><u>Code</u> : <b>{{ $order->code }}</b></div>
    <div id="" class="col-7"><u>Référence</u> : <b>{{ $order->reference }}</b></div>
</div>
@php
    $i = 1;
    $total = 0;
    $url_validate = route('validate_order', ['id' => $order->id]);
    $url_reject = route('reject_order', ['id' => $order->id]);
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
        @foreach ($productOrders as $productOrder)
            <tr>
                <th scope="row" class="text-center">{{ $i++ }}</th>
                <td class="ml-2">{{ $productOrder->product->wording }}</td>
                <td class="text-center">{{ $productOrder->unity->wording }}</td>
                <td class="text-center">{{ $productOrder->unit_price }}</td>
                <td class="text-center">{{ $productOrder->quantity }}</td>
                <td class="text-center">{{ $productOrder->unit_price * $productOrder->quantity }}</td>
            </tr>
            @php
                $total += $productOrder->unit_price * $productOrder->quantity;
            @endphp
        @endforeach
        <tr>
            <td colspan="5" class="text-center"><b>Total</b></td>
            <td class="text-center"><b>{{ $total }}</b></td>
        </tr>
    </tbody>
</table>

@endcomponent
@if ($order->state=="S")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Ce bon de commande a déjà été validé</h2></span>
    </div>
    @elseif ($order->state=="A")
    <div class="text-right">
        <span class="badge badge-info my-auto"><h2>Ce bon de commande a déjà été annulé</h2></span>
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
