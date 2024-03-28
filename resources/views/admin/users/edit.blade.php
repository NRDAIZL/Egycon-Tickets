@extends('layouts.app')
@section('page')
users
@endsection
@section('title')
Edit User
@endsection
@section('content')
<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Edit User
            </h2>
            @if(Session::has('success'))
            <div
              class="flex items-center justify-between px-4 p-2 mb-8 text-sm font-semibold text-green-600 bg-green-100 rounded-lg focus:outline-none focus:shadow-outline-purple"
            >
              <div class="flex items-center">
                <i class="fas fa-check mr-2"></i>
                <span>{!! Session::get('success') !!}</span>
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
                  value="{{ old('name') ?? $user->name }}"
                  type="text"
                  name="name"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="John Doe"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Email <span class="text-red-500">*</span>
                </span>
                <input
                  value="{{ old('email') ?? $user->email }}"
                  type="email"
                  name="email"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="john@doe.com"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Role <span class="text-red-500">*</span>
                </span>
                <select
                  @if($self_user)
                    style="cursor: not-allowed" 
                  @endif
                  name="role"
                  class=" {{ $self_user ? "cursor-not-allowed" : "" }} cursor-not-allowed block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                >
                  @if($self_user)
                      <option value="{{ $user->roles()->first()->id }}">{{ ucfirst($user->roles()->first()->name) }}</option>
                  @else
                    @php
                      $current_user_role = $user->roles()->first()->id ?? null;
                    @endphp
                    @foreach($roles as $role)
                      <option {{ $current_user_role == $role->id ? "selected":"" }}  value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                  @endif
              </select>
              @if ($self_user)
                <p class="text-sm font-bold text-gray-700 dark:text-gray-400">
                  You can not change your role
                </p>
              @endif
              </label>
             <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Password
                </span>
                <input
                  type="password"
                  name="password"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="P@$$W0RD!23"
                />
              </label>
              <label class="block text-sm">
                <span class="text-gray-700 dark:text-gray-400">
                Confirm Password 
                </span>
                <input
                  type="password"
                  name="password_confirmation"
                  class="block w-full mt-1 text-sm border dark:border-gray-600 dark:bg-gray-700 focus:border-purple-400 focus:outline-none focus:shadow-outline-purple dark:text-gray-300 dark:focus:shadow-outline-gray form-input"
                  placeholder="P@$$W0RD!23"
                />
              </label>
              <button type="submit" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                Edit User
              <span class="ml-2" aria-hidden="true">
                  <i class='las la-arrow-right'></i>
              </span>
            </button>
        </form>

          </div>
        </main>
@endsection
