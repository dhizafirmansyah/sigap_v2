<template>
  <header class="bg-white border-b border-gray-200 fixed w-full top-0 z-50">
    <div class="px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Left Side -->
        <div class="flex items-center space-x-4">
          <!-- Logo -->
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">S</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">SIGAP</h1>
          </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="p-2 text-gray-400 hover:text-gray-600 relative">
            <i class="pi pi-bell text-lg"></i>
            <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              3
            </span>
          </button>

          <!-- User Menu -->
          <div class="relative">
            <button 
              @click="toggleUserMenu"
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors"
            >
              <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                <span class="text-white font-medium text-sm">
                  {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                </span>
              </div>
              <div class="text-left hidden md:block">
                <p class="text-sm font-medium text-gray-900">{{ user?.name || 'User' }}</p>
                <p class="text-xs text-gray-500">{{ user?.role?.display_name || user?.role?.name || 'No Role' }}</p>
              </div>
              <i class="pi pi-chevron-down text-gray-400"></i>
            </button>

            <!-- Dropdown Menu -->
            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div 
                v-show="userMenuOpen"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
              >
                <div class="px-4 py-3 border-b border-gray-100">
                  <p class="text-sm font-medium text-gray-900">{{ user?.name || 'User' }}</p>
                  <p class="text-sm text-gray-500">{{ user?.email || 'No Email' }}</p>
                  <p class="text-xs text-gray-400 mt-1">{{ user?.role?.display_name || user?.role?.name || 'No Role' }}</p>
                </div>
                
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <div class="flex items-center space-x-2">
                    <i class="pi pi-user text-gray-400"></i>
                    <span>Profile</span>
                  </div>
                </a>
                
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  <div class="flex items-center space-x-2">
                    <i class="pi pi-cog text-gray-400"></i>
                    <span>Settings</span>
                  </div>
                </a>
                
                <div class="border-t border-gray-100 my-1"></div>
                
                <button 
                  @click="handleLogout"
                  class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                >
                  <div class="flex items-center space-x-2">
                    <i class="pi pi-sign-out text-red-500"></i>
                    <span>Logout</span>
                  </div>
                </button>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

defineProps({
  user: Object
})

const emit = defineEmits(['logout'])

const userMenuOpen = ref(false)

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value
}

const handleLogout = () => {
  userMenuOpen.value = false
  emit('logout')
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    userMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
