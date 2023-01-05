@extends('layouts.app')
@section('page')
event-settings
@endsection
@section('title')
Edit Event Days
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Edit Event Days
            </h2>
            
            @if(Session::has('success'))
            <div
              class="flex items-center justify-between px-4 p-2 mb-8 text-sm font-semibold text-green-600 bg-green-100 rounded-lg focus:outline-none focus:shadow-outline-purple"
            >
              <div class="flex items-center">
                <i class="fas fa-check mr-2"></i>
                <span>{{ Session::get('success') }}</span>
              </div>
            </div>
            @endif
            @if(Session::has('error'))
            <div
              class="flex items-center justify-between px-4 p-2 mb-8 text-sm font-semibold text-red-600 bg-red-100 rounded-lg focus:outline-none focus:shadow-outline-purple"
            >
              <div class="flex items-center">
                <i class="fas fa-check mr-2"></i>
                <span>{{ Session::get('error') }}</span>
              </div>
            </div>
            @endif
            <!-- General elements -->
            <form method="POST" enctype="multipart/form-data"
              class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800"
            >
            <span class="text-red-500 text-sm">* Is required</span>

            @csrf
            @if($errors->any())
                {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
            @endif
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-signature text-xl"></i>
                Event Name <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ $event->name }}"
                type="text"
                readonly
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="EGYcon X"
                />
              </label>
              <div id="days-container">
                <div class="hidden" id="days-clone">
                    <h2 class="mt-4 text-xl text-gray-500">
                        Day <span>1</span>
                    </h2>
                    <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">
                    <i class="las la-calendar text-xl"></i>
                    Date
                    </span>
                    <input
                    type="date"
                    name="date[]"
                    class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    />
                </label>
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">
                    <i class="las la-clock text-xl"></i>
                    Start Time
                    </span>
                    <input
                    type="time"
                    name="start_time[]"
                    class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    />
                </label>
                <label class="block text-sm">
                    <span class="text-gray-700 dark:text-gray-400">
                    <i class="las la-clock text-xl"></i>
                    End Time
                    </span>
                    <input
                    type="time"
                    name="end_time[]"
                    class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                    />
                </label>
                </div>
              </div>
              <button type="button" id="add-new-day" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-lg active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                Add New Day
              </button>
              <script>
                var days = {!! json_encode($event->event_days) !!};
                var daysContainer = document.getElementById('days-container');
                var daysClone = document.getElementById('days-clone');
                var daysCloneHtml = daysClone.innerHTML;
                daysClone.remove();
                for (var i = 0; i < days.length; i++) {
                    var day = days[i];
                    var dayClone = document.createElement('div');
                    dayClone.innerHTML = daysCloneHtml;
                    dayClone.querySelector('h2 span').innerHTML = i + 1;
                    dayClone.querySelector('input[name="date[]"]').value = day.date;
                    dayClone.querySelector('input[name="start_time[]"]').value = day.start_time;
                    dayClone.querySelector('input[name="end_time[]"]').value = day.end_time;
                    daysContainer.appendChild(dayClone);
                }
                document.getElementById('add-new-day').addEventListener('click', function() {
                    var dayClone = document.createElement('div');
                    dayClone.innerHTML = daysCloneHtml;
                    dayClone.querySelector('h2 span').innerHTML = daysContainer.querySelectorAll('div').length + 1;
                    daysContainer.appendChild(dayClone);
                });
              </script>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
              Update Event Dates
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
