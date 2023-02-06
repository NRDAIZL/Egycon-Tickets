@extends('layouts.form')
@section('title')
Egycon Tickets
@endsection
@section('content')
@csrf
<div class="text-left text-white">
  <h1 class="text-2xl mb-8">1) Select your Ticket/s.</h1>
  <div class="flex flex-wrap items-center w-full overflow-auto">
    <table class=" mr-4 max-w-full overflow-x-auto lg:min-w-fit w-full border-2 border-white space-x-3 border-separate">
      <tr>
        <th class="border-r-2 text-2xl border-white">Ticket type</th>
        <th class="border-r-2 text-2xl border-white">Person</th>
        <th class="border-r-2 text-2xl border-white">No. of Days</th>
        <th class="border-r-2 text-2xl border-white">Price/Ticket</th>
        <th class="border-white">Quantity</th>
      </tr>
      @foreach ($ticket_types as $type)
        <tr>
          <td class="border-r-2 text-2xl border-white">{{ $type->name }}</td>
          <td class="border-r-2 text-2xl border-white text-center">{{ $type->person }}</td>
          <td class="border-r-2 text-2xl border-white text-center">{{ 
          $type->scan_type == "once_per_day" ? $type->event_days()->count() : 1
          }}</td>
          <td class="border-r-2 text-2xl border-white">{{ $type->price }}</td>
          <td class="border-white"><input type="number" data-price="{{ $type->price }}" data-quantity="0" value="0"  minlength="0" max="10" min="0" maxlength="2" class="quantity w-16 text-black text-left" name="quantity[]"></td>
        </tr>
      @endforeach
    </table>
    <div class="border-4 h-12 p-2  border-black text-lg bg-purple-900 text-white">
      TOTAL: <span id="price">0</span> EGP
    </div>
    <script>
      let total=0;
      let quantity_inputs = document.getElementsByClassName('quantity');
      let total_price = document.getElementById('price');
      for(let i = 0; i < quantity_inputs.length; i++){
        quantity_inputs[i].addEventListener('change',function(){
          let price = this.getAttribute('data-price');
          let difference = this.value - parseInt(this.getAttribute('data-quantity'));
          this.setAttribute('data-quantity',this.value);
          total += difference*parseInt(price);
          total_price.innerHTML = total;
        });
      }
    </script>
    
  </div>
  <div class="mt-4">
      <h1 class="text-2xl mx-2">2) Choose your payment method.</h1>
      <div class="flex">
        @php
          $payment_methods_group = $payment_methods->groupBy('payment_method_id');
        @endphp
        @foreach ($payment_methods_group as $payment_method)
        @php
          $payment_method = $payment_method->first();
          // get payment methods where name is vodafone cash
          $vodafone_cash = $payment_methods->where('name','Vodafone Cash')->all();
        @endphp
          <label class="flex items-center">
            <input type="radio" 
              @if($payment_method->name == "Vodafone Cash")
                onchange="document.querySelector('#vodafone-cash-instructions').classList.remove('hidden');"
              @else
                onchange="document.querySelector('#vodafone-cash-instructions').classList.add('hidden');"
              @endif
            name="payment_method" value="{{ str_replace(' ','_',strtolower($payment_method->name)) }}" class="mr-2">
            <h1 class="text-2xl mx-2">{{ $payment_method->name }}</h1>
          </label>
        @endforeach
        <label class="flex items-center">
            <input type="radio" 
            disabled
             class="mr-2">
            <h1 class="text-2xl mx-2 opacity-70">Credit Card (Soon)</h1>
          </label>
        {{-- <label class="flex items-center ml-4">
          <input type="radio" onchange="document.querySelector('#vodafone-cash-instructions').classList.add('hidden');" name="payment_method" value="credit_card" class="mr-2">
          <h1 class="text-2xl mx-2">Credit Card</h1>
        </label> --}}
      </div>
  </div>
  @if(count($vodafone_cash ?? []) > 0)
  <div id='vodafone-cash-instructions' class="hidden">
    <div class="flex items-center my-4">
    <h1 class="text-2xl mr-2">3) Transfer the total amount to</h1>
    <div>
      @foreach ($vodafone_cash as $vodafone_c)
      <h1 class="text-2xl font-bold  mx-2">
        {{-- format number add space between numbers  --}}
        {{ implode(' ', str_split($vodafone_c->account_number, 4)) }}
      </h1>
      @endforeach
    </div>
    </div>
    <h1 class=" mt-4 text-2xl mr-4">4) Take a photo/screenshot of your reciept then hit continue.</h1>
    <p class="text-xl">*reciept must show date of payment and total amount</p>
  </div>
  @endif
  <br>
  <div class="text-center">
  <button type='submit' class="text-center w-64 mx-2 text-xl bg-green-500 mt-4 py-2 text-black font-bold   hover:bg-green-400 border-4 border-black">
  Continue
  </button>
    <button type='button' disabled class="text-center w-64 mx-2 text-xl bg-green-500 hover:bg-green-400 mt-4 py-2 text-black font-bold border-4 border-black opacity-50">
      I have a code
    </button>
 
 </div>
@endsection
