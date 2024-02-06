@extends('layouts.app')
@section('page')
edit requests
@endsection
@section('title')
Edit Requests
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
             
          <div class="container grid px-6 mx-auto">
           <br>
            <form action="action" method='post'>  
            @csrf
              <input onload="this.focus();" type="text" name="code" id="search" style="width: 100%;padding: 12px 20px;margin: 8px 0;display: inline-block;border: 1px solid #ccc;border-radius: 4px;box-sizing: border-box;" placeholder="Search By Code" /><br><br>
              
              <input type="submit" class="flex items-center justify-between  px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple" style="display:inline-block;margin-right:5px;" value="Search"/>
              <!-- <input type="submit" name="edit"  style="width: 6.5%; background-color: #4CAF50; border: none;color: white;padding: 6px 10px;text-align: center; text-decoration: none;display: inline-block; font-size: 16px; border-radius: 8px;" value="Edit"> -->
              
            </form>  
            
           <br>
           @if(session()->has('success'))
            <div class="bg-green-200 text-green-600 font-bold text-lg py-4 px-8">
              {{ session()->get('success') }}
            </div>
          @endif
          @if(session()->has('error'))
            <div class="bg-red-200 text-red-600 font-bold text-lg py-4 px-8">
              {{ session()->get('error') }}
            </div>
          @endif
            </div>
          </div>
</main>
<script>
  window.onload = function(){
    document.getElementById('search').focus();
  }
</script>
@endsection