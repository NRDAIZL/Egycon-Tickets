@extends('layouts.form')
@section('title')
Egycon Tickets
@endsection
@section('content')

  @csrf
  @php
    $i=0;
    $ticket_type_quantities = [];
  @endphp

  @isset($code)
    @php
        if(!is_string($code)){
            $code = $code->code;
        }
    @endphp
    <input type="hidden" name="promo_code" value="{{ $code }}">
  @endisset
  @foreach ($ticket_types as $type)
    <input type="hidden" name="quantity[]" value="{{ $quantity[$i] }}">
    @php
      $ticket_type_quantities[$type->id] = (int) $quantity[$i];
      $i++;
    @endphp
  @endforeach
  <p class="text-white">Payment Method:
    @if ($payment_method == 'vodafone_cash' || old('payment_method') == 'vodafone_cash' )
      Vodafone Cash
    @elseif($payment_method == 'instapay' || old('payment_method') == 'instapay' )
      InstaPay
    @elseif($payment_method == "reservation")
      On Door
    @else
      Credit Card
    @endif
  </p>
  <input type="hidden" name="payment_method" value="{{ old('payment_method')??$payment_method }}">
  <input type="hidden" name="total" value="{{ $total }}" >
  <input type="hidden" name="unique_code" value="{{ uniqid() }}">
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
  @php
      $i = 0;
  @endphp
    @foreach ($ticket_types as $type)
       @if(count($type->sub_ticket_types()->get()) > 0)
        @for($j = 0; $j < $ticket_type_quantities[$type->id]; $j++)
            <label class="text-center text-lg w-full my-2">
                <p class="text-white">Choose Type: {{$type->name}} {{$j+1}}</p>
                <select required name='sub_ticket_{{$type->id}}[]' class="w-full py-2 px-4 border border-slate-400">
                        <option selected disabled value="">Choose Ticket Type</option>
                        @foreach($type->sub_ticket_types()->get() as $sub_ticket)
                        <option value="{{ $sub_ticket->id }}">{{ $sub_ticket->name }}</option>
                        @endforeach
                </select>
            </label>
            @endfor
       @endif
       @php
           $i++;
       @endphp
    @endforeach

  @foreach ($questions as $question)
      {{-- questions can be of type text, radio, checkbox, select --}}
       <div class="text-center text-lg w-full my-2">
        <p class="text-white">{{ $question->question }}</p>
        @if($question->type == "select")
          @php
            $options = explode(",",$question->options);
          @endphp
          <select required name='question_{{ $question->id }}' class="w-full py-2 px-4 border border-slate-400">
            @foreach ($options as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
        @elseif($question->type == "radio")
          @php
            $options = explode(",",$question->options);
          @endphp
          @foreach ($options as $option)
          <label>
            <input required name='question_{{ $question->id }}' value="{{ $option }}" type="radio"> <span class="text-white"> {{ $option }} </span>
          </label>
          @endforeach
        @elseif($question->type == "checkbox")
          @php
            $options = explode(",",$question->options);
          @endphp
          @foreach ($options as $option)
          <label>
            <input  name='question_{{ $question->id }}[]' value="{{ $option }}" type="checkbox"> <span class="text-white"> {{ $option }} </span>
          </label>
          @endforeach
        @else
        <input required value="{{ old("question_{$question->id}") }}"  name='question_{{ $question->id }}' class="w-full py-2 px-4 border border-slate-400" type='{{ $question->type }}'>
        @endif
      </div>
  @endforeach
  @if($payment_method == 'vodafone_cash' || old('payment_method') == 'vodafone_cash' || $payment_method == 'instapay' || old('payment_method') == 'instapay'  )
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
