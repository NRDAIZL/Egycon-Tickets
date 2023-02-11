@extends('layouts.app')
@section('page')
promo-codes
@endsection
@section('title')
Promo Codes
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Promo Codes
            </h2>
            <a href="{{ route('admin.promo_codes.add',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Add Promo Code
            </button></a>
              </div>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Promo Code</th>
                      <th class="px-4 py-3">Ticket Type</th>
                      <th class="px-4 py-3">Discount</th>
                      <th class="px-4 py-3">Max Uses</th>
                      <th class="px-4 py-3">Uses</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($promo_codes as $promo_code)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $promo_code->code }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $promo_code->ticket_type->name }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $promo_code->discount }}%</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $promo_code->max_uses }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $promo_code->uses }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                            @if($promo_code->is_active)
                                <div class="py-1 px-8 rounded-full bg-green-200 text-green-500 dark:bg-green-700 dark:text-green-300">
                                    <p class="font-semibold">Active</p>
                                </div>
                                @else
                                <div class="py-1 px-8 rounded-full bg-red-200 text-red-500 dark:bg-red-700 dark:text-red-300">
                                    <p class="font-semibold">Inactive</p>
                                </div>
                            @endif
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <a 
                        href="{{ route('admin.promo_codes.edit', ['id'=>$promo_code->id,'event_id'=>$event_id]) }}"
                        >
                          <button class="bg-transparent hover:bg-neutral-200 transition-colors text-green-500 py-2 px-4 rounded-md">
                            <i class="las la-pen text-xl"></i>
                        </button></a>
                        <a 
                        href="{{ route('admin.promo_codes.delete', ['id'=>$promo_code->id,'event_id'=>$event_id]) }}"
                            >
                          <button class="bg-transparent hover:bg-neutral-200 transition-colors text-red-500 py-2 px-4 rounded-md">
                            <i class="las la-trash-alt text-xl"></i>
                        </button></a>
                      </td>
                    
                    </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
            <div class="mt-4">
             {{$promo_codes->links('pagination::tailwind')}}
            </div>
          </div>
        </main>

@endsection