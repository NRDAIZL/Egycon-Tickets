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