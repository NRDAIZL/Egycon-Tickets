@extends('layouts.app')
@section('page')
belongings
@endsection
@section('title')
{{ $belonging->name }}
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Belonging 
            </h2>
            
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto bg-white dark:bg-gray-800 px-4 py-8">
                <table class="w-full dark:text-white">
                  <tr>
                    <th class="w-24 text-right pr-4">Name: </th>
                    <td>{{$belonging->name}}</td>
                  </tr>
                  <tr>
                    <th class="w-24 text-right pr-4">Email: </th>
                    <td>{{$belonging->email}}</td>
                  </tr>
                  <tr>
                    <th class="w-24 text-right pr-4">Phone: </th>
                    <td>{{$belonging->phone}}</td>
                  </tr>
                  <tr>
                    <th class="w-24 text-right pr-4">Code: </th>
                    <td class="font-bold">{{$belonging->code}}</td>
                  </tr>
                  <tr>
                    <th class="w-24 text-right pr-4">Slot: </th>
                    <td class="font-bold">{{$belonging->slot->name}}</td>
                  </tr>
                  <tr>
                    <th class="w-24 text-right pr-4">Color: </th>
                    <td class="font-bold" style="color:{{$belonging->color}}">{{$belonging->color_name}}</td>
                  </tr>
                  <tr>
                    <th class="w-24 text-right pr-4">Status: </th>
                    <td class="font-bold" >
                    @if($belonging->status == 1)
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full dark:bg-green-700 dark:text-green-100"
                        >
                          INSIDE
                        </span> 
                        <a href="#"><span class="ml-2 text-purple-500 underline"> Change to outside</span></a>
                        @else
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-700 dark:text-yellow-100"
                        >
                          OUTSIDE
                        </span>
                        <a href="#"><span class="ml-2 text-purple-500 underline"> Change to inside</span></a>
                        @endif
                    </td>
                  </tr>
                </table>
              </div>
              
            </div>
          </div>
        </main>

@endsection