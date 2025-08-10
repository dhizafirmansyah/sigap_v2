<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Role Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage roles and permissions for your system</p>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canCreate('users')"
            @click="showCreateModal = true" 
            class="bg-primary-600 hover:bg-primary-700"
          >
            <i class="pi pi-plus mr-2"></i>
            Create Role
          </Button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-shield text-2xl text-blue-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Roles</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_roles }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Roles</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.active_roles }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-key text-2xl text-purple-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Permissions</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_permissions }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-users text-2xl text-indigo-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Users with Roles</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.users_with_roles }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
      <div class="flex flex-col lg:flex-row gap-4">
        <div class="flex-1">
          <InputText
            v-model="localFilters.search"
            placeholder="Search roles by name, display name, or description..."
            class="w-full"
            @input="debouncedSearch"
          />
        </div>
        <div class="flex gap-3">
          <Dropdown
            v-model="localFilters.is_active"
            :options="statusOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Status"
            class="w-32"
            @change="applyFilters"
          />
          <InputNumber
            v-model="localFilters.level_min"
            placeholder="Min Level"
            class="w-24"
            :min="1"
            :max="100"
            @input="applyFilters"
          />
          <InputNumber
            v-model="localFilters.level_max"
            placeholder="Max Level"
            class="w-24"
            :min="1"
            :max="100"
            @input="applyFilters"
          />
          <Button @click="clearFilters" outlined>
            <i class="pi pi-times mr-2"></i>
            Clear
          </Button>
        </div>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <DataTable
        :value="roles.data"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="name" header="Role Name" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <Badge
                :value="data.level"
                :severity="getLevelSeverity(data.level)"
                class="mr-2"
              />
              <div>
                <div class="font-medium text-gray-900">{{ data.display_name }}</div>
                <div class="text-sm text-gray-500">{{ data.name }}</div>
              </div>
            </div>
          </template>
        </Column>
        
        <Column field="description" header="Description">
          <template #body="{ data }">
            <span class="text-sm text-gray-600">
              {{ data.description || '-' }}
            </span>
          </template>
        </Column>
        
        <Column field="permissions_count" header="Permissions" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <i class="pi pi-key text-purple-500 mr-1"></i>
              <span class="font-medium">{{ data.permissions_count }}</span>
            </div>
          </template>
        </Column>
        
        <Column field="users_count" header="Users" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <i class="pi pi-users text-blue-500 mr-1"></i>
              <span class="font-medium">{{ data.users_count }}</span>
            </div>
          </template>
        </Column>
        
        <Column field="is_active" header="Status" sortable>
          <template #body="{ data }">
            <Badge
              :value="data.is_active ? 'Active' : 'Inactive'"
              :severity="data.is_active ? 'success' : 'danger'"
            />
          </template>
        </Column>
        
        <Column header="Actions" class="w-40">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button
                @click="viewRole(data)"
                size="small"
                outlined
                title="View Details"
              >
                <i class="pi pi-eye"></i>
              </Button>
              <Button
                v-if="canEdit('users')"
                @click="editRole(data)"
                size="small"
                outlined
                severity="warning"
                title="Edit Role"
              >
                <i class="pi pi-pencil"></i>
              </Button>
              <Button
                v-if="canCreate('users')"
                @click="duplicateRole(data)"
                size="small"
                outlined
                severity="info"
                title="Duplicate Role"
              >
                <i class="pi pi-copy"></i>
              </Button>
              <Button
                v-if="canDelete('users') && data.users_count === 0"
                @click="deleteRole(data)"
                size="small"
                outlined
                severity="danger"
                title="Delete Role"
              >
                <i class="pi pi-trash"></i>
              </Button>
            </div>
          </template>
        </Column>
      </DataTable>
      
      <!-- Pagination -->
      <div class="p-4 border-t border-gray-200">
        <Paginator
          :rows="roles.per_page"
          :totalRecords="roles.total"
          :first="(roles.current_page - 1) * roles.per_page"
          @page="onPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          :rowsPerPageOptions="[10, 25, 50]"
        />
      </div>
    </div>

    <!-- Create/Edit Role Modal -->
    <Dialog
      v-model:visible="showCreateModal"
      modal
      :header="editingRole ? 'Edit Role' : 'Create Role'"
      class="w-full max-w-4xl"
    >
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Role Information -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Role Information</h3>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role Name</label>
            <InputText
              v-model="roleForm.name"
              placeholder="e.g., manager"
              class="w-full"
              :class="{ 'p-invalid': roleFormErrors.name }"
            />
            <small v-if="roleFormErrors.name" class="p-error">{{ roleFormErrors.name }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
            <InputText
              v-model="roleForm.display_name"
              placeholder="e.g., Manager"
              class="w-full"
              :class="{ 'p-invalid': roleFormErrors.display_name }"
            />
            <small v-if="roleFormErrors.display_name" class="p-error">{{ roleFormErrors.display_name }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <Textarea
              v-model="roleForm.description"
              placeholder="Role description..."
              class="w-full"
              rows="3"
            />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
              <InputNumber
                v-model="roleForm.level"
                class="w-full"
                :min="1"
                :max="100"
                :class="{ 'p-invalid': roleFormErrors.level }"
              />
              <small v-if="roleFormErrors.level" class="p-error">{{ roleFormErrors.level }}</small>
              <small class="text-gray-500">Higher level = more authority</small>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <div class="flex items-center pt-2">
                <Checkbox
                  v-model="roleForm.is_active"
                  inputId="is_active"
                  :binary="true"
                />
                <label for="is_active" class="ml-2">Active</label>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Permissions -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Permissions</h3>
          
          <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
            <div v-for="(permissions, group) in permissionGroups" :key="group" class="mb-4">
              <div class="flex items-center mb-2">
                <Checkbox
                  :modelValue="isGroupSelected(group)"
                  @change="toggleGroup(group, $event)"
                  :inputId="`group-${group}`"
                />
                <label :for="`group-${group}`" class="ml-2 font-medium text-gray-900 capitalize">
                  {{ group.replace('_', ' ') }}
                </label>
              </div>
              
              <div class="ml-6 space-y-1">
                <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
                  <Checkbox
                    v-model="roleForm.permissions"
                    :value="permission.id"
                    :inputId="`permission-${permission.id}`"
                  />
                  <label :for="`permission-${permission.id}`" class="ml-2 text-sm">
                    {{ permission.display_name }}
                    <span class="text-gray-500">({{ permission.name }})</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <Button @click="showCreateModal = false" outlined>Cancel</Button>
        <Button @click="saveRole" :loading="saving">
          {{ editingRole ? 'Update' : 'Create' }} Role
        </Button>
      </template>
    </Dialog>

    <!-- Duplicate Role Modal -->
    <Dialog
      v-model:visible="showDuplicateModal"
      modal
      header="Duplicate Role"
      class="w-full max-w-md"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">New Role Name</label>
          <InputText
            v-model="duplicateForm.name"
            placeholder="e.g., manager-copy"
            class="w-full"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">New Display Name</label>
          <InputText
            v-model="duplicateForm.display_name"
            placeholder="e.g., Manager Copy"
            class="w-full"
          />
        </div>
      </div>
      
      <template #footer>
        <Button @click="showDuplicateModal = false" outlined>Cancel</Button>
        <Button @click="saveDuplicate" :loading="saving">Duplicate</Button>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions'
import { debounce } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'
import Paginator from 'primevue/paginator'

// Props
const props = defineProps({
  roles: Object,
  statistics: Object,
  permissionGroups: Object,
  filters: Object,
  user: Object
})

// Permissions
const { canView, canCreate, canEdit, canDelete } = usePermissions()

// Reactive data
const loading = ref(false)
const saving = ref(false)
const showCreateModal = ref(false)
const showDuplicateModal = ref(false)
const editingRole = ref(null)
const roleToDelete = ref(null)
const roleToDuplicate = ref(null)

// Filters
const localFilters = reactive({
  search: props.filters?.search || '',
  is_active: props.filters?.is_active ?? null,
  level_min: props.filters?.level_min || null,
  level_max: props.filters?.level_max || null,
  sort_by: props.filters?.sort_by || 'level',
  sort_order: props.filters?.sort_order || 'desc',
  per_page: props.filters?.per_page || 10
})

// Forms
const roleForm = reactive({
  name: '',
  display_name: '',
  description: '',
  level: 50,
  is_active: true,
  permissions: []
})

const duplicateForm = reactive({
  name: '',
  display_name: ''
})

const roleFormErrors = ref({})

// Options
const statusOptions = [
  { label: 'All Status', value: null },
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

// Methods
const applyFilters = () => {
  router.get(route('roles.index'), localFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const clearFilters = () => {
  Object.assign(localFilters, {
    search: '',
    is_active: null,
    level_min: null,
    level_max: null
  })
  applyFilters()
}

const onPageChange = (event) => {
  localFilters.per_page = event.rows
  router.get(route('roles.index'), {
    ...localFilters,
    page: event.page + 1
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const getLevelSeverity = (level) => {
  if (level >= 80) return 'danger'
  if (level >= 60) return 'warning'
  if (level >= 40) return 'info'
  return 'success'
}

const resetRoleForm = () => {
  Object.assign(roleForm, {
    name: '',
    display_name: '',
    description: '',
    level: 50,
    is_active: true,
    permissions: []
  })
  roleFormErrors.value = {}
  editingRole.value = null
}

const viewRole = (role) => {
  router.get(route('roles.show', role.id))
}

const editRole = (role) => {
  editingRole.value = role
  Object.assign(roleForm, {
    name: role.name,
    display_name: role.display_name,
    description: role.description || '',
    level: role.level,
    is_active: role.is_active,
    permissions: role.permissions?.map(p => p.id) || []
  })
  showCreateModal.value = true
}

const duplicateRole = (role) => {
  roleToDuplicate.value = role
  duplicateForm.name = `${role.name}-copy`
  duplicateForm.display_name = `${role.display_name} Copy`
  showDuplicateModal.value = true
}

const deleteRole = async (role) => {
  if (confirm(`Are you sure you want to delete the role "${role.display_name}"?`)) {
    try {
      await router.delete(route('roles.destroy', role.id))
      // Refresh the page after successful deletion
      router.reload()
    } catch (error) {
      console.error('Failed to delete role:', error)
    }
  }
}

const saveRole = async () => {
  saving.value = true
  roleFormErrors.value = {}
  
  try {
    const url = editingRole.value 
      ? route('roles.update', editingRole.value.id)
      : route('roles.store')
      
    const method = editingRole.value ? 'put' : 'post'
    
    await router[method](url, roleForm, {
      onSuccess: () => {
        showCreateModal.value = false
        resetRoleForm()
      },
      onError: (errors) => {
        roleFormErrors.value = errors
      }
    })
  } catch (error) {
    console.error('Failed to save role:', error)
  } finally {
    saving.value = false
  }
}

const saveDuplicate = async () => {
  saving.value = true
  
  try {
    await router.post(route('roles.duplicate', roleToDuplicate.value.id), duplicateForm, {
      onSuccess: () => {
        showDuplicateModal.value = false
        duplicateForm.name = ''
        duplicateForm.display_name = ''
        roleToDuplicate.value = null
      }
    })
  } catch (error) {
    console.error('Failed to duplicate role:', error)
  } finally {
    saving.value = false
  }
}

const isGroupSelected = (group) => {
  const groupPermissions = props.permissionGroups[group]
  return groupPermissions?.every(permission => 
    roleForm.permissions.includes(permission.id)
  )
}

const toggleGroup = (group, event) => {
  const groupPermissions = props.permissionGroups[group]
  
  if (event.checked) {
    // Add all group permissions
    groupPermissions.forEach(permission => {
      if (!roleForm.permissions.includes(permission.id)) {
        roleForm.permissions.push(permission.id)
      }
    })
  } else {
    // Remove all group permissions
    groupPermissions.forEach(permission => {
      const index = roleForm.permissions.indexOf(permission.id)
      if (index > -1) {
        roleForm.permissions.splice(index, 1)
      }
    })
  }
}

// Lifecycle
onMounted(() => {
  // Any initialization logic
})
</script>
