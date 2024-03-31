@extends('layouts.app')
@section('page')
requests
@endsection
@section('title')
Requests @isset($promo_code) ({{ $promo_code->code }}) @endisset

@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
            {{-- if on promo_codes page hide search bar --}}
            @if(!isset($promo_code))
            <form action="">
                <div class="flex  my-4">
                  <button class=" w-14 rounded-l-md flex items-center justify-center dark:bg-slate-800 border-l border-t border-b border-gray-800"> <i class="las la-search text-xl text-purple-500 "></i> </button>
                  <input value="{{ $query }}" name="q" placeholder="Search phone, email, order id" type="text" class="w-full py-2 px-4  flex-1  dark:bg-slate-800 rounded-r-md dark:text-white border-t border-r border-b border-l-0 border-gray-800 ">
                </div>
            </form>
            @endif
            @include('admin.includes.alerts')
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Requests @isset($promo_code) ({{ $promo_code->code }}) @endisset @isset($requests_keyword) ({{ $requests_keyword }}) @endisset
              <a href="{{ route('admin.requests.export',['event_id'=>$event_id, "query"=>"null",'ticket_id'=>$ticket_type_id ?? null]) }}">
                <button class="ml-4 py-1 px-4 text-sm rounded-md bg-purple-500 hover:bg-purple-600 text-white"> 
                  <i class="las la-download"></i> 
                  Export {!! !empty($requests_keyword) ? "<b>(".$requests_keyword.")</b>" : "All"!!} Requests
                </button>
              </a>
              @if(!empty($query))
              <a href="{{ route('admin.requests.export', ["event_id"=>$event_id, "query"=>$query, 'ticket_id'=>$ticket_type_id ?? null]) }}">
                <button class="ml-4 py-1 px-4 text-sm rounded-md bg-purple-500 hover:bg-purple-600 text-white"> 
                  <i class="las la-download"></i> 
                  Export {!! !empty($requests_keyword) ? "<b>(".$requests_keyword.")</b>" : "All"!!} <b>Searched</b> Requests
                </button>
              </a>
              @endif
            </h2>

            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Order ID</th>
                      <th class="px-4 py-3">Registrant</th>
                      <th class="px-4 py-3">Email</th>
                      <th class="px-4 py-3">Phone</th>
                      <th class="px-4 py-3">Receipt</th>
                      <th class="px-4 py-3">Ticket Type</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Date</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($requests as $request)

                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{!! StringUtils::highlight($request->id, $query) !!}</p>
                            {{-- <p class="font-semibold">{!! str_ireplace("$query","<span class='bg-yellow-100'>$query</span>",$request->id) !!}</p> --}}
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{!! StringUtils::highlight($request->name, $query) !!}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{!! StringUtils::highlight($request->email, $query) !!}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{!! StringUtils::highlight($request->phone_number, $query) !!}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-sm">
                            @if($request->picture)
                        <div class="w-12 h-12 relative">

                            <div class=" bg-black absolute w-full h-full top-0 left-0 flex justify-center items-center">
                                <i class="las la-search-plus text-xl"></i>
                            </div>
                            <img src="{{ asset('images/'.$request->picture) }}" class="transition-all absolute top-0 left-0 flex justify-center items-center opacity-100 hover:opacity-40 w-full h-full object-cover" alt="">
                          </div>
                          @elseif ($request->payment_method == "credit_card")
                            Order Reference: <br>
                            <b>
                              {!! StringUtils::highlight($request->order_reference_id, $query) !!}
                            <b>
                          @else
                          N/A
                            @endif
                      </td>
                      <td class="px-4 py-3 text-sm">
                        <a class="hover:underline" href="{{ route('admin.view_tickets',['id'=>$request->id,'event_id'=>$event_id]) }}">
                          @php
                            $similar = [];
                            $similar_person = [];

                            $tickets = [];
                          foreach ($request->ticket as $ticket){
                            if(!isset($ticket->ticket_type)){
                                    $tickets[] = "N/A";
                                    continue;
                            }
                            if(isset($ticket->sub_ticket_type) && !isset($ticket->ticket_type->name_chaned)){
                              $ticket->ticket_type->name = $ticket->ticket_type->name . " " . StringUtils::wrapWithParentheses($ticket->sub_ticket_type->name);
                              $ticket->ticket_type->name_chaned = true;
                            }
                            if(!isset($similar[$ticket->ticket_type->name])){

                              $similar[$ticket->ticket_type->name] = 1;

                              $similar_person[$ticket->ticket_type->name] = $ticket->ticket_type->person;
                            }else{
                              $similar[$ticket->ticket_type->name]++;
                            }
                          }

                          foreach ($similar as $key => $value) {
                            $tickets[] = $value/$similar_person[$key] . " " . StringUtils::highlight($key, $query);
                          }
                          @endphp
                            {!! implode(',',$tickets) !!}
                            @if($request->promo_code_id)
                            <br>
                            <span class="text-xs text-gray-500">Promo Code: {!! StringUtils::highlight($request->promo_code->code, $query) !!}</span>
                            @endif
                        </a>
                      </td>
                      <td class="px-4 py-3 text-xs">
                        @php
                          if($request->external_service_provider_payment_method == 'opay'){
                            // $payment = new \Nafezly\Payments\Classes\OpayPayment();
                            // $payment = $payment->verify(
                            //   new \Illuminate\Http\Request(
                            //     [
                            //       'reference_id'=>$request->external_service_provider_order_id,
                            //     ]
                            //   )
                            // );
                            // print_r($payment);
                          }
                        @endphp
                        @if($request->status === null)
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-yellow-600 bg-yellow-100 rounded-full dark:bg-yellow-600 dark:text-yellow-100"
                        >
                          Pending
                        </span>
                        @elseif($request->status == 1)
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-green-600 bg-green-100 rounded-full dark:bg-green-600 dark:text-green-100"
                        >
                          Approved


                        </span>
                        @if($request->provider)
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-black-600 bg-green-100 rounded-full dark:bg-green-600 dark:text-green-100"
                        >
                        {!! StringUtils::highlight($request->provider->name, $query) !!}
                        </span>
                        @endif
                        @else
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-red-600 bg-red-100 rounded-full dark:bg-red-600 dark:text-red-100"
                        >
                          Declined
                        </span>
                        @endif
                        @if($request->external_service_provider_payment_method == 'opay')
                        <span class="font-bold mx-2">
                          Reference: {{ $request->external_service_provider_order_id }}
                        </span>
                        @endif
                      </td>
                      <td class="px-4 py-3 text-sm">
                        {{ date('Y/m/d h:i A',strtotime($request->created_at)) }}
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center space-x-4 text-sm">
                            <button
                            @if ($request->status !== null)
                                disabled
                            @else
                            onclick="display_popup(this)"
                            data-title="Are you sure you want to ACCEPT {{ explode(' ',$request->name)[0] }}'s request?"
                            data-content="By continuing, you ensure that this request is completely accepted and cannot be undone. An email will be sent to them confirming their request and providing a QR Code image to be able to enter the event!"
                            data-action="{{ route('admin.accept',['id'=>$request->id,'event_id'=>$event_id]) }}"
                            @endif
                            class="flex items-center group disabled:hover:bg-inherit disabled:cursor-not-allowed  hover:bg-gray-300 dark:hover:bg-gray-600 justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400  focus:shadow-outline-gray"
                            aria-label="Accept"
                          >
                            <i class="las la-check text-xl group-disabled:text-gray-500 text-green-500"></i>
                          </button>
                          <button
                            @if ($request->status !== null)
                                disabled
                            @else
                            onclick="display_popup(this)"
                            data-title="Are you sure you want to REJECT {{ explode(' ',$request->name)[0] }}'s request?"
                            data-content="By continuing, you ensure that this request is completely rejected and cannot be undone. An email will be sent to them informing them with the status of their request!"
                            data-action="{{ route('admin.reject',['id'=>$request->id,'event_id'=>$event_id]) }}"
                            @endif
                            class="flex items-center group disabled:hover:bg-inherit disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400  focus:shadow-outline-gray"
                            aria-label="Reject"
                          >
                            <i class="las la-times text-xl group-disabled:text-gray-500 text-red-500"></i>
                          </button>
                          <button
                            @if ($request->status !== null)
                                disabled
                            @else
                            onclick="display_popup(this)"
                            data-title="Are you sure you want to DELETE {{ explode(' ',$request->name)[0] }}'s request?"
                            data-content="By continuing, you ensure that this request is completely DELETED and cannot be undone."
                            data-action="{{ route('admin.requests.delete',['id'=>$request->id,'event_id'=>$event_id]) }}"
                            @endif
                            class="flex items-center group disabled:hover:bg-inherit disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400  focus:shadow-outline-gray"
                            aria-label="Reject"
                          >
                            <i class="las la-trash-alt text-xl group-disabled:text-gray-500 text-red-500"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
            <div class="mt-4">
                @if($requests instanceof \Illuminate\Pagination\LengthAwarePaginator )

                {{$requests->links('pagination::tailwind') ?? ''}}
                @endif
            </div>
          </div>
        </main>

@endsection
