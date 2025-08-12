<template>
  <aside :class="[
    'fixed left-0 top-0 h-screen bg-white border-r border-gray-200 transition-all duration-300 ease-in-out z-40',
    isOpen ? 'w-64' : 'w-16'
  ]">
    <div class="flex flex-col h-full">
      <!-- Logo Section with Toggle Button -->
      <div class="px-4 py-4 border-b border-gray-200 h-16 flex items-center">
        <div v-if="isOpen" class="flex items-center justify-between w-full">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">S</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">SIGAP</h1>
          </div>
          <button 
            @click="toggleSidebar"
            class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors flex items-center justify-center"
          >
            <i class="pi pi-angle-left text-gray-500 text-sm"></i>
          </button>
        </div>
        <div v-else class="flex justify-center w-full">
          <button 
            @click="toggleSidebar"
            class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition-colors flex items-center justify-center"
          >
            <i class="pi pi-angle-right text-gray-500 text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto">
        <div class="p-2 space-y-1">
          <!-- Dashboard (Always visible) -->
          <NavItem 
            href="/dashboard"
            :active="$page.url === '/dashboard'"
            :collapsed="!isOpen"
            icon="pi pi-home"
            label="Dashboard"
          />

          <!-- Menu Groups -->
          <template v-for="group in menuGroups" :key="group.name">
            <div v-if="group.items.some(item => item.show)" class="py-2">
              <!-- Group Title -->
              <div v-if="isOpen" class="px-3 py-2">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                  {{ group.name }}
                </h3>
              </div>
              <div v-else class="px-2">
                <div class="h-px bg-gray-200"></div>
              </div>

              <!-- Group Items -->
              <div class="space-y-1">
                <NavItem 
                  v-for="item in group.items"
                  v-show="item.show"
                  :key="item.href"
                  :href="item.href"
                  :active="item.active"
                  :collapsed="!isOpen"
                  :icon="item.icon"
                  :label="item.label"
                />
              </div>
            </div>
          </template>
        </div>
      </nav>

      <!-- User Info (Bottom) -->
      <div class="p-3 border-t border-gray-200">
        <div v-if="isOpen" class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
            <span class="text-white font-medium text-sm">
              {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ user?.name }}</p>
            <p class="text-xs text-gray-500">{{ user?.role?.display_name || user?.role?.name || 'No Role' }}</p>
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
import { usePermissions } from '@/composables/usePermissions'
import NavItem from './NavItem.vue'

defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['toggle'])

const page = usePage()
const user = computed(() => page.props.auth?.user)

// Use permission system
const { 
  canView, 
  hasPermission, 
  hasAnyPermission, 
  isSuperAdmin, 
  isAdmin 
} = usePermissions()

// Permission checks for menu items - using exact permission names
const canAccessEmployees = computed(() => hasPermission('view_employees') || isSuperAdmin.value)
const canAccessBrands = computed(() => hasPermission('view_brands') || isSuperAdmin.value)
const canAccessLocations = computed(() => hasPermission('view_locations') || isSuperAdmin.value)
const canAccessShifts = computed(() => hasPermission('view_shifts') || isSuperAdmin.value)
const canAccessPresences = computed(() => hasPermission('view_presences') || isSuperAdmin.value)
const canAccessQuality = computed(() => hasPermission('view_quality') || isSuperAdmin.value)
const canAccessPacks = computed(() => hasPermission('view_packs') || isSuperAdmin.value)
const canAccessContracts = computed(() => hasPermission('view_contracts') || isSuperAdmin.value)
const canAccessUsers = computed(() => hasPermission('view_users') || isSuperAdmin.value)
const canAccessRoles = computed(() => hasPermission('view_users') || isSuperAdmin.value) // Role management requires user permissions
const canAccessReports = computed(() => hasPermission('view_reports') || isSuperAdmin.value)
const canAccessSettings = computed(() => 
  hasAnyPermission(['system_settings', 'audit_logs', 'backup_restore']) || isSuperAdmin.value
)

// Menu Groups Configuration
const menuGroups = computed(() => [
  {
    name: 'Operations',
    items: [
      {
        href: '/employees',
        active: page.url.startsWith('/employees'),
        icon: 'pi pi-users',
        label: 'Employees',
        show: canAccessEmployees.value
      },
      {
        href: '/shifts',
        active: page.url.startsWith('/shifts'),
        icon: 'pi pi-clock',
        label: 'Shift Management',
        show: canAccessShifts.value
      },
      {
        href: '/presences',
        active: page.url.startsWith('/presences'),
        icon: 'pi pi-check-circle',
        label: 'Presences',
        show: canAccessPresences.value
      }
    ]
  },
  {
    name: 'Management',
    items: [
      {
        href: '/brands',
        active: page.url.startsWith('/brands'),
        icon: 'pi pi-tag',
        label: 'Brands',
        show: canAccessBrands.value
      },
      {
        href: '/locations',
        active: page.url.startsWith('/locations'),
        icon: 'pi pi-map-marker',
        label: 'Locations',
        show: canAccessLocations.value
      },
      {
        href: '/packs',
        active: page.url.startsWith('/packs'),
        icon: 'pi pi-box',
        label: 'Packs',
        show: canAccessPacks.value
      },
      {
        href: '/contracts',
        active: page.url.startsWith('/contracts'),
        icon: 'pi pi-file-text',
        label: 'Contracts',
        show: canAccessContracts.value
      }
    ]
  },
  {
    name: 'Quality & Reports',
    items: [
      {
        href: '/quality',
        active: page.url.startsWith('/quality'),
        icon: 'pi pi-shield',
        label: 'Quality',
        show: canAccessQuality.value
      },
      {
        href: '/reports',
        active: page.url.startsWith('/reports'),
        icon: 'pi pi-chart-bar',
        label: 'Reports',
        show: canAccessReports.value
      }
    ]
  },
  {
    name: 'Administration',
    items: [
      {
        href: '/users',
        active: page.url.startsWith('/users'),
        icon: 'pi pi-user',
        label: 'Users',
        show: canAccessUsers.value
      },
      {
        href: '/roles',
        active: page.url.startsWith('/roles'),
        icon: 'pi pi-key',
        label: 'Roles & Permissions',
        show: canAccessUsers.value
      },
      {
        href: '/settings',
        active: page.url.startsWith('/settings'),
        icon: 'pi pi-cog',
        label: 'Settings',
        show: canAccessSettings.value
      }
    ]
  }
])

const toggleSidebar = () => {
  emit('toggle')
}
</script>
