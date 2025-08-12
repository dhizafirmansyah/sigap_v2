<template>
  <AppLayout 
    title="Users Management" 
    subtitle="Manage system users and their permissions"
  >
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage system users and their permissions</p>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canCreate('users')"
            @click="showCreateModal = true" 
            class="bg-primary-600 hover:bg-primary-700"
          >
            <i class="pi pi-plus mr-2"></i>
            Create User
          </Button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-users text-2xl text-blue-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_users }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.active_users }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-times-circle text-2xl text-red-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Inactive Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.inactive_users }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-shield text-2xl text-purple-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">With Roles</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.users_with_roles }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-user-minus text-2xl text-orange-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Without Roles</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.users_without_roles }}</p>
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
            placeholder="Search users by name or email..."
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
          <Dropdown
            v-model="localFilters.role_id"
            :options="roleOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Role"
            class="w-40"
            @change="applyFilters"
          />
          <Button @click="clearFilters" outlined>
            <i class="pi pi-times mr-2"></i>
            Clear
          </Button>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <DataTable
        :value="users.data"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="name" header="User" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center mr-3">
                <span class="text-white font-medium text-sm">
                  {{ data.name?.charAt(0).toUpperCase() || 'U' }}
                </span>
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ data.name }}</div>
                <div class="text-sm text-gray-500">{{ data.email }}</div>
              </div>
            </div>
          </template>
        </Column>
        
        <Column field="role" header="Role">
          <template #body="{ data }">
            <div v-if="data.role" class="flex items-center">
              <Badge
                :value="data.role.level"
                :severity="getLevelSeverity(data.role.level)"
                class="mr-2"
              />
              <div>
                <div class="font-medium text-gray-900">{{ data.role.display_name }}</div>
                <div class="text-sm text-gray-500">{{ data.role.name }}</div>
              </div>
            </div>
            <span v-else class="text-gray-400 italic">No role assigned</span>
          </template>
        </Column>
        
        <Column field="permissions_count" header="Permissions" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <i class="pi pi-key text-purple-500 mr-1"></i>
              <span class="font-medium">{{ data.permissions_count || 0 }}</span>
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
        
        <Column field="created_at" header="Created" sortable>
          <template #body="{ data }">
            <span class="text-sm text-gray-600">
              {{ formatDate(data.created_at) }}
            </span>
          </template>
        </Column>
        
        <Column header="Actions" class="w-48">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button
                @click="viewUser(data)"
                size="small"
                outlined
                title="View Details"
              >
                <i class="pi pi-eye"></i>
              </Button>
              <Button
                v-if="canEdit('users')"
                @click="editUser(data)"
                size="small"
                outlined
                severity="warning"
                title="Edit User"
              >
                <i class="pi pi-pencil"></i>
              </Button>
              <Button
                v-if="canEdit('users')"
                @click="toggleUserStatus(data)"
                size="small"
                outlined
                :severity="data.is_active ? 'danger' : 'success'"
                :title="data.is_active ? 'Deactivate' : 'Activate'"
              >
                <i :class="data.is_active ? 'pi pi-times' : 'pi pi-check'"></i>
              </Button>
              <Button
                v-if="canDelete('users') && data.id !== $page.props.auth.user.id"
                @click="deleteUser(data)"
                size="small"
                outlined
                severity="danger"
                title="Delete User"
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
          :rows="users.per_page"
          :totalRecords="users.total"
          :first="(users.current_page - 1) * users.per_page"
          @page="onPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          :rowsPerPageOptions="[10, 25, 50]"
        />
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <Dialog
      v-model:visible="showCreateModal"
      modal
      :header="editingUser ? 'Edit User' : 'Create User'"
      class="w-full max-w-4xl"
    >
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Information -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">User Information</h3>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
            <InputText
              v-model="userForm.name"
              placeholder="e.g., John Doe"
              class="w-full"
              :class="{ 'p-invalid': userFormErrors.name }"
            />
            <small v-if="userFormErrors.name" class="p-error">{{ userFormErrors.name }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <InputText
              v-model="userForm.email"
              placeholder="e.g., john@example.com"
              type="email"
              class="w-full"
              :class="{ 'p-invalid': userFormErrors.email }"
            />
            <small v-if="userFormErrors.email" class="p-error">{{ userFormErrors.email }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
            <Password
              v-model="userForm.password"
              :placeholder="editingUser ? 'Leave blank to keep current password' : 'Enter password'"
              class="w-full"
              :class="{ 'p-invalid': userFormErrors.password }"
              toggleMask
            />
            <small v-if="userFormErrors.password" class="p-error">{{ userFormErrors.password }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
            <Password
              v-model="userForm.password_confirmation"
              placeholder="Confirm password"
              class="w-full"
              toggleMask
            />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
              <Dropdown
                v-model="userForm.role_id"
                :options="roles"
                optionLabel="display_name"
                optionValue="id"
                placeholder="Select Role"
                class="w-full"
                :class="{ 'p-invalid': userFormErrors.role_id }"
              />
              <small v-if="userFormErrors.role_id" class="p-error">{{ userFormErrors.role_id }}</small>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <div class="flex items-center pt-2">
                <Checkbox
                  v-model="userForm.is_active"
                  inputId="is_active"
                  :binary="true"
                />
                <label for="is_active" class="ml-2">Active</label>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Additional Permissions -->
        <div class="space-y-4">
          <h3 class="text-lg font-medium text-gray-900">Additional Permissions</h3>
          <p class="text-sm text-gray-600">Grant additional permissions beyond the assigned role</p>
          
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
                    v-model="userForm.permissions"
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
        <Button @click="saveUser" :loading="saving">
          {{ editingUser ? 'Update' : 'Create' }} User
        </Button>
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
import Password from 'primevue/password'
import Dropdown from 'primevue/dropdown'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'
import Paginator from 'primevue/paginator'

// Props
const props = defineProps({
  users: Object,
  statistics: Object,
  roles: Array,
  permissionGroups: Object,
  filters: Object
})

// Permissions
const { canView, canCreate, canEdit, canDelete } = usePermissions()

// Reactive data
const loading = ref(false)
const saving = ref(false)
const showCreateModal = ref(false)
const editingUser = ref(null)

// Filters
const localFilters = reactive({
  search: props.filters?.search || '',
  is_active: props.filters?.is_active ?? null,
  role_id: props.filters?.role_id || null,
  sort_by: props.filters?.sort_by || 'name',
  sort_order: props.filters?.sort_order || 'asc',
  per_page: props.filters?.per_page || 10
})

// Forms
const userForm = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: null,
  is_active: true,
  permissions: []
})

const userFormErrors = ref({})

// Computed options
const statusOptions = [
  { label: 'All Status', value: null },
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

const roleOptions = computed(() => [
  { label: 'All Roles', value: null },
  ...props.roles.map(role => ({
    label: role.display_name,
    value: role.id
  }))
])

// Methods
const applyFilters = () => {
  router.get(route('users.index'), localFilters, {
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
    role_id: null
  })
  applyFilters()
}

const onPageChange = (event) => {
  localFilters.per_page = event.rows
  router.get(route('users.index'), {
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

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const resetUserForm = () => {
  Object.assign(userForm, {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role_id: null,
    is_active: true,
    permissions: []
  })
  userFormErrors.value = {}
  editingUser.value = null
}

const viewUser = (user) => {
  router.get(route('users.show', user.id))
}

const editUser = (user) => {
  editingUser.value = user
  Object.assign(userForm, {
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    role_id: user.role_id,
    is_active: user.is_active,
    permissions: user.permissions?.map(p => p.id) || []
  })
  showCreateModal.value = true
}

const deleteUser = async (user) => {
  if (confirm(`Are you sure you want to delete the user "${user.name}"?`)) {
    try {
      await router.delete(route('users.destroy', user.id))
      router.reload()
    } catch (error) {
      console.error('Failed to delete user:', error)
    }
  }
}

const toggleUserStatus = async (user) => {
  const action = user.is_active ? 'deactivate' : 'activate'
  if (confirm(`Are you sure you want to ${action} the user "${user.name}"?`)) {
    try {
      await router.patch(route('users.toggle-status', user.id))
      router.reload()
    } catch (error) {
      console.error('Failed to toggle user status:', error)
    }
  }
}

const saveUser = async () => {
  saving.value = true
  userFormErrors.value = {}
  
  try {
    const url = editingUser.value 
      ? route('users.update', editingUser.value.id)
      : route('users.store')
      
    const method = editingUser.value ? 'put' : 'post'
    
    await router[method](url, userForm, {
      onSuccess: () => {
        showCreateModal.value = false
        resetUserForm()
      },
      onError: (errors) => {
        userFormErrors.value = errors
      }
    })
  } catch (error) {
    console.error('Failed to save user:', error)
  } finally {
    saving.value = false
  }
}

const isGroupSelected = (group) => {
  const groupPermissions = props.permissionGroups[group]
  return groupPermissions?.every(permission => 
    userForm.permissions.includes(permission.id)
  )
}

const toggleGroup = (group, event) => {
  const groupPermissions = props.permissionGroups[group]
  
  if (event.checked) {
    // Add all group permissions
    groupPermissions.forEach(permission => {
      if (!userForm.permissions.includes(permission.id)) {
        userForm.permissions.push(permission.id)
      }
    })
  } else {
    // Remove all group permissions
    groupPermissions.forEach(permission => {
      const index = userForm.permissions.indexOf(permission.id)
      if (index > -1) {
        userForm.permissions.splice(index, 1)
      }
    })
  }
}

// Lifecycle
onMounted(() => {
  // Any initialization logic
})
</script>
