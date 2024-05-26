@extends('layouts.app')
@section('page')
profile
@endsection
@section('title')
Profile
@endsection
@section('content')
@section('head')
    <script src="{{ asset('modules/cropperjs-1.6.1/dist/cropper.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('modules/cropperjs-1.6.1/dist/cropper.min.css') }}"></link>
@endsection
<main class="h-full pb-16 overflow-y-auto">
          <div class="container px-6 mx-auto grid">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Edit Profile
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
            {{-- <h1 class="text-lg font-semibold">
                <i class="fab fa-telegram"></i>
                Telegram Notifications
            </h1>
            <h2 class="text-lg">Receive system notifications as telegram messages.</h2>
            --}}

            <!-- General elements -->
            <form method="POST">
                @csrf
                @if($errors->any())
                    {!! implode('', $errors->all('<div class="text-red-500">:message</div>')) !!}
                @endif
            </form>
            <div id="editImageContainer" class="w-64 h-72">
                <img id="userImage" class="max-w-full max-h-full block" src="{{ $user->getAvatar() }}" alt="">
                <label class="text-center w-full my-2">
                    <input accept="image/jpg,image/jpeg,image/png"  name='newUserImage' id="newUserImage" type="file" class="hidden" type='text'>
                    <div class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"><i class="las la-image mr-2"></i> <span id="filename">Upload new image</span></div>
                </label>
            </div>
            <div class="hidden" id="cropperContainer">
                <div class="w-64 h-72">
                    <img id="image" class="max-w-full max-h-full block" src="{{ $user->getAvatar() }}" alt="">
                </div>
                <button id="saveImage" onclick="saveImage()" type="button" class="table items-center mt-4 justify-between px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Save Image
                </button>
            </div>
        </div>
          </div>
        </main>
        <script>
            const imageUploadInput = document.getElementById('newUserImage');
            const image = document.getElementById('image');
            const userImage = document.getElementById('userImage');
            const editImageContainer = document.getElementById('editImageContainer');
            const cropperContainer = document.getElementById('cropperContainer');
            var cropper = null;
            var editImage = function(edited = false){
                if(edited){
                    cropperContainer.classList.add('hidden');
                    editImageContainer.classList.remove('hidden');
                }else{
                    cropperContainer.classList.remove('hidden');
                    editImageContainer.classList.add('hidden');
                }
            }
            var saveImage = function(){
                const newImage = cropper.getCroppedCanvas().toDataURL('image/jpeg')
                userImage.src = newImage;
                image.src = newImage;
                cropper.destroy();
                editImage(true);
            }
            var reader = new FileReader();
            reader.onload = function(r){
                image.src = r.currentTarget.result;
                cropper = new Cropper(image, {
                    aspectRatio: 1 / 1
                });
                editImage();
            }
            imageUploadInput.addEventListener('change', function(){
                const file = this.files[0];
                const type = file.type;
                const acceptedTypes = ["image/jpg", "image/jpeg", "image/png"];
                var validations = [
                    file.size > 5 * 1024 * 1024,  // 5 MB
                    !acceptedTypes.includes(type)
                ]
                var valid = true;
                if(validations[0]){
                    console.log("File to large");
                    valid = false;
                }
                if(validations[1]){
                    console.log("File is not an image");
                    valid = false;
                }
                if(valid)
                    reader.readAsDataURL(file);
            })
        </script>
@endsection
