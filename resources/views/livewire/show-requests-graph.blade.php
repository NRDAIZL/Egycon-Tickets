<div>


    {{ $chart->container() }}
     <script src="{{ $chart->cdn() }}"></script>

    {{ $chart->script() }}


    {{-- <button wire:click="$emit('postAdded')">Refresh</button>

    <script>
        document.addEventListener('livewire:load', function () {
            setInterval(function () {
                window.livewire.emit('postAdded');
            }, 5000);
        });
    </script> --}}
</div>
