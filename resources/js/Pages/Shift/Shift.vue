<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Shift Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage employee shifts and schedules</p>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canCreate('shifts')"
            @click="showCreateModal = true" 
            class="bg-primary-600 hover:bg-primary-700"
          >
            <i class="pi pi-plus mr-2"></i>
            Create Shift
          </Button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-clock text-2xl text-blue-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Shifts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_shifts }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Shifts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.active_shifts }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-sun text-2xl text-yellow-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Morning Shifts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.morning_shifts }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-users text-2xl text-indigo-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Employees with Shifts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.employees_with_shifts }}</p>
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
            placeholder="Search shifts by name or description..."
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
            v-model="localFilters.shift_type"
            :options="shiftTypeOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Shift Type"
            class="w-32"
            @change="applyFilters"
          />
          <Calendar
            v-model="localFilters.start_time_from"
            timeOnly
            placeholder="From Time"
            class="w-32"
            @date-select="applyFilters"
          />
          <Calendar
            v-model="localFilters.start_time_to"
            timeOnly
            placeholder="To Time"
            class="w-32"
            @date-select="applyFilters"
          />
          <Button @click="clearFilters" outlined>
            <i class="pi pi-times mr-2"></i>
            Clear
          </Button>
        </div>
      </div>
    </div>

    <!-- Shifts Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <DataTable
        :value="shifts.data"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="name" header="Shift Name" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <Badge
                :value="getShiftType(data.start_time)"
                :severity="getShiftTypeSeverity(data.start_time)"
                class="mr-3"
              />
              <div>
                <div class="font-medium text-gray-900">{{ data.name }}</div>
                <div class="text-sm text-gray-500">{{ data.description || '-' }}</div>
              </div>
            </div>
          </template>
        </Column>
        
        <Column field="time_range" header="Time Range" sortable>
          <template #body="{ data }">
            <div class="flex items-center text-sm">
              <i class="pi pi-clock mr-2 text-gray-400"></i>
              <span class="font-medium">{{ formatTime(data.start_time) }}</span>
              <span class="mx-2 text-gray-400">-</span>
              <span class="font-medium">{{ formatTime(data.end_time) }}</span>
              <span class="ml-2 text-gray-500">({{ calculateDuration(data.start_time, data.end_time) }})</span>
            </div>
          </template>
        </Column>
        
        <Column field="employees_count" header="Employees" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <i class="pi pi-users text-blue-500 mr-1"></i>
              <span class="font-medium">{{ data.employees_count }}</span>
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
        
        <Column header="Actions" class="w-52">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button
                @click="viewShift(data)"
                size="small"
                outlined
                title="View Details"
              >
                <i class="pi pi-eye"></i>
              </Button>
              <Button
                v-if="canEdit('shifts')"
                @click="editShift(data)"
                size="small"
                outlined
                severity="warning"
                title="Edit Shift"
              >
                <i class="pi pi-pencil"></i>
              </Button>
              <Button
                v-if="canCreate('shifts')"
                @click="duplicateShift(data)"
                size="small"
                outlined
                severity="info"
                title="Duplicate Shift"
              >
                <i class="pi pi-copy"></i>
              </Button>
              <Button
                v-if="canEdit('shifts')"
                @click="toggleShiftStatus(data)"
                size="small"
                outlined
                :severity="data.is_active ? 'danger' : 'success'"
                :title="data.is_active ? 'Deactivate' : 'Activate'"
              >
                <i :class="data.is_active ? 'pi pi-times' : 'pi pi-check'"></i>
              </Button>
              <Button
                v-if="canDelete('shifts') && data.employees_count === 0"
                @click="deleteShift(data)"
                size="small"
                outlined
                severity="danger"
                title="Delete Shift"
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
          :rows="shifts.per_page"
          :totalRecords="shifts.total"
          :first="(shifts.current_page - 1) * shifts.per_page"
          @page="onPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          :rowsPerPageOptions="[10, 25, 50]"
        />
      </div>
    </div>

    <!-- Create/Edit Shift Modal -->
    <Dialog
      v-model:visible="showCreateModal"
      modal
      :header="editingShift ? 'Edit Shift' : 'Create Shift'"
      class="w-full max-w-2xl"
    >
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Shift Name *</label>
            <InputText
              v-model="shiftForm.name"
              placeholder="e.g., Morning Shift"
              class="w-full"
              :class="{ 'p-invalid': shiftFormErrors.name }"
            />
            <small v-if="shiftFormErrors.name" class="p-error">{{ shiftFormErrors.name }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <div class="flex items-center pt-2">
              <Checkbox
                v-model="shiftForm.is_active"
                inputId="is_active"
                :binary="true"
              />
              <label for="is_active" class="ml-2">Active</label>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Time *</label>
            <Calendar
              v-model="shiftForm.start_time"
              timeOnly
              hourFormat="24"
              placeholder="Select start time"
              class="w-full"
              :class="{ 'p-invalid': shiftFormErrors.start_time }"
            />
            <small v-if="shiftFormErrors.start_time" class="p-error">{{ shiftFormErrors.start_time }}</small>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Time *</label>
            <Calendar
              v-model="shiftForm.end_time"
              timeOnly
              hourFormat="24"
              placeholder="Select end time"
              class="w-full"
              :class="{ 'p-invalid': shiftFormErrors.end_time }"
            />
            <small v-if="shiftFormErrors.end_time" class="p-error">{{ shiftFormErrors.end_time }}</small>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <Textarea
            v-model="shiftForm.description"
            placeholder="Shift description..."
            class="w-full"
            rows="3"
          />
        </div>
        
        <!-- Time preview -->
        <div v-if="shiftForm.start_time && shiftForm.end_time" class="bg-gray-50 p-4 rounded-lg">
          <div class="flex items-center text-sm text-gray-600">
            <i class="pi pi-info-circle mr-2"></i>
            <span>Duration: {{ calculateDuration(shiftForm.start_time, shiftForm.end_time) }}</span>
            <span class="mx-3">•</span>
            <span>Type: {{ getShiftType(shiftForm.start_time) }}</span>
          </div>
        </div>
      </div>
      
      <template #footer>
        <Button @click="showCreateModal = false" outlined>Cancel</Button>
        <Button @click="saveShift" :loading="saving">
          {{ editingShift ? 'Update' : 'Create' }} Shift
        </Button>
      </template>
    </Dialog>

    <!-- Duplicate Shift Modal -->
    <Dialog
      v-model:visible="showDuplicateModal"
      modal
      header="Duplicate Shift"
      class="w-full max-w-md"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">New Shift Name</label>
          <InputText
            v-model="duplicateForm.name"
            placeholder="e.g., Morning Shift Copy"
            class="w-full"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <div class="flex items-center pt-2">
            <Checkbox
              v-model="duplicateForm.is_active"
              inputId="duplicate_is_active"
              :binary="true"
            />
            <label for="duplicate_is_active" class="ml-2">Active</label>
          </div>
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
import { format, parse, differenceInHours, addDays } from 'date-fns'
import AppLayout from '@/Layouts/AppLayout.vue'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'
import Paginator from 'primevue/paginator'

// Props
const props = defineProps({
  shifts: Object,
  statistics: Object,
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
const editingShift = ref(null)
const shiftToDelete = ref(null)
const shiftToDuplicate = ref(null)

// Filters
const localFilters = reactive({
  search: props.filters?.search || '',
  is_active: props.filters?.is_active ?? null,
  shift_type: props.filters?.shift_type || null,
  start_time_from: props.filters?.start_time_from || null,
  start_time_to: props.filters?.start_time_to || null,
  sort_by: props.filters?.sort_by || 'start_time',
  sort_order: props.filters?.sort_order || 'asc',
  per_page: props.filters?.per_page || 10
})

// Forms
const shiftForm = reactive({
  name: '',
  start_time: null,
  end_time: null,
  description: '',
  is_active: true
})

const duplicateForm = reactive({
  name: '',
  is_active: true
})

const shiftFormErrors = ref({})

// Options
const statusOptions = [
  { label: 'All Status', value: null },
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

const shiftTypeOptions = [
  { label: 'All Types', value: null },
  { label: 'Morning', value: 'morning' },
  { label: 'Afternoon', value: 'afternoon' },
  { label: 'Night', value: 'night' }
]

// Helper functions
const formatTime = (timeString) => {
  if (!timeString) return '-'
  try {
    if (timeString instanceof Date) {
      return format(timeString, 'HH:mm')
    }
    const parsed = parse(timeString, 'HH:mm:ss', new Date())
    return format(parsed, 'HH:mm')
  } catch (error) {
    return timeString
  }
}

const formatTimeForForm = (timeString) => {
  if (!timeString) return null
  try {
    const today = new Date()
    const [hours, minutes] = timeString.split(':').map(Number)
    return new Date(today.getFullYear(), today.getMonth(), today.getDate(), hours, minutes)
  } catch (error) {
    return null
  }
}

const formatTimeForSubmission = (dateObject) => {
  if (!dateObject) return ''
  return format(dateObject, 'HH:mm')
}

const calculateDuration = (startTime, endTime) => {
  if (!startTime || !endTime) return '-'
  
  try {
    let start, end
    
    if (startTime instanceof Date && endTime instanceof Date) {
      start = startTime
      end = endTime
    } else {
      const [startHours, startMinutes] = startTime.split(':').map(Number)
      const [endHours, endMinutes] = endTime.split(':').map(Number)
      
      start = new Date(2024, 0, 1, startHours, startMinutes)
      end = new Date(2024, 0, 1, endHours, endMinutes)
    }
    
    // Handle overnight shifts
    if (end < start) {
      end = addDays(end, 1)
    }
    
    const hours = differenceInHours(end, start)
    return `${hours} hours`
  } catch (error) {
    return '-'
  }
}

const getShiftType = (startTime) => {
  if (!startTime) return 'Unknown'
  
  try {
    let hour
    if (startTime instanceof Date) {
      hour = startTime.getHours()
    } else {
      hour = parseInt(startTime.split(':')[0])
    }
    
    if (hour >= 6 && hour < 14) return 'Morning'
    if (hour >= 14 && hour < 22) return 'Afternoon'
    return 'Night'
  } catch (error) {
    return 'Unknown'
  }
}

const getShiftTypeSeverity = (startTime) => {
  const type = getShiftType(startTime)
  switch (type) {
    case 'Morning': return 'success'
    case 'Afternoon': return 'warning'
    case 'Night': return 'info'
    default: return 'secondary'
  }
}

// Methods
const applyFilters = () => {
  const filterData = { ...localFilters }
  
  // Format time filters
  if (filterData.start_time_from) {
    filterData.start_time_from = formatTimeForSubmission(filterData.start_time_from)
  }
  if (filterData.start_time_to) {
    filterData.start_time_to = formatTimeForSubmission(filterData.start_time_to)
  }
  
  router.get(route('shifts.index'), filterData, {
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
    shift_type: null,
    start_time_from: null,
    start_time_to: null
  })
  applyFilters()
}

const onPageChange = (event) => {
  localFilters.per_page = event.rows
  router.get(route('shifts.index'), {
    ...localFilters,
    page: event.page + 1
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const resetShiftForm = () => {
  Object.assign(shiftForm, {
    name: '',
    start_time: null,
    end_time: null,
    description: '',
    is_active: true
  })
  shiftFormErrors.value = {}
  editingShift.value = null
}

const viewShift = (shift) => {
  router.get(route('shifts.show', shift.id))
}

const editShift = (shift) => {
  editingShift.value = shift
  Object.assign(shiftForm, {
    name: shift.name,
    start_time: formatTimeForForm(shift.start_time),
    end_time: formatTimeForForm(shift.end_time),
    description: shift.description || '',
    is_active: shift.is_active
  })
  showCreateModal.value = true
}

const duplicateShift = (shift) => {
  shiftToDuplicate.value = shift
  duplicateForm.name = `${shift.name} Copy`
  duplicateForm.is_active = true
  showDuplicateModal.value = true
}

const deleteShift = async (shift) => {
  if (confirm(`Are you sure you want to delete the shift "${shift.name}"?`)) {
    try {
      await router.delete(route('shifts.destroy', shift.id))
      router.reload()
    } catch (error) {
      console.error('Failed to delete shift:', error)
    }
  }
}

const toggleShiftStatus = async (shift) => {
  try {
    await router.patch(route('shifts.toggle-status', shift.id))
    router.reload()
  } catch (error) {
    console.error('Failed to toggle shift status:', error)
  }
}

const saveShift = async () => {
  saving.value = true
  shiftFormErrors.value = {}
  
  try {
    const formData = {
      name: shiftForm.name,
      start_time: formatTimeForSubmission(shiftForm.start_time),
      end_time: formatTimeForSubmission(shiftForm.end_time),
      description: shiftForm.description,
      is_active: shiftForm.is_active
    }
    
    const url = editingShift.value 
      ? route('shifts.update', editingShift.value.id)
      : route('shifts.store')
      
    const method = editingShift.value ? 'put' : 'post'
    
    await router[method](url, formData, {
      onSuccess: () => {
        showCreateModal.value = false
        resetShiftForm()
      },
      onError: (errors) => {
        shiftFormErrors.value = errors
      }
    })
  } catch (error) {
    console.error('Failed to save shift:', error)
  } finally {
    saving.value = false
  }
}

const saveDuplicate = async () => {
  saving.value = true
  
  try {
    await router.post(route('shifts.duplicate', shiftToDuplicate.value.id), duplicateForm, {
      onSuccess: () => {
        showDuplicateModal.value = false
        duplicateForm.name = ''
        duplicateForm.is_active = true
        shiftToDuplicate.value = null
      }
    })
  } catch (error) {
    console.error('Failed to duplicate shift:', error)
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(() => {
  // Any initialization logic
})
</script>