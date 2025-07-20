<template>
  <Head title="Employees" />
  
  <AppLayout 
    title="Employees" 
    subtitle="Manage employee records and information"
    :user="page.props.auth?.user"
  >
    <template #headerActions>
      <div class="flex gap-3">
        <Button 
          icon="pi pi-download"
          label="Export"
          severity="secondary"
          outlined
          @click="exportData"
        />
        <Button 
          icon="pi pi-plus"
          label="Add Employee"
          @click="router.visit('/employees/create')" 
          size="large"
          class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 border-0 shadow-lg px-6 py-3"
        />
      </div>
    </template>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-100 text-sm font-medium">Total Employees</p>
            <p class="text-3xl font-bold">{{ filteredEmployees?.total || 0 }}</p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-lg p-3">
            <i class="pi pi-users text-2xl"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-100 text-sm font-medium">Active</p>
            <p class="text-3xl font-bold">{{ getStatusCount('active') }}</p>
          </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
            <i class="pi pi-check-circle text-2xl"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-orange-100 text-sm font-medium">Locations</p>
            <p class="text-3xl font-bold">{{ locations?.length || 0 }}</p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-lg p-3">
            <i class="pi pi-map-marker text-2xl"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-100 text-sm font-medium">This Month</p>
            <p class="text-3xl font-bold">{{ getNewEmployeesCount() }}</p>
          </div>
          <div class="bg-white bg-opacity-20 rounded-lg p-3">
            <i class="pi pi-calendar-plus text-2xl"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filters Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="pi pi-search text-blue-500 mr-2"></i>
            Search & Filter
          </h3>
          <Button 
            :icon="showFilters ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
            @click="showFilters = !showFilters"
            class="p-button-text p-button-sm"
            :label="showFilters ? 'Hide Filters' : 'Show Filters'"
          />
        </div>
        
        <!-- Always visible search -->
        <div class="mb-4">
          <div class="relative">
            <i class="pi pi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-base z-10"></i>
            <AutoComplete
              v-model="searchQuery"
              :suggestions="searchSuggestions"
              @complete="searchEmployees"
              @item-select="selectEmployee"
              @keyup.enter="performSearch"
              placeholder="Search employees by name, ID, email, position..."
              class="w-full"
              :loading="searchLoading"
              forceSelection="false"
              completeOnFocus="false"
            />
          </div>
        </div>

        <!-- Expandable filters -->
        <Transition name="slide-down">
          <div v-show="showFilters" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Status Filter with chips -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="pi pi-flag mr-1"></i>Status
                </label>
                <div class="flex flex-wrap gap-2 mb-2">
                  <div 
                    v-for="status in statusOptions.slice(1)" 
                    :key="status.value"
                    @click="toggleStatusFilter(status.value)"
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-all',
                      filters.status === status.value 
                        ? 'bg-blue-500 text-white' 
                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                  >
                    {{ status.label }}
                  </div>
                </div>
              </div>

              <!-- Location Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="pi pi-map-marker mr-1"></i>Location
                </label>
                <Dropdown
                  v-model="filters.location_id"
                  :options="locations"
                  optionLabel="name"
                  optionValue="id"
                  placeholder="All Locations"
                  class="w-full"
                  showClear
                  @change="applyClientSideFilters"
                />
              </div>

              <!-- Quick Actions -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  <i class="pi pi-bolt mr-1"></i>Quick Actions
                </label>
                <div class="flex gap-2">
                  <Button 
                    icon="pi pi-search" 
                    label="Apply" 
                    @click="applyFilters"
                    size="small"
                    class="flex-1"
                  />
                  <Button 
                    icon="pi pi-refresh" 
                    label="Reset" 
                    @click="clearFilters"
                    severity="secondary"
                    outlined
                    size="small"
                    class="flex-1"
                  />
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="pi pi-table text-blue-500 mr-2"></i>
            Employee Directory
            <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
              {{ filteredEmployees?.total || 0 }} employees
            </span>
          </h3>
          <div class="flex gap-2">
            <Button 
              icon="pi pi-plus" 
              label="Add Employee"
              @click="router.visit('/employees/create')"
              class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 border-0"
            />
            <Button 
              icon="pi pi-upload" 
              label="Import"
              @click="importEmployees"
              severity="secondary"
              outlined
            />
            <Button 
              icon="pi pi-refresh" 
              @click="refreshData"
              class="p-button-text p-button-sm"
              v-tooltip="'Refresh Data'"
            />
            <Button 
              icon="pi pi-download" 
              @click="exportData"
              class="p-button-text p-button-sm"
              v-tooltip="'Export Data'"
            />
          </div>
        </div>

        <DataTable 
          :value="paginatedEmployees" 
          :loading="loading"
          stripedRows
          responsiveLayout="scroll"
          :paginator="true"
          :rows="itemsPerPage"
          :totalRecords="filteredEmployees?.total || 0"
          :lazy="false"
          @page="onPage"
          paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
          currentPageReportTemplate="Showing {first} to {last} of {totalRecords} employees"
          :rowsPerPageOptions="[10, 25, 50]"
          class="p-datatable-customers"
          dataKey="id"
          :globalFilterFields="['name', 'email', 'position']"
        >
          <template #empty>
            <div class="text-center py-12">
              <i class="pi pi-users text-6xl text-gray-300 mb-4"></i>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No employees found</h3>
              <p class="text-gray-500 mb-6">Get started by adding your first employee to the system</p>
              <div class="flex justify-center gap-4">
                <Button 
                  label="Add Employee" 
                  icon="pi pi-plus"
                  @click="router.visit('/employees/create')"
                  size="large"
                  class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 border-0"
                />
                <Button 
                  label="Import Employees" 
                  icon="pi pi-upload"
                  @click="importEmployees"
                  size="large"
                  severity="secondary"
                  outlined
                  class="px-8 py-3"
                />
              </div>
            </div>
          </template>

          <Column field="employee_id" header="Employee ID" :sortable="true" class="min-w-32">
            <template #body="slotProps">
              <div class="flex items-center">
                <span class="font-mono text-sm font-medium bg-gray-50 px-2 py-1 rounded border">
                  {{ slotProps.data.employee_id }}
                </span>
              </div>
            </template>
          </Column>

          <Column field="name" header="Employee" :sortable="true" class="min-w-64">
            <template #body="slotProps">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-12 w-12 mr-4">
                  <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-400 to-blue-500 flex items-center justify-center text-white font-semibold shadow-md">
                    {{ getInitials(slotProps.data.name) }}
                  </div>
                </div>
                <div>
                  <div class="font-semibold text-gray-900 hover:text-blue-600 cursor-pointer transition-colors"
                       @click="viewEmployee(slotProps.data)">
                    {{ slotProps.data.name }}
                  </div>
                  <div class="text-sm text-gray-500 flex items-center">
                    <i class="pi pi-envelope mr-1"></i>
                    {{ slotProps.data.email || 'No email' }}
                  </div>
                </div>
              </div>
            </template>
          </Column>

          <Column field="position" header="Position" :sortable="true" class="min-w-48">
            <template #body="slotProps">
              <div>
                <div class="font-medium text-gray-900">{{ slotProps.data.position || '-' }}</div>
                <div class="text-sm text-gray-500 flex items-center">
                  <i class="pi pi-building mr-1"></i>
                  {{ slotProps.data.department || '-' }}
                </div>
              </div>
            </template>
          </Column>

          <Column field="location.name" header="Location" :sortable="true" class="min-w-40">
            <template #body="slotProps">
              <div class="flex items-center text-gray-700">
                <i class="pi pi-map-marker text-orange-500 mr-2"></i>
                <span class="font-medium">{{ slotProps.data.location?.name || '-' }}</span>
              </div>
            </template>
          </Column>

          <Column field="hire_date" header="Hire Date" :sortable="true" class="min-w-36">
            <template #body="slotProps">
              <div class="text-center">
                <div class="font-medium text-gray-900">{{ formatDate(slotProps.data.hire_date) }}</div>
                <div class="text-xs text-gray-500" v-if="slotProps.data.years_of_service">
                  {{ slotProps.data.years_of_service }} years
                </div>
              </div>
            </template>
          </Column>

          <Column field="status" header="Status" :sortable="true" class="min-w-32">
            <template #body="slotProps">
              <div class="text-center">
                <Tag 
                  :value="slotProps.data.status_label || slotProps.data.status" 
                  :severity="slotProps.data.status_color || 'info'"
                  class="font-medium"
                />
              </div>
            </template>
          </Column>

          <Column header="Actions" :exportable="false" class="min-w-48">
            <template #body="slotProps">
              <div class="flex gap-2 justify-center">
                <Button 
                  icon="pi pi-eye"
                  size="small"
                  severity="info"
                  outlined
                  @click="viewEmployee(slotProps.data)"
                  v-tooltip="'View Details'"
                />
                <Button 
                  icon="pi pi-pencil"
                  size="small"
                  severity="warning"
                  outlined
                  @click="editEmployee(slotProps.data)"
                  v-tooltip="'Edit Employee'"
                />
                <Button 
                  icon="pi pi-trash"
                  size="small"
                  severity="danger"
                  outlined
                  @click="confirmDelete(slotProps.data)"
                  v-tooltip="'Delete Employee'"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </div>
    </div>

    <!-- Enhanced Delete Confirmation Dialog -->
    <Dialog 
      v-model:visible="deleteDialog" 
      :style="{width: '500px'}" 
      header="Delete Employee" 
      :modal="true"
      class="p-fluid"
    >
      <div class="text-center">
        <div class="bg-red-100 rounded-full p-6 w-24 h-24 mx-auto mb-4 flex items-center justify-center">
          <i class="pi pi-exclamation-triangle text-4xl text-red-500"></i>
        </div>
        
        <h3 class="text-xl font-semibold text-gray-900 mb-2">Are you absolutely sure?</h3>
        <p class="text-gray-600 mb-4">
          You are about to permanently delete <strong class="text-red-600">{{ selectedEmployee?.name }}</strong>. 
          This action cannot be undone and will remove all associated data.
        </p>
        
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Employee ID:</span>
            <span class="font-mono font-medium">{{ selectedEmployee?.employee_id }}</span>
          </div>
          <div class="flex items-center justify-between text-sm mt-1">
            <span class="text-gray-600">Position:</span>
            <span class="font-medium">{{ selectedEmployee?.position || 'N/A' }}</span>
          </div>
          <div class="flex items-center justify-between text-sm mt-1">
            <span class="text-gray-600">Department:</span>
            <span class="font-medium">{{ selectedEmployee?.department || 'N/A' }}</span>
          </div>
        </div>
      </div>
      
      <template #footer>
        <div class="flex gap-3 justify-center">
          <Button 
            label="Cancel" 
            icon="pi pi-times" 
            class="p-button-outlined flex-1" 
            @click="deleteDialog = false"
          />
          <Button 
            label="Delete Employee" 
            icon="pi pi-trash" 
            class="p-button-danger flex-1" 
            @click="deleteEmployee"
            :loading="deleting"
          />
        </div>
      </template>
    </Dialog>

    <!-- Floating Action Button -->
    <div class="fixed bottom-6 right-6 z-50">
      <Button 
        icon="pi pi-plus"
        @click="router.visit('/employees/create')"
        class="p-button-rounded p-button-lg bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 border-0 shadow-2xl"
        style="width: 60px; height: 60px;"
        v-tooltip="'Add New Employee'"
      />
    </div>
  </AppLayout>
</template>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* AutoComplete search input styling */
:deep(.p-autocomplete) {
  width: 100%;
}

:deep(.p-autocomplete .p-inputtext) {
  width: 100%;
  padding: 12px 16px 12px 48px !important;
  font-size: 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  transition: all 0.2s ease;
  background-color: #ffffff;
}

:deep(.p-autocomplete .p-inputtext:focus) {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

:deep(.p-autocomplete .p-inputtext::placeholder) {
  color: #9ca3af;
  font-size: 14px;
}

/* Loading icon styling */
:deep(.p-autocomplete .p-autocomplete-loader) {
  right: 12px;
}

.p-datatable-customers .p-datatable-tbody > tr:hover {
  background: #f8fafc;
}

.p-datatable-customers .p-datatable-thead > tr > th {
  background: #f1f5f9;
  color: #475569;
  font-weight: 500;
  font-size: 0.875rem;
  border-bottom: 1px solid #e2e8f0;
}

.p-datatable-customers .p-datatable-tbody > tr > td {
  font-size: 0.875rem;
  font-weight: 400;
  padding: 0.75rem;
}

.p-datatable-customers .p-datatable-tbody > tr > td .font-semibold {
  font-weight: 500;
}

.p-datatable-customers .p-datatable-tbody > tr > td .font-medium {
  font-weight: 500;
}
</style>

<script setup>
import { ref, reactive, onMounted, computed, Transition, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import { employeeApi } from '@/utils/api'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import AutoComplete from 'primevue/autocomplete'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import { debounce } from 'lodash'

const props = defineProps({
    employees: Object,
    locations: Array,
    filters: Object
})

const page = usePage()
const toast = useToast()

// Reactive data
const loading = ref(false)
const searchLoading = ref(false)
const searchQuery = ref('')
const searchSuggestions = ref([])
const deleteDialog = ref(false)
const selectedEmployee = ref(null)
const deleting = ref(false)
const showFilters = ref(false)

// Original data and filtered data
const originalEmployees = ref(props.employees)
const filteredEmployees = ref(props.employees)

const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status ?? null,
    location_id: props.filters?.location_id ?? null
})

// Options for dropdowns
const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Resigned', value: 'resigned' },
    { label: 'Terminated', value: 'terminated' }
]

// Stats computed functions
const getStatusCount = (status) => {
    if (!filteredEmployees.value?.data) return 0
    return filteredEmployees.value.data.filter(emp => emp.status === status).length
}

const getNewEmployeesCount = () => {
    if (!filteredEmployees.value?.data) return 0
    const currentMonth = new Date().getMonth()
    const currentYear = new Date().getFullYear()
    
    return filteredEmployees.value.data.filter(emp => {
        if (!emp.hire_date) return false
        const hireDate = new Date(emp.hire_date)
        return hireDate.getMonth() === currentMonth && hireDate.getFullYear() === currentYear
    }).length
}

// JavaScript-based filtering function
const applyClientSideFilters = () => {
    if (!originalEmployees.value?.data) return

    let filtered = [...originalEmployees.value.data]

    // Apply search filter
    if (filters.search && filters.search.trim()) {
        const searchTerm = filters.search.toLowerCase().trim()
        filtered = filtered.filter(emp => 
            emp.name?.toLowerCase().includes(searchTerm) ||
            emp.employee_id?.toLowerCase().includes(searchTerm) ||
            emp.email?.toLowerCase().includes(searchTerm) ||
            emp.position?.toLowerCase().includes(searchTerm) ||
            emp.department?.toLowerCase().includes(searchTerm)
        )
    }

    // Apply status filter
    if (filters.status !== null) {
        filtered = filtered.filter(emp => emp.status === filters.status)
    }

    // Apply location filter
    if (filters.location_id !== null) {
        filtered = filtered.filter(emp => {
            // Handle both direct location_id and nested location object
            const empLocationId = emp.location_id || emp.location?.id
            return empLocationId === filters.location_id
        })
    }

    // Update filtered data
    filteredEmployees.value = {
        ...originalEmployees.value,
        data: filtered,
        total: filtered.length,
        per_page: originalEmployees.value.per_page,
        current_page: 1,
        last_page: Math.ceil(filtered.length / (originalEmployees.value.per_page || 10))
    }

    // Reset pagination to first page
    currentPage.value = 1
}

// Toggle status filter
const toggleStatusFilter = (status) => {
    if (filters.status === status) {
        filters.status = null
    } else {
        filters.status = status
    }
    applyClientSideFilters()
}

// Export functionality
const exportData = () => {
    toast.add({
        severity: 'info',
        summary: 'Export Started',
        detail: 'Preparing employee data for export...',
        life: 3000
    })
    
    // Simulate export process
    setTimeout(() => {
        toast.add({
            severity: 'success',
            summary: 'Export Complete',
            detail: 'Employee data has been exported successfully',
            life: 3000
        })
    }, 2000)
}

// Import functionality
const importEmployees = () => {
    toast.add({
        severity: 'info',
        summary: 'Import Feature',
        detail: 'Import functionality will be available soon',
        life: 3000
    })
}

// Search functionality with local filtering
const searchEmployees = debounce(async (event) => {
    if (!event.query.trim()) {
        searchSuggestions.value = []
        return
    }

    // Use local data for suggestions
    if (originalEmployees.value?.data) {
        const query = event.query.toLowerCase()
        const filteredEmployees = originalEmployees.value.data.filter(emp => 
            emp.name?.toLowerCase().includes(query) ||
            emp.employee_id?.toLowerCase().includes(query) ||
            emp.email?.toLowerCase().includes(query) ||
            emp.position?.toLowerCase().includes(query)
        ).slice(0, 10) // Limit to 10 results
        
        // Return only names for suggestions
        searchSuggestions.value = filteredEmployees.map(emp => emp.name)
    }
}, 200)

const selectEmployee = (event) => {
    // When user selects a name from autocomplete, trigger search
    if (event.value && typeof event.value === 'string') {
        searchQuery.value = event.value
        filters.search = event.value
        applyClientSideFilters()
    }
}

// Add manual search trigger
const performSearch = () => {
    filters.search = searchQuery.value
    applyClientSideFilters()
}

// Status severity helper
const getStatusSeverity = (status) => {
    const statusMap = {
        'active': 'success',
        'inactive': 'warning', 
        'resigned': 'info',
        'terminated': 'danger'
    }
    return statusMap[status] || 'info'
}

// Filter functionality with client-side filtering
const applyFilters = () => {
    applyClientSideFilters()
}

const clearFilters = () => {
    filters.search = ''
    filters.status = null
    filters.location_id = null
    searchQuery.value = ''
    
    // Reset to show all data
    filteredEmployees.value = originalEmployees.value
    
    toast.add({
        severity: 'success',
        summary: 'Filters Cleared',
        detail: 'All filters have been cleared',
        life: 2000
    })
}

const refreshData = async () => {
    try {
        loading.value = true
        const { data } = await employeeApi.getEmployees()
        
        if (data.props?.employees) {
            // Update original data
            originalEmployees.value = data.props.employees
            // Reset filters and show all data
            filters.search = ''
            filters.status = null
            filters.location_id = null
            searchQuery.value = ''
            filteredEmployees.value = originalEmployees.value
        }
        
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Data refreshed successfully',
            life: 3000
        })
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to refresh data',
            life: 3000
        })
    } finally {
        loading.value = false
    }
}

// Client-side pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

const paginatedEmployees = computed(() => {
    if (!filteredEmployees.value?.data) return []
    
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    
    return filteredEmployees.value.data.slice(start, end)
})

const totalPages = computed(() => {
    if (!filteredEmployees.value?.data) return 0
    return Math.ceil(filteredEmployees.value.data.length / itemsPerPage.value)
})

const onPage = (event) => {
    currentPage.value = event.page + 1
    itemsPerPage.value = event.rows
}

// CRUD actions dengan navigasi langsung untuk view dan edit
const viewEmployee = (employee) => {
    router.visit(`/employees/${employee.id}`)
}

const editEmployee = (employee) => {
    router.visit(`/employees/${employee.id}/edit`)
}

// Delete functionality
const confirmDelete = (employee) => {
    selectedEmployee.value = employee
    deleteDialog.value = true
}

const deleteEmployee = async () => {
    if (!selectedEmployee.value) return

    deleting.value = true
    try {
        await employeeApi.deleteEmployee(selectedEmployee.value.id)

        // Remove from local data
        const currentData = [...(originalEmployees.value?.data || [])]
        const updatedData = currentData.filter(emp => emp.id !== selectedEmployee.value.id)
        
        // Update both original and filtered data
        if (originalEmployees.value) {
            originalEmployees.value.data = updatedData
            originalEmployees.value.total = (originalEmployees.value.total || 0) - 1
        }
        
        // Reapply filters to update filtered data
        applyClientSideFilters()

        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Employee deleted successfully',
            life: 3000
        })
        
        deleteDialog.value = false
        selectedEmployee.value = null
    } catch (error) {
        console.error('Delete error:', error)
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to delete employee',
            life: 3000
        })
    } finally {
        deleting.value = false
    }
}

// Utility functions
const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getInitials = (name) => {
    if (!name) return ''
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

onMounted(() => {
    if (props.filters?.search) {
        searchQuery.value = props.filters.search
        filters.search = props.filters.search
    }
    
    // Initialize filtered data
    applyClientSideFilters()
})

// Watch for filter changes
watch([() => filters.search, () => filters.status, () => filters.location_id], () => {
    currentPage.value = 1 // Reset to first page when filters change
    applyClientSideFilters()
}, { deep: true })

// Watch for search query changes
watch(searchQuery, (newValue) => {
    filters.search = newValue
}, { debounce: 300 })
</script>
