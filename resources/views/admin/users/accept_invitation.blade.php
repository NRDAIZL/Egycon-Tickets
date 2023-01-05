<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="h-screen overflow-hidden flex items-center justify-center" style="background: #edf2f7;">
    <form method="POST" class="bg-grey-lighter w-full min-h-screen flex flex-col">
            @csrf
            <div class=" w-full max-w-lg mx-auto flex-1 flex flex-col items-center justify-center px-2">
                <div class="bg-white px-6 py-8 rounded shadow-md text-black w-full">
                    <p class="text-center text-lg mb-4 bg-green-200 p-4 rounded-md border border-green-700 text-green-700">
                        You have been invited to access {{ $invitation->event()->first()->name }}.<br> Please create an account to accept the invitation.
                    </p>
                    @if(Session::has('status'))
                        <p class="text-red-500">{{ Session::get('status') }}</p>
                    @endif
                    @if(Session::has('error'))
                        <p class="text-red-500">{{ Session::get('error') }}</p>
                    @endif
                    @if($errors->any())
                        {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
                    @endif
                    <h1 class="mb-8 text-3xl text-center">Create your account</h1>
                    <input 
                        type="text"
                        class="block border border-grey-light w-full p-3 rounded mb-4"
                        name="name"
                        placeholder="Full Name" />

                    <input 
                        type="email"
                        class="block border border-grey-light w-full p-3 rounded mb-4 read-only:bg-gray-200"
                        name="email"
                        readonly
                        value="{{ $invitation->email }}"
                        placeholder="Email" />

                    <input 
                        type="password"
                        class="block border border-grey-light w-full p-3 rounded mb-4"
                        name="password"
                        placeholder="Password" />
                    <input 
                        type="password"
                        class="block border border-grey-light w-full p-3 rounded mb-4"
                        name="password_confirmation"
                        placeholder="Confirm Password" />

                    <button
                        type="submit"
                        class="w-full text-center py-3 rounded bg-green-500 text-white hover:bg-green-dark focus:outline-none my-1"
                    >Create Account</button>

                </div>
            </div>
        </form>
</body>
</html>
