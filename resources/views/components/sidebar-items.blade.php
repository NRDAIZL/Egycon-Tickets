<div class="py-4 text-gray-500 dark:text-gray-400">
          <a
            class="ml-6 text-lg flex flex-wrap items-center font-bold text-gray-800 dark:text-gray-200"
            href="#"
          >
          <div class="w-8 h-8 inline-block mr-4 group">
            @php
              $logo = 'logo.png';
              $dashboard_name = 'Egycon Tickets';
              if(isset($event_id)){
                $event = App\Models\Event::find($event_id);
                $dashboard_name = $event->name;
                if($event->logo)
                  $logo = Storage::url($event->logo);
              }
              
            @endphp
            <img src="{{ asset($logo) }}" class="w-full h-full object-contain group-hover:hidden" alt="">
            <img src="{{ asset('logo2.png') }}" class="hidden w-full h-full object-contain group-hover:block" alt="">
          </div>
            {{ $dashboard_name }}
          </a>
            @isset($event_id)
          <ul class="mt-6">
            <li class="relative px-6 py-3">
              @if($page == 'dashboard')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                href="{{ route('admin.home',$event_id) }}"
              >
                <svg
                  class="w-5 h-5"
                  aria-hidden="true"
                  fill="none"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                  ></path>
                </svg>
                <span class="ml-4">Dashboard</span>
              </a>
            </li>
          </ul>
          <ul class="">
            <li class="relative px-6 py-3">
             
              <a
                class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                href="{{ route('instructions',$event_id) }}"
              >
                <i class="las la-eye text-xl"></i>
                <span class="ml-4">Preview Payment Form</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
                @if($page == 'event-settings')
                <span
                    class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                    aria-hidden="true"
                ></span>
                @endif
              <button
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                @click="toggleEventSettingsMenu"
                aria-haspopup="true"
              >
                <span class="inline-flex items-center">
                  <i class="las la-cog text-2xl"></i>
                  <span class="ml-4">Event Settings</span>
                </span>
                <svg
                  class="w-4 h-4"
                  aria-hidden="true"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
              </button>
              <template x-if="isEventSettingsMenuOpen">
                <ul
                  x-transition:enter="transition-all ease-in-out duration-300"
                  x-transition:enter-start="opacity-25 max-h-0"
                  x-transition:enter-end="opacity-100 max-h-xl"
                  x-transition:leave="transition-all ease-in-out duration-300"
                  x-transition:leave-start="opacity-100 max-h-xl"
                  x-transition:leave-end="opacity-0 max-h-0"
                  class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                  aria-label="submenu"
                >
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.event_settings.templates',$event_id) }}">
                      Email Templates
                    </a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.event_settings.theme',$event_id) }}">
                      Edit Event Theme
                    </a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.event_settings.questions',$event_id) }}">
                      Form Additional Questions
                    </a>
                  </li>
                </ul>
              </template>
            </li>
          </ul>
          
          @else
          <h1 class="text-xl font-bold px-4 py-4">
            Please select/create an event to continue
          </h1>
          @endisset
          <ul>
            @isset($event_id)
            <li class="relative px-6 py-3">
              @if($page == 'requests')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                href="{{ route('admin.requests',$event_id) }}"
              >
                <i class="las la-receipt text-xl"></i>
                <span class="ml-4">Requests</span>
                @php
                  // get pending requests
                  $pending_requests = App\Models\Event::find($event_id)->posts()->where('status',null)->count();
                @endphp
                @if($pending_requests > 0)
                <span class="ml-auto text-sm font-medium text-white flex items-center justify-center w-7 h-7 rounded-full bg-red-500">
                  {{ $pending_requests>99 ? '99+' : $pending_requests }}
                </span>
                @endif
              </a>
            </li>
            <li class="relative px-6 py-3">
              @if($page == 'import')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                href="{{ route('admin.import',$event_id) }}"
              >
                <i class="las la-file-excel text-xl"></i>
                <span class="ml-4">Import</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              @if($page == 'tickets')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                href="{{ route('admin.tickets.view',$event_id) }}"
              >
                <i class="las la-receipt text-xl"></i>
                <span class="ml-4">Tickets</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              @if($page == 'register')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                href="{{ route('admin.register',$event_id) }}"
              >
                <i class="las la-plus text-xl"></i>
                <span class="ml-4">Onsite Registration</span>
              </a>
            </li>
            <li class="relative px-6 py-3">
              @if($page == 'reset-tickets')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                href="{{ route('admin.delete_all',$event_id) }}"
              >
                <i class="las la-trash-alt text-xl"></i>
                <span class="ml-4">Clear Tickets</span>
              </a>
            </li>
            <li class="relative px-6 py-3 hidden ">
                @if($page == 'codes')
                <span
                    class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                    aria-hidden="true"
                ></span>
                @endif
              <button
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                @click="toggleCodesMenu"
                aria-haspopup="true"
              >
                <span class="inline-flex items-center">
                  <i class="las la-receipt text-xl"></i>
                  <span class="ml-4">Codes</span>
                </span>
                <svg
                  class="w-4 h-4"
                  aria-hidden="true"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
              </button>
              <template x-if="isCodesMenuOpen">
                <ul
                  x-transition:enter="transition-all ease-in-out duration-300"
                  x-transition:enter-start="opacity-25 max-h-0"
                  x-transition:enter-end="opacity-100 max-h-xl"
                  x-transition:leave="transition-all ease-in-out duration-300"
                  x-transition:leave-start="opacity-100 max-h-xl"
                  x-transition:leave-end="opacity-0 max-h-0"
                  class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                  aria-label="submenu"
                >
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.codes.add',$event_id) }}">Add Code</a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.codes.view',$event_id) }}">
                      View Codes
                    </a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.codes.upload',$event_id) }}">
                      Upload Codes Sheet
                    </a>
                  </li>
                </ul>
              </template>
            </li>
            <li class="relative px-6 py-3">
                @if($page == 'qr_codes')
                <span
                    class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                    aria-hidden="true"
                ></span>
                @endif
              <button
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                @click="toggleGenerateMenu"
                aria-haspopup="true"
              >
                <span class="inline-flex items-center">
                  <i class="las la-qrcode text-2xl"></i>
                  <span class="ml-4">Generate Tickets</span>
                </span>
                <svg
                  class="w-4 h-4"
                  aria-hidden="true"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
              </button>
              <template x-if="isGenerateMenuOpen">
                <ul
                  x-transition:enter="transition-all ease-in-out duration-300"
                  x-transition:enter-start="opacity-25 max-h-0"
                  x-transition:enter-end="opacity-100 max-h-xl"
                  x-transition:leave="transition-all ease-in-out duration-300"
                  x-transition:leave-start="opacity-100 max-h-xl"
                  x-transition:leave-end="opacity-0 max-h-0"
                  class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                  aria-label="submenu"
                >
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.generate_qr_codes',$event_id) }}">
                      Generate QR Codes</a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.generate_qr_tickets',$event_id) }}">
                      Generate QR Tickets
                    </a>
                  </li>
                </ul>
              </template>
            </li>
            <li class="relative px-6 py-3">
                @if($page == 'users')
                <span
                    class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                    aria-hidden="true"
                ></span>
                @endif
              <button
                class="inline-flex items-center justify-between w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                @click="toggleUsersMenu"
                aria-haspopup="true"
              >
                <span class="inline-flex items-center">
                  <i class="las la-users text-2xl"></i>
                  <span class="ml-4">Users</span>
                </span>
                <svg
                  class="w-4 h-4"
                  aria-hidden="true"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
              </button>
              <template x-if="isUsersMenuOpen">
                <ul
                  x-transition:enter="transition-all ease-in-out duration-300"
                  x-transition:enter-start="opacity-25 max-h-0"
                  x-transition:enter-end="opacity-100 max-h-xl"
                  x-transition:leave="transition-all ease-in-out duration-300"
                  x-transition:leave-start="opacity-100 max-h-xl"
                  x-transition:leave-end="opacity-0 max-h-0"
                  class="p-2 mt-2 space-y-2 overflow-hidden text-sm font-medium text-gray-500 rounded-md shadow-inner bg-gray-50 dark:text-gray-400 dark:bg-gray-900"
                  aria-label="submenu"
                >
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.users.invite',$event_id) }}">Invite User</a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="{{ route('admin.users.view',$event_id) }}">
                      View Users
                    </a>
                  </li>
                </ul>
              </template>
            </li>
            @endisset
            
            <li class="relative px-6 py-3">
              @if($page == 'events')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                @isset($event_id)
                  href="{{ route('admin.event.events.view',$event_id) }}"
                @else
                  href="{{ route('admin.events.view') }}"
                @endisset
              >
                <i class="las la-campground text-xl"></i>
                <span class="ml-4">Events</span>
              </a>
            </li>
            
          </ul>
          
          <div class="px-6 my-6">
            <a
            href="{{ route('logout') }}"
              class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple"
            >
              Logout
              <i class="ml-2 las la-sign-out-alt text-xl"></i>
          </a>
          </div>
        </div>