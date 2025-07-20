<template>
  <aside :class="[
    'fixed left-0 top-16 h-[calc(100vh-4rem)] bg-white border-r border-gray-200 transition-all duration-300 ease-in-out z-40',
    isOpen ? 'w-64' : 'w-16'
  ]">
    <div class="flex flex-col h-full">
      <!-- Toggle Button -->
      <div class="p-4 border-b border-gray-200">
        <button 
          @click="toggleSidebar"
          class="w-full flex items-center justify-center p-2 rounded-lg hover:bg-gray-100 transition-colors"
        >
          <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  :d="isOpen ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'"/>
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-2">
        <!-- Dashboard -->
        <NavItem 
          href="/dashboard"
          :active="$page.url === '/dashboard'"
          :collapsed="!isOpen"
          icon="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
          label="Dashboard"
        />

        <!-- Users Management (Admin & SuperAdmin only) -->
        <NavItem 
          v-if="canAccessUsers"
          href="/users"
          :active="$page.url.startsWith('/users')"
          :collapsed="!isOpen"
          icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"
          label="Users"
        />

        <!-- Reports -->
        <NavItem 
          href="/reports"
          :active="$page.url.startsWith('/reports')"
          :collapsed="!isOpen"
          icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          label="Reports"
        />

        <!-- Settings (Admin & SuperAdmin only) -->
        <NavItem 
          v-if="canAccessSettings"
          href="/settings"
          :active="$page.url.startsWith('/settings')"
          :collapsed="!isOpen"
          icon="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
          label="Settings"
        />
      </nav>

      <!-- User Info (Bottom) -->
      <div class="p-4 border-t border-gray-200">
        <div v-if="isOpen" class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
            <span class="text-white font-medium text-sm">
              {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ user?.role }}</p>
          </div>
        </div>
        <div v-else class="flex justify-center">
          <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
            <span class="text-white font-medium text-sm">
              {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import NavItem from './NavItem.vue'

defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['toggle'])

const page = usePage()
const user = computed(() => page.props.auth?.user)

// Permission checks based on user role
const canAccessUsers = computed(() => {
  const role = user.value?.role
  return role === 'superadmin' || role === 'admin'
})

const canAccessSettings = computed(() => {
  const role = user.value?.role
  return role === 'superadmin' || role === 'admin'
})

const toggleSidebar = () => {
  emit('toggle')
}
</script>
