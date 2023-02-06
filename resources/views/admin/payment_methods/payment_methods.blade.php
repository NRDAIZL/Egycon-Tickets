@extends('layouts.app')
@section('page')
event-settings
@endsection
@section('title')
Payment Methods
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Payment Methods
            </h2>
            <a href="{{ route('admin.payment_methods.create',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Add Payment Method
            </button></a>
              </div>
            
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Name</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Info</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($payment_methods as $payment_method)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $payment_method->name }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <p>
                            @if ($payment_method->is_active == 1)
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full dark:bg-red-700 dark:text-red-100">
                                    Inactive
                                </span>
                            @endif
                          </p>
                        </div>
                      </td>
                      
                      <td>
                        <div class="flex items-center text-sm py-2">
                            <a href="#">
                            <button
                                class="flex items-center group disabled:hover:bg-inherit disabled:cursor-not-allowed  hover:bg-gray-300 dark:hover:bg-gray-600 justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                                aria-label="Switch"
                            >
                                <i class="las la-arrow-right text-xl group-disabled:text-gray-500 text-green-500"></i>
                            </button>
                            </a>
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            {{-- <div class="mt-4">
             {{$events->links('pagination::tailwind')}}
            </div> --}}
          </div>
            {{-- <div class="mt-4">
             {{$events->links('pagination::tailwind')}}
            </div> --}}
          </div>
        </main>

@endsection