
@extends('layouts.app')
@section('page')
tickets
@endsection
@section('title')
{{ isset($ticket_type)?"Edit":"Add" }} Ticket
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              {{ isset($ticket_type)?"Edit":"Add" }} Ticket
            </h2>
            
            @include('admin.includes.alerts')
            <!-- General elements -->
            <form method="POST" enctype="multipart/form-data"
            action="{{ route('admin.tickets.add',$event_id) }}"
              class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800"
            >
            <span class="text-red-500 text-sm">* Is required</span>

            @csrf
            @if($errors->any())
                {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
            @endif
            @isset($ticket_type)
            <input type="hidden" name="id" value="{{ $ticket_type->id }}">
            @endisset
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-signature text-xl"></i>
                Ticket Name <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('name')??@$ticket_type->name??"" }}"
                type="text"
                name="name"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="Early Ninja"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-dollar-sign text-xl"></i>
                Ticket Price <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('price')??@$ticket_type->price??"" }}"
                type="number"
                min="0"
                name="price"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="220"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-users text-xl"></i>
                Number of Persons <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('persons')??@$ticket_type->person??"" }}"
                type="number"
                name="persons"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="1"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-ticket-alt text-xl"></i>
                Ticket Type <span class="text-red-500">*</span>
                </span>
                <select
                name="type"
                  required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
                <option value="qr" @if(old('type') == "qr" || @$ticket_type->type == "qr") selected @endif>QR Code</option>
                <option value="discount" @if(old('type') == "discount" || @$ticket_type->type == "discount") selected @endif>Discount Code</option>
                <option value="noticket" @if(old('type') == "noticket" || @$ticket_type->type == "noticket") selected @endif>No Ticket (Just registers on the system, user won't get any ticket)</option>
                <option value="reservation" @if(old('type') == "reservation" || @$ticket_type->type == "reservation") selected @endif>Reservation (Sends email confirmation without QR Code on purchase)</option>
                </select>
              </label>
              <div class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-calendar text-xl"></i>
                Event Days <span class="text-red-500">*</span>
                </span><br>
                <div class="px-8">
                  @foreach ($event_days as $day)
                  <label class="py-4 block dark:text-white">
                    @php
                      $days = @$ticket_type->event_days;
                      if($days){
                        $days = $days->pluck('id')->toArray();
                      }
                    @endphp
                    <input
                    type="checkbox"
                    name="event_days[]"
                    @if(in_array($day->id, old('event_days')??
                    $days
                    ??[])) checked @endif
                    value="{{ $day->id }}"
                    />
                    {{ $day->date }} 
                  </label>
                @endforeach
                </div>
              </div>
              <div class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Scan Type <span class="text-red-500">*</span>
                </span><br>
                <div class="px-8">
                  <label class="py-4 block dark:text-white">
                    <input
                    type="radio"
                    name="scan_type"
                    required
                    @if(old('scan_type') == "once" || @$ticket_type->scan_type == "once") checked @endif
                    value="once"
                    />
                    Once
                  </label>
                  <label class="py-4 block dark:text-white">
                    <input
                    type="radio"
                    name="scan_type"
                    required
                    @if(old('scan_type') == "once_per_day" || @$ticket_type->scan_type == "once_per_day") checked @endif
                    value="once_per_day"
                    />
                    Once Per Day
                  </label>
                </div>
              </div>
              <div class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Is Visible? <span class="text-red-500">*</span>
                </span><br>
                <div class="px-8">
                  <label class="py-4 block dark:text-white">
                    <input
                    type="radio"
                    name="is_visible"
                    required
                    @if(old('is_visible') == "1" || @$ticket_type->is_visible == "1") checked @endif
                    value="1"
                    />
                    Yes
                  </label>
                  <label class="py-4 block dark:text-white">
                    <input
                    type="radio"
                    name="is_visible"
                    required
                    @if(old('is_visible') == "0" || @$ticket_type->is_visible == "0") checked @endif
                    value="0"
                    />
                    No
                  </label>
                </div>
              </div>
              <div class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Is Disabled?   <span class="text-red-500">*</span>
                </span><br>
                <span class="text-sm text-gray-700 dark:text-gray-400">Note: disabling a ticket won't hide it from the payment form unless you set it to not visible</span><br>
                <div class="px-8">
                  <label class="py-4 block dark:text-white">
                    <input
                    type="radio"
                    name="is_disabled"
                    required
                    @if(old('is_disabled') == "1" || @$ticket_type->is_disabled == "1") checked @endif
                    value="1"
                    />
                    Yes
                  </label>
                  <label class="py-4 block dark:text-white">
                    <input
                    type="radio"
                    name="is_disabled"
                    required
                    @if(old('is_disabled') == "0" || @$ticket_type->is_disabled == "0") checked @endif
                    value="0"
                    />
                    No
                  </label>
                </div>
              </div>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
              {{ isset($ticket_type)?"Edit":"Add" }} Ticket
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
