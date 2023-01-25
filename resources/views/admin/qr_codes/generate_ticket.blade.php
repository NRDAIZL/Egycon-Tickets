@extends('layouts.app')
@section('page')
qr_codes
@endsection
@section('title')
Generate Tickets
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Generate Tickets
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
                QR Codes Amount to Generate (max: 200 per batch) <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('quantity') }}"
                type="number"
                name="quantity"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="10"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Ticket Type <span class="text-red-500">*</span>
                </span>
                <select
                name="ticket_type_id"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
                @foreach ($ticket_types as $ticket_type)
                    <option value="{{ $ticket_type->id }}">{{ $ticket_type->name }}</option>
                @endforeach
                </select>
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Template <span class="text-red-500">*</span>
                </span>
                 <input
                type="file"
                accept="image/jpeg,image/png,image/jpg"
                name="template"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                />
              </label>
              <label class="block text-sm mt-4">
                <span class="text-gray-700 dark:text-gray-400">
                Serial Start Number <span class="text-gray-500">(leave empty for no serialization)</span>
                </span>
                 <input
                type="number"
                name="start_number"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                />
              </label>
              <button type="submit" id="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Generate Tickets
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>
        <div class="col-md-12">
		<p>&nbsp;</p>
	    <p>&nbsp;</p>
		<div id="progressbar" style="border:1px solid #ccc; border-radius: 5px; "></div>
  
		<!-- Progress information -->
		<br>
		<div id="information" ></div>
	</div>
<iframe id="loadarea" style="display:none;"></iframe><br />

          </div>
        </main>
        <script>
            var progress_interval = null;
            var submit_button = document.getElementById('submit');
            submit_button.addEventListener('click', function() {
                progress_interval = setInterval(() => {
                  // create iframe element
                  var iframe = document.createElement('iframe');
                  iframe.style.display = 'none';
                  var progressbar = document.getElementById('progressbar');
                  console.log(progressbar.style.width);
                  if(progressbar.style.width == '100%') {
                    clearInterval(progress_interval);
                    return;
                  }
                  iframe.src = "{{ route('admin.qr_progress',$event_id) }}";
                  document.body.appendChild(iframe);
                }, 3000);
            });
          
        </script>
@endsection
