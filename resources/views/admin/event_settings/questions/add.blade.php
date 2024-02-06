
@extends('layouts.app')
@section('page')
event-settings
@endsection
@section('title')
{{ isset($question)?"Edit":"Add" }} Question
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              {{ isset($question)?"Edit":"Add" }} Question
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
            action="{{ route('admin.event_settings.questions.add',$event_id) }}"
              class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800"
            >
            <span class="text-red-500 text-sm">* Is required</span>

            @csrf
            @if($errors->any())
                {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
            @endif
            @isset($question)
            <input type="hidden" name="id" value="{{ $question->id }}">
            @endisset
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-signature text-xl"></i>
                Question <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('question')??@$question->question??"" }}"
                type="text"
                name="question"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="How did you hear about this event?"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-dollar-sign text-xl"></i>
                Type <span class="text-red-500">*</span>
                </span>
                @php
                  $types = [
                    "text"=>"Text",
                    "number"=>"Number",
                    "email"=>"Email",
                    "date"=>"Date",
                    "time"=>"Time",
                    "select"=>"Select",
                    "radio"=>"Radio",
                    "checkbox"=>"Checkbox",
                  ];
                @endphp
                <select
                name="type"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
                @foreach($types as $key=>$type)
                  <option value="{{ $key }}" {{ (old('type')==$key || (@$question->type == $key && !old('type'))) ?"selected":"" }}>{{ $type }}</option>
                @endforeach
                </select>
              </label>
              <label class="text-sm hidden" id="options">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-users text-xl"></i>
                Options <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('options')??@$question->options??"" }}"
                type="text"
                name="options"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="Social Media, Friends, Newspaper, TV, Radio, Other"
                />
              </label>
              <script>
                var type = document.querySelector('select[name="type"]');
                var options = document.querySelector('#options');
                type.addEventListener('change',function(){
                  if(this.value == "select" || this.value == "radio" || this.value == "checkbox"){
                    options.classList.remove('hidden');
                  }else{
                    options.classList.add('hidden');
                  }
                });
              </script>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
              {{ isset($question)?"Edit":"Add" }} Question
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
