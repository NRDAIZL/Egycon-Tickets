@extends('layouts.app')
@section('page')
codes
@endsection
@section('title')
Codes
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Ticket Codes
            </h2>
            <a href="{{ route('admin.codes.add',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Add Code
            </button></a>
              </div>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Code</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Claimed By</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($discount_codes as $code)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $code->code }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $code->claimed_at?"Claimed":"Available" }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $code->ticket->post->name??"N/A" }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        @if($code->trashed())
                        <a href="{{ route('admin.codes.restore', $code->id) }}"><button class="bg-green-500 text-white py-2 px-8 rounded-md">
                            Restore
                        </button></a>
                        @else
                        <a href="{{ route('admin.codes.delete', $code->id) }}">
                          <button class="bg-red-600 text-white py-2 px-8 rounded-md">
                            Delete
                        </button></a>
                        @endif
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
            <div class="mt-4">
             {{$discount_codes->links('pagination::tailwind')}}
            </div>
          </div>
        </main>

@endsection