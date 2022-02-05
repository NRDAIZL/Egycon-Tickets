@extends('layouts.app')
@section('page')
events
@endsection
@section('title')
Add Event
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Add Event
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
                Name <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('name') }}"
                type="text"
                name="name"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="MIU Gaming Festival"
                />
              </label>
              <label class="block text-sm mt-2">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-image text-xl"></i>
                Logo
                </span>
                <input
                value="{{ old('logo') }}"
                    type="file"
                    name="logo"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-align-justify text-xl"></i>
                Description <span class="text-red-500">*</span>
                </span>
                <textarea 
                class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                required name="description" id="" cols="30" rows="10"
                placeholder="Description"
                >{{ old('description') }}</textarea>
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-hourglass-start text-xl"></i>
                Start Date <span class="text-red-500">*</span>
                </span>
                <input type="date" name="event_start_date" required value="{{ old('event_start_date') }}"
                class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-hourglass-end text-xl"></i>
                End Date <span class="text-red-500">*</span>
                </span>
                <input type="date" name="event_end_date" required value="{{ old('event_end_date') }}"
                class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
              </label>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
              Add Event
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
