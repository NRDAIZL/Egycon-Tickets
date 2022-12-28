@extends('layouts.form')
@section('title')
Egycon Tickets
@endsection
@section('content')
  
  @csrf
  @php
    $i=0;
  @endphp
  @foreach ($ticket_types as $type)
      <input type="hidden" name="quantity[]" value="{{ $quantity[$i] }}">
      @php
        $i++;
      @endphp
  @endforeach
  <p class="text-white">Payment Method:
    @if ($payment_method == 'vodafone_cash')
      Vodafone Cash
    @else
      Credit Card
    @endif
  </p>
  <input type="hidden" name="payment_method" value="{{ $payment_method }}">
  <input type="hidden" name="total" value="{{ $total }}" >
  <label class="text-center text-lg w-full my-2">
    <p class="text-white">Full Name</p>
    <input required name='name' value="{{ old('name') }}" placeholder='Name' class="w-full py-2 px-4 border border-slate-400" type='text'>
  </label>
  <label class="text-center text-lg w-full my-2">
    <p class="text-white">Email</p>
    <input required name='email' value="{{ old('email') }}" placeholder='Email' class="w-full py-2 px-4 border border-slate-400" type='text'>
  </label>
  <label class="text-center text-lg w-full my-2">
    <p class="text-white">Phone</p>
    <input required  value="{{ old('phone_number') }}"  name='phone_number' placeholder='Phone Number' class="w-full py-2 px-4 border border-slate-400" type='text'>
  </label>
  @if($payment_method == 'vodafone_cash')
    <label class="text-center w-full my-2">
      <input  name='receipt' id="file" type="file" placeholder='Phone Number' class="hidden" type='text'>
      <div class="bg-yellow-600 cursor-pointer font-bold text-black inline-block py-2 px-4 border-4 border-black hover:bg-yellow-500"><i class="las la-image"></i> <span id="filename">Upload receipt</span></div>
    </label>
    <br>
  <script>
    var file = document.getElementById('file');
    file.onchange = function(){
      document.getElementById('filename').innerHTML = (this.files[0]?this.files[0].name:"Upload receipt");
    }
  </script>
 @endif
  <input type='submit' class="bg-green-500 mt-4 py-2 px-8 text-black font-bold   hover:bg-green-400 border-4 border-black" value='Continue'>

@endsection