<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Button 
            @click="router.get(route('roles.index'))" 
            outlined
            size="small"
          >
            <i class="pi pi-arrow-left mr-2"></i>
            Back to Roles
          </Button>
          <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
              <Badge
                :value="role.level"
                :severity="getLevelSeverity(role.level)"
                class="mr-3"
              />
              {{ role.display_name }}
            </h1>
            <p class="text-sm text-gray-600 mt-1">{{ role.description || 'No description' }}</p>
          </div>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canEdit('users')"
            @click="editRole" 
            outlined
          >
            <i class="pi pi-pencil mr-2"></i>
            Edit Role
          </Button>
          <Badge
            :value="role.is_active ? 'Active' : 'Inactive'"
            :severity="role.is_active ? 'success' : 'danger'"
            class="text-base px-3 py-1"
          />
        </div>
      </div>
    </div>

    <!-- Role Information Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Role Details -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Details</h3>
        <div class="space-y-3">
          <div>
            <span class="text-sm font-medium text-gray-500">Role Name:</span>
            <span class="ml-2 text-sm text-gray-900">{{ role.name }}</span>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Display Name:</span>
            <span class="ml-2 text-sm text-gray-900">{{ role.display_name }}</span>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Level:</span>
            <span class="ml-2 text-sm text-gray-900">{{ role.level }}</span>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-500">Status:</span>
            <Badge
              :value="role.is_active ? 'Active' : 'Inactive'"
              :severity="role.is_active ? 'success' : 'danger'"
              class="ml-2"
            />
          </div>
        </div>
      </div>

      <!-- Statistics -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="pi pi-key text-purple-500 mr-2"></i>
              <span class="text-sm text-gray-600">Permissions</span>
            </div>
            <span class="text-lg font-semibold text-gray-900">{{ role.permissions?.length || 0 }}</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="pi pi-users text-blue-500 mr-2"></i>
              <span class="text-sm text-gray-600">Assigned Users</span>
            </div>
            <span class="text-lg font-semibold text-gray-900">{{ users.total }}</span>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-2">
          <Button 
            v-if="canCreate('users')"
            @click="duplicateRole" 
            outlined 
            class="w-full justify-start"
          >
            <i class="pi pi-copy mr-2"></i>
            Duplicate Role
          </Button>
          <Button 
            v-if="canEdit('users')"
            @click="assignUserModal = true" 
            outlined 
            class="w-full justify-start"
          >
            <i class="pi pi-user-plus mr-2"></i>
            Assign User
          </Button>
          <Button 
            v-if="canDelete('users') && users.total === 0"
            @click="deleteRole" 
            outlined 
            severity="danger"
            class="w-full justify-start"
          >
            <i class="pi pi-trash mr-2"></i>
            Delete Role
          </Button>
        </div>
      </div>
    </div>

    <!-- Permissions Section -->
    <div class="bg-white rounded-lg border border-gray-200 mb-6">
      <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Permissions</h3>
        <p class="text-sm text-gray-600 mt-1">Permissions assigned to this role</p>
      </div>
      
      <div class="p-6">
        <div v-if="role.permissions?.length" class="space-y-6">
          <div v-for="(permissions, group) in groupedPermissions" :key="group">
            <h4 class="text-md font-medium text-gray-900 mb-3 capitalize">
              {{ group.replace('_', ' ') }}
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
              <div
                v-for="permission in permissions"
                :key="permission.id"
                class="flex items-center p-3 bg-gray-50 rounded-lg"
              >
                <i class="pi pi-key text-purple-500 mr-2 flex-shrink-0"></i>
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ permission.display_name }}</div>
                  <div class="text-xs text-gray-500">{{ permission.name }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8">
          <i class="pi pi-key text-gray-400 text-6xl mb-4 block"></i>
          <p class="text-gray-500">No permissions assigned to this role</p>
        </div>
      </div>
    </div>

    <!-- Users Section -->
    <div class="bg-white rounded-lg border border-gray-200">
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Users with this Role</h3>
            <p class="text-sm text-gray-600 mt-1">Users currently assigned to this role</p>
          </div>
          <Button 
            v-if="canEdit('users')"
            @click="assignUserModal = true"
            size="small"
          >
            <i class="pi pi-user-plus mr-2"></i>
            Assign User
          </Button>
        </div>
      </div>

      <!-- Users Filter -->
      <div class="p-4 border-b border-gray-200">
        <div class="flex gap-4">
          <InputText
            v-model="userFilters.search"
            placeholder="Search users..."
            class="flex-1"
            @input="debouncedUserSearch"
          />
          <Dropdown
            v-model="userFilters.is_active"
            :options="statusOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Status"
            class="w-32"
            @change="applyUserFilters"
          />
        </div>
      </div>
      
      <!-- Users Table -->
      <DataTable
        :value="users.data"
        :loading="loadingUsers"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="name" header="User">
          <template #body="{ data }">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center mr-3">
                <span class="text-white font-medium text-sm">
                  {{ data.name?.charAt(0).toUpperCase() }}
                </span>
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ data.name }}</div>
                <div class="text-sm text-gray-500">{{ data.email }}</div>
              </div>
            </div>
          </template>
        </Column>
        
        <Column field="is_active" header="Status">
          <template #body="{ data }">
            <Badge
              :value="data.is_active ? 'Active' : 'Inactive'"
              :severity="data.is_active ? 'success' : 'danger'"
            />
          </template>
        </Column>
        
        <Column header="Actions" class="w-32">
          <template #body="{ data }">
            <Button
              v-if="canEdit('users')"
              @click="removeUserFromRole(data)"
              size="small"
              outlined
              severity="danger"
              title="Remove from Role"
              icon="pi pi-user-minus"
            />
          </template>
        </Column>
      </DataTable>
      
      <!-- Users Pagination -->
      <div v-if="users.total > 0" class="p-4 border-t border-gray-200">
        <Paginator
          :rows="users.per_page"
          :totalRecords="users.total"
          :first="(users.current_page - 1) * users.per_page"
          @page="onUserPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
        />
      </div>
      
      <div v-else class="p-8 text-center">
        <i class="pi pi-users text-gray-400 text-6xl mb-4 block"></i>
        <p class="text-gray-500">No users assigned to this role</p>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions'
import { debounce } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import Paginator from 'primevue/paginator'

// Props
const props = defineProps({
  role: Object,
  users: Object,
  permissionGroups: Object,
  filters: Object
})

// Permissions
const { canView, canCreate, canEdit, canDelete } = usePermissions()

// Reactive data
const loadingUsers = ref(false)
const assignUserModal = ref(false)

// User filters
const userFilters = reactive({
  search: props.filters?.search || '',
  is_active: props.filters?.is_active ?? null,
  sort_by: props.filters?.sort_by || 'name',
  sort_order: props.filters?.sort_order || 'asc',
  per_page: props.filters?.per_page || 10
})

// Options
const statusOptions = [
  { label: 'All Status', value: null },
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

// Computed
const groupedPermissions = computed(() => {
  if (!props.role.permissions) return {}
  
  return props.role.permissions.reduce((groups, permission) => {
    const group = permission.group || 'other'
    if (!groups[group]) {
      groups[group] = []
    }
    groups[group].push(permission)
    return groups
  }, {})
})

// Methods
const getLevelSeverity = (level) => {
  if (level >= 80) return 'danger'
  if (level >= 60) return 'warning'
  if (level >= 40) return 'info'
  return 'success'
}

const editRole = () => {
  router.get(route('roles.index'), {}, {
    onSuccess: () => {
      // This would typically open an edit modal or navigate to edit page
      // For now, just go back to index where edit functionality exists
    }
  })
}

const duplicateRole = () => {
  const newName = prompt('Enter new role name:', `${props.role.name}-copy`)
  const newDisplayName = prompt('Enter new display name:', `${props.role.display_name} Copy`)
  
  if (newName && newDisplayName) {
    router.post(route('roles.duplicate', props.role.id), {
      name: newName,
      display_name: newDisplayName
    }, {
      onSuccess: () => {
        router.get(route('roles.index'))
      }
    })
  }
}

const deleteRole = () => {
  if (confirm(`Are you sure you want to delete the role "${props.role.display_name}"?`)) {
    router.delete(route('roles.destroy', props.role.id), {
      onSuccess: () => {
        router.get(route('roles.index'))
      }
    })
  }
}

const removeUserFromRole = (user) => {
  if (confirm(`Remove ${user.name} from this role?`)) {
    router.post(route('roles.remove-user', props.role.id), {
      user_id: user.id
    }, {
      preserveState: true
    })
  }
}

const applyUserFilters = () => {
  router.get(route('roles.show', props.role.id), userFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const debouncedUserSearch = debounce(() => {
  applyUserFilters()
}, 300)

const onUserPageChange = (event) => {
  userFilters.per_page = event.rows
  router.get(route('roles.show', props.role.id), {
    ...userFilters,
    page: event.page + 1
  }, {
    preserveState: true,
    preserveScroll: true
  })
}
</script>
