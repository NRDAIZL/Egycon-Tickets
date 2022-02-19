<!DOCTYPE html>
<html class="w-full h-full">
<head>
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
<style>
    body{
        font-family:'Gothic';
        background-image: url({{ asset('images/BG.jpg') }});
    }
    form{
        background-image: url({{ asset('images/bg2.png') }});
    }
</style>
</head>

<body class="bg-white w-full h-full flex justify-center items-center">
<div class=" px-4 w-full lg:w-2/3 2xl:w-1/2 mx-auto text-center">
<form method="POST" class="bg-slate-100 border-r-8 border-l-8 border-b-8 border-black shadow-md rounded-lg" enctype="multipart/form-data">
    <div class="w-full h-48 relative">
        <img src="{{ asset('images/header.png') }}" class="w-full h-full object-right-top" alt="">
        <img src="https://gamerslegacy.net/b/logos//Egycon-8.png" class="absolute left-4 hidden sm:block top-1/2 -translate-y-1/2 transform h-2/3 " alt="">
        <img src="{{ asset('images/vodafone cash.png') }}" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 transform h-1/2 lg:h-2/3 " alt="">
            {{-- <img src="{{ asset('logo.png') }}" alt=""> --}}
    </div>
    <div class="py-4 px-4 md:px-8 lg:px-16 xl:px-32">
         @if($errors->any())
    <div class="alert alert-danger">
      @foreach ($errors->all() as $input_error)
        {!! $input_error !!}<br>
      @endforeach 
    </div>
  @endif
  @if(session('status-success'))
    <div class="alert alert-success">
        {{ session('status-success') }}
    </div>
  @endif
  @if(session('status-failure'))
    <div class="alert alert-danger">
        {{ session('status-failure') }}
    </div>
  @endif
@yield('content')
</div>
</form>
</div>
</body>
</html>