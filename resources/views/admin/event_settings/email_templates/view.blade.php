@extends('layouts.app')
@section('page')
event-settings
@endsection
@section('title')
Tickets
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
              <div class="flex justify-between items-center">

            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Email Templates
            </h2>
            <a href="{{ route('admin.event_settings.template',$event_id) }}"><button class="bg-purple-600 text-white py-2 px-8 rounded-md">
                Add Email Template
            </button></a>
              </div>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Subject</th>
                      <th class="px-4 py-3">Type</th>
                      <th class="px-4 py-3">Preview</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($templates as $template)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $template->subject }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $template->type }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">
                                <textarea class="hidden">{{ $template->body }}</textarea>
                                <button class="py-2 px-4 bg-green-500 text-white preview-button">Preview</button>
                            </p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                       
                        <a href="{{ route('admin.event_settings.template.edit',['event_id'=>$event_id,'type'=>$template->type]) }}">
                          <button class="bg-transparent hover:bg-neutral-200 transition-colors text-green-500 py-2 px-4 rounded-md">
                            <i class="las la-pen text-xl"></i>
                        </button></a>
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
            {{-- <div class="mt-4">
             {{$ticket_types->links('pagination::tailwind')}}
            </div> --}}
          </div>
        </main>

        <script>
            var preview_button = document.querySelectorAll('.preview-button');
            preview_button.forEach(button => {
                button.addEventListener('click',function(){
                    var textarea = this.previousElementSibling;
                    var body = textarea.value;
                    var win = window.open('', 'Preview', 'width=600,height=600');
                    win.document.write(body);
                })
            })
        </script>

@endsection