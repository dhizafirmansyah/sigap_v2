<template>
  <Head title="Settings" />
  
  <AppLayout 
    title="Settings" 
    subtitle="Configure system preferences"
    :user="$page.props.auth.user"
  >
    <template #headerActions>
      <button class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>Save Changes</span>
      </button>
    </template>

    <div class="card p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Settings Dashboard</h3>
      <p class="text-gray-600">This is a demo page for settings functionality.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
</script>
