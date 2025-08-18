<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Presence Management</h1>
          <p class="text-sm text-gray-600 mt-1">Track employee attendance and work hours</p>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canCreate('presences')"
            @click="showQuickCheckInModal = true" 
            class="bg-green-600 hover:bg-green-700"
          >
            <i class="pi pi-clock mr-2"></i>
            Quick Check-in
          </Button>
          <Button 
            v-if="canCreate('presences')"
            @click="showCreateModal = true" 
            class="bg-primary-600 hover:bg-primary-700"
          >
            <i class="pi pi-plus mr-2"></i>
            Add Presence
          </Button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-calendar text-2xl text-blue-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Today's Attendance</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_today }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Present Today</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.present_today }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-exclamation-triangle text-2xl text-orange-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Late Today</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.late_today }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-clock text-2xl text-purple-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Avg Work Hours</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.avg_work_hours }}h</p>
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
            placeholder="Search by employee name or ID..."
            class="w-full"
            @input="debouncedSearch"
          />
        </div>
        <div class="flex gap-3">
          <Dropdown
            v-model="localFilters.employee_id"
            :options="availableEmployees"
            optionLabel="name"
            optionValue="id"
            placeholder="Employee"
            class="w-48"
            showClear
            @change="applyFilters"
          />
          <Dropdown
            v-model="localFilters.shift_id"
            :options="availableShifts"
            optionLabel="name"
            optionValue="id"
            placeholder="Shift"
            class="w-32"
            showClear
            @change="applyFilters"
          />
          <Dropdown
            v-model="localFilters.status"
            :options="statusOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Status"
            class="w-32"
            showClear
            @change="applyFilters"
          />
          <Calendar
            v-model="localFilters.date_from"
            placeholder="From Date"
            class="w-32"
            showIcon
            @date-select="applyFilters"
          />
          <Calendar
            v-model="localFilters.date_to"
            placeholder="To Date"
            class="w-32"
            showIcon
            @date-select="applyFilters"
          />
          <Button @click="clearFilters" outlined>
            <i class="pi pi-times mr-2"></i>
            Clear
          </Button>
        </div>
      </div>
    </div>

    <!-- Presences Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <DataTable
        :value="presences.data"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="employee" header="Employee" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                <i class="pi pi-user text-gray-500"></i>
              </div>
              <div>
                <div class="font-medium text-gray-900">{{ data.employee?.name }}</div>
                <div class="text-sm text-gray-500">{{ data.employee?.employee_id }}</div>
              </div>
            </div>
          </template>
        </Column>
        
        <Column field="shift" header="Shift" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <Badge
                :value="getShiftType(data.shift?.start_time)"
                :severity="getShiftTypeSeverity(data.shift?.start_time)"
                class="mr-2"
              />
              <div>
                <div class="font-medium text-gray-900">{{ data.shift?.name }}</div>
                <div class="text-sm text-gray-500">
                  {{ formatTime(data.shift?.start_time) }} - {{ formatTime(data.shift?.end_time) }}
                </div>
              </div>
            </div>
          </template>
        </Column>

        <Column field="check_in" header="Check In" sortable>
          <template #body="{ data }">
            <div class="text-sm">
              <div class="font-medium text-gray-900">{{ formatDateTime(data.check_in) }}</div>
              <div v-if="data.is_late" class="text-red-500 text-xs">
                <i class="pi pi-exclamation-triangle mr-1"></i>
                Late
              </div>
            </div>
          </template>
        </Column>

        <Column field="check_out" header="Check Out" sortable>
          <template #body="{ data }">
            <div class="text-sm">
              <div v-if="data.check_out" class="font-medium text-gray-900">
                {{ formatDateTime(data.check_out) }}
              </div>
              <div v-else class="text-gray-500 italic">Not checked out</div>
              <div v-if="data.is_early_checkout" class="text-orange-500 text-xs">
                <i class="pi pi-clock mr-1"></i>
                Early
              </div>
            </div>
          </template>
        </Column>

        <Column field="work_hours" header="Work Hours" sortable>
          <template #body="{ data }">
            <div class="text-sm">
              <div class="font-medium text-gray-900">
                {{ data.work_hours ? `${data.work_hours}h` : '-' }}
              </div>
              <div v-if="data.overtime_hours > 0" class="text-blue-500 text-xs">
                +{{ data.overtime_hours }}h OT
              </div>
            </div>
          </template>
        </Column>

        <Column field="status" header="Status" sortable>
          <template #body="{ data }">
            <Badge
              :value="data.status"
              :severity="getStatusSeverity(data.status)"
              class="capitalize"
            />
          </template>
        </Column>
        
        <Column header="Actions" class="w-48">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button
                @click="viewPresence(data)"
                size="small"
                outlined
                title="View Details"
              >
                <i class="pi pi-eye"></i>
              </Button>
              <Button
                v-if="canEdit('presences')"
                @click="editPresence(data)"
                size="small"
                outlined
                severity="warning"
                title="Edit Presence"
              >
                <i class="pi pi-pencil"></i>
              </Button>
              <Button
                v-if="canEdit('presences') && !data.check_out"
                @click="checkOutEmployee(data)"
                size="small"
                outlined
                severity="success"
                title="Check Out"
              >
                <i class="pi pi-sign-out"></i>
              </Button>
              <Button
                v-if="canDelete('presences')"
                @click="deletePresence(data)"
                size="small"
                outlined
                severity="danger"
                title="Delete Presence"
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
          :rows="presences.per_page"
          :totalRecords="presences.total"
          :first="(presences.current_page - 1) * presences.per_page"
          @page="onPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          :rowsPerPageOptions="[10, 25, 50]"
        />
      </div>
    </div>

    <!-- Quick Check-in Modal -->
    <Dialog
      v-model:visible="showQuickCheckInModal"
      modal
      header="Quick Check-in"
      class="w-full max-w-md"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
          <Dropdown
            v-model="quickCheckInForm.employee_id"
            :options="availableEmployees"
            optionLabel="name"
            optionValue="id"
            placeholder="Select employee"
            class="w-full"
            :class="{ 'p-invalid': quickCheckInFormErrors.employee_id }"
            filter
          />
          <small v-if="quickCheckInFormErrors.employee_id" class="p-error">
            {{ quickCheckInFormErrors.employee_id }}
          </small>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Shift *</label>
          <Dropdown
            v-model="quickCheckInForm.shift_id"
            :options="availableShifts"
            optionLabel="name"
            optionValue="id"
            placeholder="Select shift"
            class="w-full"
            :class="{ 'p-invalid': quickCheckInFormErrors.shift_id }"
          />
          <small v-if="quickCheckInFormErrors.shift_id" class="p-error">
            {{ quickCheckInFormErrors.shift_id }}
          </small>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
          <Textarea
            v-model="quickCheckInForm.notes_checkin"
            placeholder="Optional check-in notes"
            class="w-full"
            rows="3"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2">
          <Button @click="showQuickCheckInModal = false" outlined>Cancel</Button>
          <Button @click="submitQuickCheckIn" :loading="submitting">
            Check In
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Create/Edit Presence Modal -->
    <Dialog
      v-model:visible="showCreateModal"
      modal
      :header="editingPresence ? 'Edit Presence' : 'Create Presence'"
      class="w-full max-w-4xl"
    >
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
            <Dropdown
              v-model="presenceForm.employee_id"
              :options="availableEmployees"
              optionLabel="name"
              optionValue="id"
              placeholder="Select employee"
              class="w-full"
              :class="{ 'p-invalid': presenceFormErrors.employee_id }"
              filter
            />
            <small v-if="presenceFormErrors.employee_id" class="p-error">
              {{ presenceFormErrors.employee_id }}
            </small>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Shift *</label>
            <Dropdown
              v-model="presenceForm.shift_id"
              :options="availableShifts"
              optionLabel="name"
              optionValue="id"
              placeholder="Select shift"
              class="w-full"
              :class="{ 'p-invalid': presenceFormErrors.shift_id }"
            />
            <small v-if="presenceFormErrors.shift_id" class="p-error">
              {{ presenceFormErrors.shift_id }}
            </small>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check In *</label>
            <Calendar
              v-model="presenceForm.check_in"
              showTime
              hourFormat="24"
              placeholder="Select check-in time"
              class="w-full"
              :class="{ 'p-invalid': presenceFormErrors.check_in }"
            />
            <small v-if="presenceFormErrors.check_in" class="p-error">
              {{ presenceFormErrors.check_in }}
            </small>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check Out</label>
            <Calendar
              v-model="presenceForm.check_out"
              showTime
              hourFormat="24"
              placeholder="Select check-out time"
              class="w-full"
              :class="{ 'p-invalid': presenceFormErrors.check_out }"
            />
            <small v-if="presenceFormErrors.check_out" class="p-error">
              {{ presenceFormErrors.check_out }}
            </small>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Notes</label>
            <Textarea
              v-model="presenceForm.notes_checkin"
              placeholder="Check-in notes"
              class="w-full"
              rows="3"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Notes</label>
            <Textarea
              v-model="presenceForm.notes_checkout"
              placeholder="Check-out notes"
              class="w-full"
              rows="3"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">General Notes</label>
          <Textarea
            v-model="presenceForm.notes"
            placeholder="General notes about this presence record"
            class="w-full"
            rows="2"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2">
          <Button @click="showCreateModal = false" outlined>Cancel</Button>
          <Button @click="submitPresence" :loading="submitting">
            {{ editingPresence ? 'Update' : 'Create' }}
          </Button>
        </div>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import { format } from 'date-fns'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePermissions } from '@/composables/usePermissions'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'

// Props
const props = defineProps({
  presences: Object,
  statistics: Object,
  availableEmployees: Array,
  availableShifts: Array,
  filters: Object,
  user: Object
})

// Permissions
const { canView, canCreate, canEdit, canDelete } = usePermissions()

// Reactive state
const loading = ref(false)
const submitting = ref(false)
const showQuickCheckInModal = ref(false)
const showCreateModal = ref(false)
const editingPresence = ref(null)

// Filter state
const localFilters = reactive({
  search: props.filters.search || '',
  employee_id: props.filters.employee_id || null,
  shift_id: props.filters.shift_id || null,
  status: props.filters.status || null,
  date_from: props.filters.date_from ? new Date(props.filters.date_from) : null,
  date_to: props.filters.date_to ? new Date(props.filters.date_to) : null,
})

// Form states
const quickCheckInForm = reactive({
  employee_id: null,
  shift_id: null,
  notes_checkin: ''
})

const presenceForm = reactive({
  employee_id: null,
  shift_id: null,
  check_in: null,
  check_out: null,
  notes_checkin: '',
  notes_checkout: '',
  notes: ''
})

const quickCheckInFormErrors = ref({})
const presenceFormErrors = ref({})

// Options
const statusOptions = [
  { label: 'Present', value: 'present' },
  { label: 'Late', value: 'late' },
  { label: 'Absent', value: 'absent' },
  { label: 'Partial', value: 'partial' }
]

// Methods
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  loading.value = true
  
  const filterData = {
    search: localFilters.search || undefined,
    employee_id: localFilters.employee_id || undefined,
    shift_id: localFilters.shift_id || undefined,
    status: localFilters.status || undefined,
    date_from: localFilters.date_from ? format(localFilters.date_from, 'yyyy-MM-dd') : undefined,
    date_to: localFilters.date_to ? format(localFilters.date_to, 'yyyy-MM-dd') : undefined,
  }

  router.get(route('presences.index'), filterData, {
    preserveState: true,
    onFinish: () => loading.value = false
  })
}

const clearFilters = () => {
  Object.keys(localFilters).forEach(key => {
    localFilters[key] = key.includes('date') ? null : ''
  })
  
  router.get(route('presences.index'), {}, {
    preserveState: true,
    onFinish: () => loading.value = false
  })
}

const onPageChange = (event) => {
  const filterData = {
    ...localFilters,
    page: event.page + 1,
    per_page: event.rows
  }

  router.get(route('presences.index'), filterData, {
    preserveState: true
  })
}

const viewPresence = (presence) => {
  router.get(route('presences.show', presence.id))
}

const editPresence = (presence) => {
  editingPresence.value = presence
  presenceForm.employee_id = presence.employee_id
  presenceForm.shift_id = presence.shift_id
  presenceForm.check_in = presence.check_in ? new Date(presence.check_in) : null
  presenceForm.check_out = presence.check_out ? new Date(presence.check_out) : null
  presenceForm.notes_checkin = presence.notes_checkin || ''
  presenceForm.notes_checkout = presence.notes_checkout || ''
  presenceForm.notes = presence.notes || ''
  showCreateModal.value = true
}

const deletePresence = async (presence) => {
  if (confirm(`Are you sure you want to delete this presence record for ${presence.employee?.name}?`)) {
    try {
      await router.delete(route('presences.destroy', presence.id))
    } catch (error) {
      console.error('Failed to delete presence:', error)
    }
  }
}

const checkOutEmployee = (presence) => {
  if (confirm(`Check out ${presence.employee?.name}?`)) {
    router.post(route('presences.check-out', presence.id), {
      notes_checkout: 'Quick check-out'
    })
  }
}

const submitQuickCheckIn = async () => {
  quickCheckInFormErrors.value = {}
  submitting.value = true

  try {
    await router.post(route('presences.check-in'), quickCheckInForm, {
      onSuccess: () => {
        showQuickCheckInModal.value = false
        resetQuickCheckInForm()
      },
      onError: (errors) => {
        quickCheckInFormErrors.value = errors
      },
      onFinish: () => submitting.value = false
    })
  } catch (error) {
    submitting.value = false
  }
}

const submitPresence = async () => {
  presenceFormErrors.value = {}
  submitting.value = true

  const formData = {
    ...presenceForm,
    check_in: presenceForm.check_in ? format(presenceForm.check_in, 'yyyy-MM-dd HH:mm:ss') : null,
    check_out: presenceForm.check_out ? format(presenceForm.check_out, 'yyyy-MM-dd HH:mm:ss') : null,
  }

  try {
    const url = editingPresence.value 
      ? route('presences.update', editingPresence.value.id)
      : route('presences.store')
    
    const method = editingPresence.value ? 'put' : 'post'

    await router[method](url, formData, {
      onSuccess: () => {
        showCreateModal.value = false
        resetPresenceForm()
      },
      onError: (errors) => {
        presenceFormErrors.value = errors
      },
      onFinish: () => submitting.value = false
    })
  } catch (error) {
    submitting.value = false
  }
}

const resetQuickCheckInForm = () => {
  Object.keys(quickCheckInForm).forEach(key => {
    quickCheckInForm[key] = key === 'notes_checkin' ? '' : null
  })
}

const resetPresenceForm = () => {
  Object.keys(presenceForm).forEach(key => {
    presenceForm[key] = key.includes('notes') ? '' : null
  })
  editingPresence.value = null
}

// Utility functions
const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return format(new Date(datetime), 'dd/MM/yyyy HH:mm')
}

const formatTime = (time) => {
  if (!time) return '-'
  return format(new Date(`2000-01-01 ${time}`), 'HH:mm')
}

const getShiftType = (startTime) => {
  if (!startTime) return 'Unknown'
  const hour = new Date(`2000-01-01 ${startTime}`).getHours()
  
  if (hour >= 6 && hour < 14) return 'Morning'
  if (hour >= 14 && hour < 22) return 'Afternoon'
  return 'Night'
}

const getShiftTypeSeverity = (startTime) => {
  const type = getShiftType(startTime)
  switch (type) {
    case 'Morning': return 'info'
    case 'Afternoon': return 'warning'
    case 'Night': return 'secondary'
    default: return null
  }
}

const getStatusSeverity = (status) => {
  switch (status) {
    case 'present': return 'success'
    case 'late': return 'warning'
    case 'absent': return 'danger'
    case 'partial': return 'info'
    default: return null
  }
}
</script>
