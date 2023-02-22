@extends('layouts.app')
@section('page')
dashboard
@endsection
@section('title')
Dashboard
@endsection
@section('content')
        <main class="h-full overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Dashboard
            </h2>
            <!-- Cards -->
            <h3 class="text-xl text-gray-700 dark:text-gray-200">
              {{ $event->name }} Insights
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 2xl:grid-cols-4 gap-4 my-4 flex-wrap">
                <livewire:cards.total-tickets :event_id="$event_id" />
                <livewire:cards.total-requests :event_id="$event_id" />
                <livewire:cards.accepted-requests :event_id="$event_id" />
                <livewire:cards.successful-scans :event_id="$event_id" />
            </div>
            <div class="text-center py-12 w-full xl:w-2/3 mx-auto dark:text-white">
               {{-- <div class="w-48 h-48 text-center flex items-center justify-center mx-auto">
                <img src="{{ asset('logo.png') }}" class="w-full h-full object-contain" alt="">
              </div> --}}
            
            <div class="dark:bg-neutral-300 text-black overflow-hidden">
              <livewire:show-requests-graph :event_id="$event_id" />
              <livewire:show-scans-graph :event_id="$event_id" />
            </div>
{{-- 
              <h1 class="text-3xl font-bold my-2 ">
                Welcome {{ ucfirst(explode(' ',Auth::user()->name)[0]) }},
              </h1>
              <p class="text-2xl">
                This is <span class="text-purple-700 dark:text-purple-500 font-bold">Egycon Tickets</span>, a system crafted for Egycon! <br>
              </p> --}}
              {{-- <p class="text-xl my-4 ">You can change your password from the <a href="#" class="text-purple-500 underline">settings</a></p> --}}
            </div>
            <!-- New Table -->
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                {{-- <x-belongings-table :belongings=$belongings ></x-belongings-table> --}}
              </div>
              <div
              class="mt-4"
              >
                  {{-- {{ $belongings->links() }} --}}
                
            </div>

          </div>
        </main>
 @endsection