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
                          if(!isset($similar[$ticket->ticket_type->name])){
                            $similar[$ticket->ticket_type->name] = 1;
                            $similar_person[$ticket->ticket_type->name] = $ticket->ticket_type->person;
                          }else{
                            $similar[$ticket->ticket_type->name]++;
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
                      <td >
                        {{ date('Y/m/d h:i A',strtotime($request->created_at)) }}
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>