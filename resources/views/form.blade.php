<!DOCTYPE html>
<html class="w-full h-full">
<head>
    <title>Egycon Tickets Form</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
</head>
<body class="bg-white w-full h-full flex justify-center items-center">
  
  <div class="w-1/2 mx-auto text-center">
<form method="POST" class="py-4 px-32 bg-slate-100 shadow-md rounded-lg" enctype="multipart/form-data">
  <div class="container mt-4">
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
  @csrf
<h2 class="text-black text-3xl font-bold">Reserve your spot at Egycon 9 now!</h2>
  <label class="text-left w-full my-2">
    <p class="text-slate-600">Name</p>
    <input required name='name' value="{{ old('name') }}" placeholder='Name' class="w-full py-2 px-4 border border-slate-400" type='text'>
  </label>
  <label class="text-left w-full my-2">
    <p class="text-slate-600">Email</p>
    <input required name='email' value="{{ old('email') }}" placeholder='Email' class="w-full py-2 px-4 border border-slate-400" type='text'>
  </label>
  <label class="text-left w-full my-2">
    <p class="text-slate-600">Phone</p>
    <input required  value="{{ old('phone_number') }}"  name='phone_number' placeholder='Phone Number' class="w-full py-2 px-4 border border-slate-400" type='text'>
  </label>
   <label class="text-left w-full my-2">
    <p class="text-slate-600">Ticket Type</p>
    <select name="ticket_type_id"
    class="w-full py-2 px-4 border border-slate-400"
    >
      <option value="" disabled selected>Please Select a Type</option>
      @foreach ($ticket_types as $ticket_type)
        <option value="{{ $ticket_type->id }}">{{ $ticket_type->name }} - {{ $ticket_type->price }} EGP</option>
      @endforeach
  </select>
  </label>
  <label class="text-left w-full my-2">
    <p class="text-slate-600">Receipt</p>
    <input  name='receipt' id="file" type="file" placeholder='Phone Number' class="hidden" type='text'>
    <div class="bg-pink-600 cursor-pointer text-white inline-block py-2 px-4 rounded-md hover:bg-pink-500"><i class="las la-image"></i> <span id="filename">Upload receipt</span></div>
  </label>
  <br>
  <input type='submit' class="bg-green-500 mt-4 py-2 px-8 text-white hover:bg-green-400 rounded-md" value='Continue'>
</form>
 </div>
 <script>
   var file = document.getElementById('file');
   file.onchange = function(){
     document.getElementById('filename').innerHTML = (this.files[0]?this.files[0].name:"Upload receipt");
   }
 </script>
</body>
</html>