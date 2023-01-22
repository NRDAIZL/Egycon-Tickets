@extends('layouts.app')
@section('page')
events
@endsection
@section('title')
Events
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Events
            </h2>
            <a href="{{ route('admin.events.add') }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Create new Event
            </button></a>
              </div>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Event Name</th>
                      <th class="px-4 py-3">Tickets Sold</th>
                      <th class="px-4 py-3">Admins</th>
                      <th class="px-4 py-3">Duration</th>
                      <th class="px-4 py-3">Start Date</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($events as $event)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $event->name }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $event->getPostsCountAttribute() }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            @php
                              $all_admins = $event->getAdminNamesAttribute();
                              // get the first 3 admins
                              $admins = array_slice($all_admins,0,2);
                              $more_admins = count($all_admins) - 2;
                              if($more_admins > 0){
                                  $more_admins_names = array_slice($all_admins,2);
                                  $more_admins_html = 
                                  "<button class='text-gray-500 dark:text-gray-400 group relative'>
                                    +$more_admins more
                                    <div class='hidden rounded-lg group-focus-within:block absolute left-1/2 w-max max-h-96 overflow-y-auto transform -translate-x-1/2 '>
                                      <div class='bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400 text-sm shadow-lg p-2'>
                                        <ul>
                                          <li class='hover:bg-gray-200 dark:hover:bg-gray-600'>
                                            <a href='#' class='block px-4 py-2 whitespace-nowrap'>".implode('</a></li><li class="hover:bg-gray-200 dark:hover:bg-gray-600"><a href="#" class="block px-4 py-2">',$more_admins_names)."</a>
                                          </li>
                                        </ul>
                                      </div>
                                    </div>
                                    </button>";
                                  array_push($admins,$more_admins_html);
                              }
                            @endphp
                            <p class="font-semibold">{!! implode(', ',$admins) !!}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            @php
                                $duration = $event->getDurationAttribute();
                            @endphp
                            <p class="font-semibold">{{ $duration ?? 'N/A' }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            {{ $event->getStartDateAttribute() }}
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="flex items-center text-sm py-2">
                            <a href="{{ route('admin.home',$event->id) }}">
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
        </main>

@endsection