<div class="py-4 text-gray-500 dark:text-gray-400">
          <a
            class="ml-6 text-lg flex items-center font-bold text-gray-800 dark:text-gray-200"
            href="#"
          >
          <div class="w-8 h-8 inline-block mr-4 group">
            <img src="{{ asset('logo.png') }}" class="w-full h-full object-contain group-hover:hidden" alt="">
            <img src="{{ asset('logo2.png') }}" class="hidden w-full h-full object-contain group-hover:block" alt="">
          </div>
            Egycon Tickets
          </a>
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
                href="{{ route('admin.home') }}"
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
          
          <ul>
            <li class="relative px-6 py-3">
              @if($page == 'requests')
              <span
                class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg"
                aria-hidden="true"
              ></span>
              @endif
              <a
                class="inline-flex items-center w-full text-sm font-semibold text-gray-500 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 dark:text-gray-400"
                href="{{ route('admin.requests') }}"
              >
                <i class="las la-receipt text-xl"></i>
                <span class="ml-4">Requests</span>
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
                href="{{ route('admin.tickets.view') }}"
              >
                <i class="las la-receipt text-xl"></i>
                <span class="ml-4">Tickets</span>
              </a>
            </li>

            <li class="relative px-6 py-3 hidden">
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
                    <a class="w-full" href="#">Add User</a>
                  </li>
                  <li
                    class="px-2 py-1 transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200"
                  >
                    <a class="w-full" href="#">
                      View Users
                    </a>
                  </li>
                </ul>
              </template>
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