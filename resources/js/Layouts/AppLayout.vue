<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Topbar -->
    <TopBar :user="user" @logout="handleLogout" />
    
    <!-- Sidebar Navigation -->
    <SideBar :is-open="sidebarOpen" @toggle="toggleSidebar" />
    
    <!-- Main Content -->
    <div :class="[
      'transition-all duration-300 ease-in-out',
      sidebarOpen ? 'ml-64' : 'ml-16'
    ]">
      <!-- Content Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 v-if="title" class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
            <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
          </div>
          <div v-if="$slots.headerActions" class="flex items-center space-x-3">
            <slot name="headerActions" />
          </div>
        </div>
      </div>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import TopBar from '@/Components/TopBar.vue'
import SideBar from '@/Components/SideBar.vue'

defineProps({
  title: String,
  subtitle: String,
  user: Object
})

const sidebarOpen = ref(true)

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const handleLogout = () => {
  router.post('/logout')
}
</script>
