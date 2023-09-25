@extends('layouts.app')
@section('page')
promo-codes
@endsection
@section('title')
Promo Code Tickets
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">

            @include('admin.includes.alerts')

              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Promo Codes Tickets ({{ $promo_code->code }})

            </h2>
            <div>
            <a href="{{ route('admin.promo_codes.generate',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Generate Promo Codes
            </button></a>
            <a href="{{ route('admin.promo_codes.add',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Add Promo Code
            </button></a>
            </div>
              </div>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                   
                    <tr>
                      <th class="px-4 py-3">Name</th>
                      <th class="px-4 py-3">Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    
                    @foreach ($tickets as $ticket)
                     <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                        <td class="px-4 py-3 text-center">{{ $ticket->name }}</td>
                        <td class="px-4 py-3 text-center">{{ $ticket->count }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            <div class="mt-4">
            </div>
          </div>
        </main>

@endsection