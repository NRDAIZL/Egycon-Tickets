<table >
                  <thead>
                    <tr
                    >
                      <th>Order ID</th>
                      <th>Registrant</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Ticket Type</th>
                      <th>Status</th>
                      <th>Promo Code</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody
                  >
                    @foreach ($requests as $request)

                    <tr >
                      <td>
                        <div >
                          <div>
                            <p >{!! $request->id !!}</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div >
                          <div>
                            <p >{!! $request->name !!}</p>
                          </div>
                        </div>
                      </td>
                      <td >
                        <div >
                          <div>
                            <p >{!! $request->email !!}</p>
                          </div>
                        </div>
                      </td>
                      <td >
                        <div >
                          <div>
                            <p >{!! $request->phone_number !!}</p>
                          </div>
                        </div>
                      </td>
                      <td >
                        @php
                          $similar = [];
                          $similar_person = [];
                        foreach ($request->ticket as $ticket){
                            $ticket_name = "";
                            $ticket_name = $ticket->ticket_type->name;
                            if($ticket->sub_ticket_type != null)
                            {
                                $ticket_name += " (" + $ticket->sub_ticket_type->name + ")";
                            }
                          if(!isset($similar[$ticket_name])){
                            $similar[$ticket_name] = 1;
                            $similar_person[$ticket_name] = $ticket->ticket_type->person;
                          }else{
                            $similar[$ticket_name]++;
                          }
                        }
                        @endphp
                        @foreach ($similar as $key=>$value)
                          {{ $value/$similar_person[$key] }} {{ $key }} <br>
                        @endforeach
                      </td>
                      <td >
                        @if($request->status === null)
                        <span
                          class="text-yellow-600"
                        >
                          Pending
                        </span>
                        @elseif($request->status == 1)
                        <span
                          class="text-green-600"
                        >
                          Approved


                        </span>
                        @if($request->provider)
                        <span
                          class=""
                        >
                          -{{ $request->provider->name }}
                        </span>
                        @endif
                        @else
                        <span
                          class="text-red-600 "
                        >
                          Declined
                        </span>
                        @endif
                      </td>
                      <td>
                          {{ $request->promo_code->code ?? "N/A" }}
                      </td>
                      <td >
                        {{ date('Y/m/d h:i A',strtotime($request->created_at)) }}
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>
