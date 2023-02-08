<!DOCTYPE html>
<html class="w-full h-full">
<head>
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
  @php
    $theme_color = "#c207db";
    $registration_form_background_color = "#c207db";
    $registration_page_header_image = "images/header.png";
    $registration_page_background = "images/BG.jpg";
    $logo = "images/logo.png";
    if(isset($theme)){
        $theme_color = $theme->theme_color;
        $registration_form_background_color = $theme->registration_form_background_color;
        if($theme->registration_page_header_image != null)
          $registration_page_header_image = Storage::url($theme->registration_page_header_image);
        if($theme->registration_page_background_image != null)
          $registration_page_background = Storage::url($theme->registration_page_background_image);
    }
    if(isset($event)){
      if($event->logo){
          $logo = Storage::url($event->logo);
      }
    }
  @endphp
  <style>
      body{
          font-family:'Gothic';
          background-image: url({{ asset($registration_page_background) }});
          background-size: cover;
          background-repeat: no-repeat;
          background-position: center;
          
      }
      .form-bg{
          background-color: {{ $registration_form_background_color }};
          /* background-image: url({{ asset('images/bg2.png') }}); */
      }
  </style>
</head>

<body class="bg-white w-full h-full flex justify-center">
<div class=" px-4 w-full lg:w-2/3 2xl:w-1/2 mx-auto text-center">
  <div class=" w-full relative my-4">
        <img src="{{ asset($registration_page_header_image) }}" class="w-full h-full object-right-top" alt="">
        {{-- <img src="{{ asset( $logo) }}" class="absolute left-1/2 hidden sm:block top-1/2 -translate-y-1/2 -translate-x-1/2 transform h-2/3 " alt=""> --}}
        {{-- <img src="{{ asset('images/vodafone cash.png') }}" class="absolute left-1/2 top-1/2 -translate-y-1/2 -translate-x-1/2 transform h-1/2 lg:h-2/3 " alt=""> --}}
            {{-- <img src="{{ asset('logo.png') }}" alt=""> --}}
    </div>
  <div class="form-bg bg-slate-100 border-8 border-white shadow-md">
    
<form method="POST" class="w-full"  enctype="multipart/form-data">
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

<div class="mt-8 text-center text-white">
    
  <p>Address: Villa 48 Narges 3, 5th settlement - New Cairo.</p>
  <p>
  Phone/WhatsApp: <a href="tel:+201017595077" class="text-white">+201017595077</a> | <a href="tel:+201027927479" class="text-white">+201027927479</a>
  </p>
  <div class="mt-4">
      <a target="_blank" href="facebook.com/egycon.official" class=" text-white"><i class="lab la-facebook text-4xl"></i></a>
      <a target="_blank" href="instagram.com/egycon.official" class=" text-white"><i class="lab la-instagram text-4xl"></i></a>
  </div>
</div>
</div>
</form>
<div class="py-4">
@yield('content-outsideform')
</div>
  </div>

</div>
</body>
</html>