@extends('layouts.form')
@section('title')
Thank you for registering at {{ $event->name??"The Event" }}!
@endsection
@section('content')
<div class="flex justify-center items-center h-full w-full py-32 text-white">
    @if(isset($status_success))
    <h1 class="text-3xl text-center">
        {{ $status_success }}
     </h1>
    @elseif(isset($status_error))
     <h1 class="text-3xl text-center">
        {{ $status_error }}
     </h1>
    @else
     <h1 class="text-3xl text-center">
        Your request is being reviewed. Once approved you will receive your ticket(s) via E-mail.
         <br></h1>
    @endif
</div>
 @endsection
