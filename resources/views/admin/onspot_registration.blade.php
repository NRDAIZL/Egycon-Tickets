@extends('layouts.app')
@section('page')
register
@endsection
@section('title')
On-site Registration
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              On-site Registration
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
                Name <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('name') }}"
                type="text"
                name="name"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="John Doe"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Email <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('email') }}"
                type="email"
                name="email"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="john@doe.com"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Phone <span class="text-red-500">*</span>
                </span>
                <input
                value="{{ old('phone') }}"
                type="text"
                name="phone"
                    required
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="01123456789"
                />
              </label>
              <div class="block text-sm my-4">
                <span class="text-gray-700 text-xl mb-4 dark:text-gray-400">
                Tickets <span class="text-red-500">*</span>
                </span>
                <table>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Quantity</th>
                    </tr>
                @foreach ($ticket_types as $ticket_type)
                    <tr>
                        <td>
                            <label>
                                <input class="disabled:bg-gray-300" onclick="removeAddQuantityValue()" type="checkbox" name="ticket_type[]" value="{{ $ticket_type->id }}">
                                <span class="ml-2 text-gray-700 dark:text-gray-400">{{ $ticket_type->name }}</span>
                            </label>
                        </td>
                        <td>
                            <input oninput="calculateTotalPrice()" data-price="{{ $ticket_type->price }}" class="w-24 disabled:bg-gray-300" disabled type="number" name="quantity[]" >
                        </td>
                    </tr>
                @endforeach
                </table>
              </div>
              <div class="bg-green-200 border border-green-800 text-green-800 p-4 rounded-md">
                <p>Total Price</p>
                <p id="total_price">0</p>
              </div>
              <script>
                function removeAddQuantityValue(){
                    var ticket_type = document.getElementsByName('ticket_type[]');
                    var quantity = document.getElementsByName('quantity[]');
                    for (var i = 0; i < ticket_type.length; i++) {
                        if (ticket_type[i].checked) {
                            quantity[i].disabled = false;
                            if(quantity[i].value == ""){
                                quantity[i].value = 1;
                            }
                        } else {
                            quantity[i].value = "";
                            quantity[i].disabled = true;
                        }
                    }
                    calculateTotalPrice();
                }
                function calculateTotalPrice(){
                    var ticket_type = document.getElementsByName('ticket_type[]');
                    var quantity = document.getElementsByName('quantity[]');
                    var total_price = 0;
                    for (var i = 0; i < ticket_type.length; i++) {
                        if (ticket_type[i].checked) {
                            total_price += quantity[i].value * quantity[i].dataset.price;
                        }
                    }
                    document.getElementById('total_price').innerHTML = total_price;
                }
              </script>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Invite User
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
