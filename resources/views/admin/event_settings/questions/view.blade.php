@extends('layouts.app')
@section('page')
event-settings
@endsection
@section('title')
Questions
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Questions
            </h2>
            <a href="{{ route('admin.event_settings.questions.add',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Add Question
            </button></a>
              </div>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Question</th>
                      <th class="px-4 py-3">Type</th>
                      <th class="px-4 py-3">Options</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($questions as $question)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $question->question }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $question->type }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $question->options??"N/A" }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <a href="#">
                          <button class="bg-transparent hover:bg-neutral-200 transition-colors text-green-500 py-2 px-4 rounded-md">
                            <i class="las la-pen text-xl"></i>
                        </button></a>
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
           
          </div>
        </main>

@endsection