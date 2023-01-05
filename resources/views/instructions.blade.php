@extends('layouts.form')
@section('title')
Egycon Tickets
@endsection
@section('content')
@csrf
<div class="text-left text-white">
  <h1 class="text-2xl mb-8 font- uppercase">1) Please select your desired tickets</h1>
  <div class="flex flex-wrap items-center w-full overflow-auto">
    <table class=" lg:flex-1 mr-4 max-w-full overflow-x-auto lg:min-w-fit w-full border-2 border-white space-x-3 border-separate">
      <tr>
        <th class="border-r-2 text-2xl border-white">Ticket type</th>
        <th class="border-r-2 text-2xl border-white">Person</th>
        <th class="border-r-2 text-2xl border-white">Price/Ticket</th>
        <th class="border-white">Quantity</th>
      </tr>
      @foreach ($ticket_types as $type)
        <tr>
          <td class="border-r-2 text-2xl border-white">{{ $type->name }}</td>
          <td class="border-r-2 text-2xl border-white text-center">{{ $type->person }}</td>
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
      <h1 class="text-2xl font- uppercase mx-2">2) Select your payment method</h1>
      <div class="flex">
        <label class="flex items-center">
          <input type="radio" onchange="document.querySelector('#vodafone-cash-instructions').classList.remove('hidden');" name="payment_method" value="vodafone_cash" class="mr-2">
          <h1 class="text-2xl font- uppercase mx-2">Vodafone Cash</h1>
        </label>
        {{-- <label class="flex items-center ml-4">
          <input type="radio" onchange="document.querySelector('#vodafone-cash-instructions').classList.add('hidden');" name="payment_method" value="credit_card" class="mr-2">
          <h1 class="text-2xl font- uppercase mx-2">Credit Card</h1>
        </label> --}}
      </div>
  </div>
  <div id="vodafone-cash-instructions" class="hidden">
    <div class="flex items-center my-4">
    <h1 class="text-2xl font- uppercase mr-2">3) Transfer the total amount to</h1>
    <div>
      <h1 class="text-2xl font- uppercase mx-2">010 1759 5077</h1>
      <h1 class="text-2xl font- uppercase mx-2">010 2792 7479</h1>
    </div>
    </div>
    <h1 class=" mt-4 text-2xl font- uppercase mr-4">4) Take a photo/screenshot of your reciept then hit continue.</h1>
    <p class="text-xl">*reciept must show date of payment and total amount</p>
  </div>
  
  <br>
  <div class="text-center">
  <button type='submit' class="text-center mx-auto text-xl bg-green-500 mt-4 py-2 px-16 text-black font-bold   hover:bg-green-400 border-4 border-black">
  Continue
  </button>
  </div>
 
 </div>
@endsection
