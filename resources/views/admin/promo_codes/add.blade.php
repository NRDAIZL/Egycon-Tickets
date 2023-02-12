
@extends('layouts.app')
@section('page')
promo_codes
@endsection
@section('title')
{{ isset($promo_code)?"Edit":"Add" }} Promo Code
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              {{ isset($promo_code)?"Edit":"Add" }} Promo Code
            </h2>
            
            @include('admin.includes.alerts')

            <!-- General elements -->
            <form method="POST" enctype="multipart/form-data"
            action="{{ route('admin.promo_codes.add',$event_id) }}"
              class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800"
            >
            <span class="text-red-500 text-sm">* Is required</span>

            @csrf
            @if($errors->any())
                {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
            @endif
            @isset($promo_code)
            <input type="hidden" name="id" value="{{ $promo_code->id }}">
            @endisset
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-signature text-xl"></i>
                Code <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('code')??@$promo_code->code??"" }}"
                type="text"
                name="code"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="SAVE50"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-ticket-alt text-xl"></i>
                Ticket Type <span class="text-red-500">*</span>
                </span>
                <select
                name="ticket_type_id"
                  required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
                    <option value="">Select Ticket Type</option>
                    @foreach ($ticket_types as $type)
                    <option
                    @if(old('type')??@$promo_code->ticket_type_id == $type->id) selected
                    @endif
                    value="{{ $type->id }}"
                    data-price="{{ $type->price }}"
                    >
                    {{ $type->name }}
                    </option>
                    @endforeach
                </select>
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-dollar-sign text-xl"></i>
                Discount (Percentage)<span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('discount')??@$promo_code->discount??"" }}"
                type='number'
                max="100"
                oninput="this.value = (this.value > 100) ? 100 : (this.value < 0 ? 0 : this.value)"
                min="0"
                name="discount"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="50"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-users text-xl"></i>
                Max Uses <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('max_uses')??@$promo_code->max_uses??"" }}"
                type="number"
                name="max_uses"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="100"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                <i class="las la-power-off text-xl"></i>
                Is Active? <span class="text-red-500">*</span>
                </span>
                <select
                name="is_active"
                  required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
                    <option value="">Select Status</option>
                    <option
                    @if(old('is_active')??@$promo_code->is_active == 1) selected
                    @endif
                    value="1"
                    >
                    Yes
                    </option>
                    <option
                    @if(old('is_active')??@$promo_code->is_active == 0) selected
                    @endif
                    value="0"
                    >
                    No
                    </option>
                </select>
              </label>
              <div>
                <p>Price after discount:</p>
                <p id="price_after_discount">0</p>
              </div>
              <script>
                var price = 0;
                var discount = 0;
                var price_after_discount = 0;
                var ticket_type = document.querySelector('select[name="ticket_type_id"]');
                var discount_input = document.querySelector('input[name="discount"]');
                var price_after_discount_element = document.querySelector('#price_after_discount');
                ticket_type.addEventListener('change',function(){
                    calculate_discount();
                });
                discount_input.addEventListener('input',function(){
                    calculate_discount();
                });
                discount_input.addEventListener('change',function(){
                    calculate_discount();
                });
                function calculate_discount(  ){
                    price = ticket_type.options[ticket_type.selectedIndex].dataset.price;
                    discount = discount_input.value;
                    price_after_discount = price - (price * discount / 100);
                    price_after_discount_element.innerHTML = price_after_discount;
                }
                @isset($promo_code)
                calculate_discount();
                @endisset
              </script>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
              {{ isset($promo_code)?"Edit":"Add" }} Promo Code
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
