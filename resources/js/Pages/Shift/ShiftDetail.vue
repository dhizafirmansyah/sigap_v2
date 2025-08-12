<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <div class="flex items-center gap-3">
            <Button 
              @click="router.get(route('shifts.index'))"
              outlined 
              size="small"
            >
              <i class="pi pi-arrow-left mr-2"></i>
              Back
            </Button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ shift.name }}</h1>
              <p class="text-sm text-gray-600 mt-1">Shift details and employee assignments</p>
            </div>
          </div>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canEdit('shifts')"
            @click="editShift" 
            outlined
            severity="warning"
          >
            <i class="pi pi-pencil mr-2"></i>
            Edit Shift
          </Button>
          <Button 
            v-if="canCreate('shifts')"
            @click="showAssignModal = true" 
            class="bg-primary-600 hover:bg-primary-700"
          >
            <i class="pi pi-user-plus mr-2"></i>
            Assign Employee
          </Button>
        </div>
      </div>
    </div>

    <!-- Shift Information Card -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-2">Shift Type</label>
          <Badge 
            :value="getShiftType(shift.start_time)" 
            :severity="getShiftTypeSeverity(shift.start_time)"
            class="text-base"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-2">Time Range</label>
          <div class="flex items-center text-lg font-medium text-gray-900">
            <i class="pi pi-clock mr-2 text-gray-400"></i>
            {{ formatTime(shift.start_time) }} - {{ formatTime(shift.end_time) }}
          </div>
          <p class="text-sm text-gray-500 mt-1">{{ calculateDuration(shift.start_time, shift.end_time) }}</p>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-2">Status</label>
          <Badge 
            :value="shift.is_active ? 'Active' : 'Inactive'" 
            :severity="shift.is_active ? 'success' : 'danger'"
            class="text-base"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-2">Total Employees</label>
          <div class="flex items-center text-lg font-medium text-gray-900">
            <i class="pi pi-users mr-2 text-blue-500"></i>
            {{ shift.users?.length || 0 }}
          </div>
        </div>
      </div>
      
      <div v-if="shift.description" class="mt-6 pt-6 border-t border-gray-200">
        <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
        <p class="text-gray-900">{{ shift.description }}</p>
      </div>
    </div>

    <!-- Assigned Employees -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-900">Assigned Employees</h2>
          <div class="flex gap-3">
            <InputText
              v-model="searchEmployee"
              placeholder="Search employees..."
              class="w-64"
            />
            <Dropdown
              v-model="filterStatus"
              :options="assignmentStatusOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Status"
              class="w-32"
            />
          </div>
        </div>
      </div>
      
      <DataTable
        :value="filteredEmployees"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="user.name" header="Employee" sortable>
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
        
        <Column field="pivot.start_date" header="Start Date" sortable>
          <template #body="{ data }">
            <div class="flex items-center text-sm">
              <i class="pi pi-calendar mr-2 text-gray-400"></i>
              {{ formatDate(data.pivot.start_date) }}
            </div>
          </template>
        </Column>
        
        <Column field="pivot.end_date" header="End Date" sortable>
          <template #body="{ data }">
            <div class="flex items-center text-sm">
              <i class="pi pi-calendar mr-2 text-gray-400"></i>
              {{ data.pivot.end_date ? formatDate(data.pivot.end_date) : 'Ongoing' }}
            </div>
          </template>
        </Column>
        
        <Column field="pivot.is_active" header="Status" sortable>
          <template #body="{ data }">
            <Badge 
              :value="data.pivot.is_active ? 'Active' : 'Inactive'" 
              :severity="data.pivot.is_active ? 'success' : 'danger'"
            />
          </template>
        </Column>
        
        <Column field="pivot.notes" header="Notes">
          <template #body="{ data }">
            <span class="text-sm text-gray-600">
              {{ data.pivot.notes || '-' }}
            </span>
          </template>
        </Column>
        
        <Column header="Actions" class="w-24">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button
                v-if="canEdit('shifts')"
                @click="editAssignment(data)"
                size="small"
                outlined
                severity="warning"
                title="Edit Assignment"
              >
                <i class="pi pi-pencil"></i>
              </Button>
              <Button
                v-if="canDelete('shifts')"
                @click="removeEmployee(data)"
                size="small"
                outlined
                severity="danger"
                title="Remove from Shift"
              >
                <i class="pi pi-trash"></i>
              </Button>
            </div>
          </template>
        </Column>
      </DataTable>
      
      <div v-if="!filteredEmployees.length" class="p-12 text-center">
        <i class="pi pi-users text-4xl text-gray-300 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No employees assigned</h3>
        <p class="text-gray-500 mb-4">Start by assigning employees to this shift</p>
        <Button 
          v-if="canCreate('shifts')"
          @click="showAssignModal = true"
          class="bg-primary-600 hover:bg-primary-700"
        >
          <i class="pi pi-plus mr-2"></i>
          Assign Employee
        </Button>
      </div>
    </div>

    <!-- Assign Employee Modal -->
    <Dialog
      v-model:visible="showAssignModal"
      modal
      header="Assign Employees to Shift"
      class="w-full max-w-2xl"
    >
      <div class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Employees</label>
          <MultiSelect
            v-model="assignForm.user_ids"
            :options="availableUsers"
            optionLabel="name"
            optionValue="id"
            placeholder="Choose employees"
            class="w-full"
            :filter="true"
            filterPlaceholder="Search employees..."
          >
            <template #option="slotProps">
              <div class="flex items-center">
                <div class="w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center mr-2">
                  <span class="text-white text-xs">
                    {{ slotProps.option.name?.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <div class="font-medium">{{ slotProps.option.name }}</div>
                  <div class="text-sm text-gray-500">{{ slotProps.option.email }}</div>
                </div>
              </div>
            </template>
          </MultiSelect>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <Calendar
              v-model="assignForm.start_date"
              dateFormat="yy-mm-dd"
              placeholder="Select start date"
              class="w-full"
              :minDate="new Date()"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date (Optional)</label>
            <Calendar
              v-model="assignForm.end_date"
              dateFormat="yy-mm-dd"
              placeholder="Select end date"
              class="w-full"
              :minDate="assignForm.start_date"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
          <Textarea
            v-model="assignForm.notes"
            placeholder="Assignment notes..."
            class="w-full"
            rows="3"
          />
        </div>
      </div>
      
      <template #footer>
        <Button @click="showAssignModal = false" outlined>Cancel</Button>
        <Button @click="assignEmployees" :loading="saving">Assign</Button>
      </template>
    </Dialog>

    <!-- Edit Assignment Modal -->
    <Dialog
      v-model:visible="showEditModal"
      modal
      header="Edit Assignment"
      class="w-full max-w-md"
    >
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
          <div class="flex items-center p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center mr-3">
              <span class="text-white font-medium text-sm">
                {{ editingAssignment?.name?.charAt(0).toUpperCase() }}
              </span>
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ editingAssignment?.name }}</div>
              <div class="text-sm text-gray-500">{{ editingAssignment?.email }}</div>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <Calendar
              v-model="editForm.start_date"
              dateFormat="yy-mm-dd"
              placeholder="Select start date"
              class="w-full"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date (Optional)</label>
            <Calendar
              v-model="editForm.end_date"
              dateFormat="yy-mm-dd"
              placeholder="Select end date"
              class="w-full"
              :minDate="editForm.start_date"
            />
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <div class="flex items-center pt-2">
            <Checkbox
              v-model="editForm.is_active"
              inputId="edit_is_active"
              :binary="true"
            />
            <label for="edit_is_active" class="ml-2">Active</label>
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
          <Textarea
            v-model="editForm.notes"
            placeholder="Assignment notes..."
            class="w-full"
            rows="3"
          />
        </div>
      </div>
      
      <template #footer>
        <Button @click="showEditModal = false" outlined>Cancel</Button>
        <Button @click="updateAssignment" :loading="saving">Update</Button>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { usePermissions } from '@/composables/usePermissions'
import { format, parse } from 'date-fns'
import AppLayout from '@/Layouts/AppLayout.vue'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Calendar from 'primevue/calendar'
import MultiSelect from 'primevue/multiselect'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'
import Dropdown from 'primevue/dropdown'

// Props
const props = defineProps({
  shift: Object,
  availableUsers: Array
})

// Permissions
const { canView, canCreate, canEdit, canDelete } = usePermissions()

// Reactive data
const loading = ref(false)
const saving = ref(false)
const showAssignModal = ref(false)
const showEditModal = ref(false)
const editingAssignment = ref(null)
const searchEmployee = ref('')
const filterStatus = ref(null)

// Forms
const assignForm = reactive({
  user_ids: [],
  start_date: new Date(),
  end_date: null,
  notes: ''
})

const editForm = reactive({
  start_date: null,
  end_date: null,
  is_active: true,
  notes: ''
})

// Options
const assignmentStatusOptions = [
  { label: 'All Status', value: null },
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

// Computed
const filteredEmployees = computed(() => {
  let employees = props.shift.users || []
  
  // Filter by search
  if (searchEmployee.value) {
    const search = searchEmployee.value.toLowerCase()
    employees = employees.filter(emp => 
      emp.name.toLowerCase().includes(search) ||
      emp.email.toLowerCase().includes(search)
    )
  }
  
  // Filter by status
  if (filterStatus.value !== null) {
    employees = employees.filter(emp => emp.pivot.is_active === filterStatus.value)
  }
  
  return employees
})

// Helper functions
const formatTime = (timeString) => {
  if (!timeString) return '-'
  try {
    const parsed = parse(timeString, 'HH:mm:ss', new Date())
    return format(parsed, 'HH:mm')
  } catch (error) {
    return timeString
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    return format(new Date(dateString), 'dd/MM/yyyy')
  } catch (error) {
    return dateString
  }
}

const calculateDuration = (startTime, endTime) => {
  if (!startTime || !endTime) return '-'
  
  try {
    const [startHours, startMinutes] = startTime.split(':').map(Number)
    const [endHours, endMinutes] = endTime.split(':').map(Number)
    
    let start = new Date(2024, 0, 1, startHours, startMinutes)
    let end = new Date(2024, 0, 1, endHours, endMinutes)
    
    // Handle overnight shifts
    if (end < start) {
      end.setDate(end.getDate() + 1)
    }
    
    const diffHours = (end - start) / (1000 * 60 * 60)
    return `${diffHours} hours`
  } catch (error) {
    return '-'
  }
}

const getShiftType = (startTime) => {
  if (!startTime) return 'Unknown'
  
  try {
    const hour = parseInt(startTime.split(':')[0])
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
const editShift = () => {
  router.get(route('shifts.edit', props.shift.id))
}

const assignEmployees = async () => {
  saving.value = true
  
  try {
    const formData = {
      user_ids: assignForm.user_ids,
      start_date: format(assignForm.start_date, 'yyyy-MM-dd'),
      end_date: assignForm.end_date ? format(assignForm.end_date, 'yyyy-MM-dd') : null,
      notes: assignForm.notes
    }
    
    await router.post(route('shifts.assign-users', props.shift.id), formData, {
      onSuccess: () => {
        showAssignModal.value = false
        Object.assign(assignForm, {
          user_ids: [],
          start_date: new Date(),
          end_date: null,
          notes: ''
        })
      }
    })
  } catch (error) {
    console.error('Failed to assign employees:', error)
  } finally {
    saving.value = false
  }
}

const editAssignment = (employee) => {
  editingAssignment.value = employee
  Object.assign(editForm, {
    start_date: new Date(employee.pivot.start_date),
    end_date: employee.pivot.end_date ? new Date(employee.pivot.end_date) : null,
    is_active: employee.pivot.is_active,
    notes: employee.pivot.notes || ''
  })
  showEditModal.value = true
}

const updateAssignment = async () => {
  saving.value = true
  
  try {
    const formData = {
      start_date: format(editForm.start_date, 'yyyy-MM-dd'),
      end_date: editForm.end_date ? format(editForm.end_date, 'yyyy-MM-dd') : null,
      is_active: editForm.is_active,
      notes: editForm.notes
    }
    
    await router.patch(route('shifts.update-assignment', {
      shift: props.shift.id,
      user: editingAssignment.value.id
    }), formData, {
      onSuccess: () => {
        showEditModal.value = false
        editingAssignment.value = null
      }
    })
  } catch (error) {
    console.error('Failed to update assignment:', error)
  } finally {
    saving.value = false
  }
}

const removeEmployee = async (employee) => {
  if (confirm(`Are you sure you want to remove ${employee.name} from this shift?`)) {
    try {
      await router.delete(route('shifts.remove-user', {
        shift: props.shift.id,
        user: employee.id
      }))
    } catch (error) {
      console.error('Failed to remove employee:', error)
    }
  }
}

// Lifecycle
onMounted(() => {
  // Any initialization logic
})
</script>
