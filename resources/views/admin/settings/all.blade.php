@extends('layouts.app')
@section('page')
settings
@endsection
@section('title')
Settings
@endsection
@section('content')

<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Settings
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
            <div
                class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800 text-gray-700 dark:text-gray-400"
            >
            <h1 class="text-lg font-semibold">
                <i class="fab fa-telegram"></i>
                Telegram Notifications
            </h1>
            <h2 class="text-lg">Receive system notifications as telegram messages.</h2>
            @if($telegramNotificationsEnabled)
                <a href="{{ route('admin.settings.disableTelegram') }}">
                    <button type="button" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-red-600 border border-transparent rounded-lg active:bg-red-600 hover:bg-red-700 focus:outline-none focus:shadow-outline-purple">
                        Disable notifications
                    </button>
                </a>
            @else
                <h2>To enable telegram notifications:</h2>
                <ul class="list-disc pl-4">
                    @php
                        $telegramBotUsername = env('TELEGRAM_BOT_USERNAME');
                    @endphp
                    <li>Send <a target="_blank" href="https://t.me/{{ $telegramBotUsername }}?start={{ $telegramCode }}"><code class="dark:text-white text-black underline" >/start {{ $telegramCode }}</code></a> to 
                        <code class="dark:text-white text-black">&commat;{{{$telegramBotUsername}}}</code>
                    </li>
                    <li>
                        Or just scan the following QR code
                    </li>
                </ul>
                <img class="mt-2" src="{{ $telegramCodeQR }}" alt="">
                <p class="text-sm">Note: This code can be used once.</p>
            @endif
            <!-- General elements -->
            <form method="POST">
                @csrf
                @if($errors->any())
                    {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
                @endif
            </form>
        </div>
          </div>
        </main>
@endsection
