
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ticket - {{ $post->name }}</title>
</head>
<body>
    <table>
        <tr>
            <td class="text-2xl ">Name: </td>
            <td class="text-2xl font-bold">{{ $post->name }}</td>
        </tr>
        <tr>
            <td class="text-2xl ">Email: </td>
            <td class="text-2xl font-bold ">{{ $post->email }}</td>
        </tr>
        <tr>
            <td class="text-2xl ">Phone: </td>
            <td class="text-2xl font-bold">{{ $post->phone_number }}</td>
        </tr>
        <tr>
            <td class="text-2xl ">Order ID: </td>
            <td class="text-2xl font-bold">{{ $post->id }}</td>
        </tr>
        <tr>
            <td class="text-2xl ">Tickets Count: </td>
            <td class="text-2xl font-bold">{{ $post->ticket->count() }}</td>
        </tr>
        <tr>
            <td class="text-2xl ">Tickets: </td>
            <td class="text-2xl font-bold">
                 @php
                    $similar = [];
                    $similar_person = [];
                    foreach ($post->ticket as $ticket){
                    if(!isset($similar[$ticket->ticket_type->name])){
                        $similar[$ticket->ticket_type->name] = 1;
                        $similar_person[$ticket->ticket_type->name] = $ticket->ticket_type->person;
                    }else{
                        $similar[$ticket->ticket_type->name]++;
                    }
                    }
                    $tickets = [];
                    foreach ($similar as $key=>$value){
                        $tickets[] =  $value/$similar_person[$key] . " " . $key;
                    }
                   
                    @endphp
                    {{ implode(", ", $tickets) }}
            </td>
        </tr>
        <tr>
            <td class="text-2xl ">Total Price: </td>
            <td class="text-2xl font-bold">
                @php
                    if($post->total_price == null)
                    $price = $post->ticket->pluck('ticket_type')->pluck('price')->sum();
                    else
                    $price = $post->total_price;
                @endphp
                {{ $price }}
            </td>
        </tr>
    </table>
    {{-- Display tickets QR Code --}}
    @foreach ($post->ticket as $ticket)
        <p>{{ $ticket->ticket_type->name }} - ID: {{ $ticket->id }}</p>
        @if($ticket->code != null)
            <img src="{{ asset('images/qrcodes/'.$ticket->code.'.jpg') }}" alt="">
        @elseif($ticket->discount_code_id != null)
            {{ $ticket->discount_code->code }}
        @endif
        <br/>
    @endforeach
</body>
</html>
