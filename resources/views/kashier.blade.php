
@extends('layouts.form')
@section('title')
Egycon Tickets
@endsection
@section('content-outsideform')
  @csrf
  <h1 class="text-2xl text-white">
    Please review your order information before proceeding to payment:
  </h1>
  <table class="w-full border border-solid border-white my-4">
    <thead>
      <tr>
        <th class="text-white">First Name</th>
        <th class="text-white">Last Name</th>
        <th class="text-white
        ">Email</th>
        <th class="text-white">Phone</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-white">{{ $data->user_first_name }}</td>
        <td class="text-white">{{ $data->user_last_name }}</td>
        <td class="text-white">{{ $data->user_email }}</td>
        <td class="text-white">{{ $data->user_phone }}</td>
      </tr>
    </tbody>
  </table>

      @php
        function generateKashierOrderHash($order,$event_payment_method){
            $mid = $event_payment_method->account_name; //your merchant id
            $amount = $order->amount; //eg: 100
            $currency = $order->currency; //eg: "EGP"
            $orderId = $order->merchantOrderId; //eg: 99, your system order ID
            $secret = $event_payment_method->account_number;
            $path = "/?payment=".$mid.".".$orderId.".".$amount.".".$currency;
            $hash = hash_hmac( 'sha256' , $path , $secret ,false);
            return $hash;
        }
        $order = new stdClass();
        $order->amount = $data->amount;
        $order->currency = $data->currency;
        $order->merchantOrderId = $data->order_reference_id;
        $hash = generateKashierOrderHash($order,$event_payment_method);
      @endphp
        <h1 class="text-2xl text-white mb-2">
      Order total: {{$order->amount}} {{$order->currency}}
  </h1>
     <script
  id="kashier-iFrame"
  src="https://checkout.kashier.io/kashier-checkout.js"
  data-amount="{{ $order->amount }}"
  data-hash="{{ $hash }}"
  data-currency="{{ $order->currency }}"
  data-orderId="{{ $order->merchantOrderId }}"
  data-merchantId="{{ $event_payment_method->account_name }}"
  data-merchantRedirect="{{ route('payment-success',['x_event_id'=>$data->event_id]) }}"
  data-mode="live"
  data-metaData='{{ 
  json_encode(
    [
      "user_first_name" => $data->user_first_name,
      "user_last_name" => $data->user_last_name,
      "user_email" => $data->user_email,
      "user_phone" => $data->user_phone,
      "order_id" => $data->order_id,
      'event_id' => $data->event_id,
    ]
  ) }}'
  data-redirectMethod="get"
  data-failureRedirect="false"
  data-allowedMethods="card,wallet"
  data-type="external"
  data-brandColor="{{ $theme->theme_color??"#b407db" }}"
  data-display="en"
  ></script>

@endsection