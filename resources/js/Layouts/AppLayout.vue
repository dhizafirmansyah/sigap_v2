<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sidebar Navigation -->
    <SideBar :is-open="sidebarOpen" @toggle="toggleSidebar" />
    
    <!-- Topbar -->
    <TopBar :user="user" :sidebar-open="sidebarOpen" @logout="handleLogout" />
    
    <!-- Main Content -->
    <div :class="[
      'pt-16 transition-all duration-300 ease-in-out',
      sidebarOpen ? 'ml-64' : 'ml-16'
    ]">
      <!-- Content Header -->
      <div class="bg-gray-50 px-6 py-6">
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
      <main class="p-6 bg-gray-50 min-h-[calc(100vh-10rem)]">
        <slot />
      </main>
    </div>
    
    <!-- Toast Messages -->
    <PrimeToast />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import TopBar from '@/Components/TopBar.vue'
import SideBar from '@/Components/SideBar.vue'
import PrimeToast from 'primevue/toast'

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
