<template>
  <Link 
    :href="href"
    :class="[
      'flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors duration-200',
      active 
        ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-600' 
        : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900',
      collapsed ? 'justify-center' : ''
    ]"
  >
    <i 
      :class="[icon, 'flex-shrink-0']" 
      style="font-size: 1.125rem;"
    ></i>
    
    <span 
      v-if="!collapsed" 
      class="font-medium"
    >
      {{ label }}
    </span>
    
    <!-- Badge/Count (if provided) -->
    <span 
      v-if="badge && !collapsed" 
      class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full"
    >
      {{ badge }}
    </span>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  href: String,
  active: Boolean,
  collapsed: Boolean,
  icon: String,
  label: String,
  badge: [String, Number]
})
</script>
