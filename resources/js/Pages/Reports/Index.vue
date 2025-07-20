<template>
  <Head title="Reports" />
  
  <AppLayout 
    title="Reports" 
    subtitle="View and generate system reports"
    :user="$page.props.auth.user"
  >
    <template #headerActions>
      <button class="btn-primary flex items-center space-x-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Generate Report</span>
      </button>
    </template>

    <div class="card p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Reports Dashboard</h3>
      <p class="text-gray-600">This is a demo page for reports functionality.</p>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
</script>
