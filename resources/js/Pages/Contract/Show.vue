<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ contract.name }}</h1>
          <p class="text-sm text-gray-600 mt-1">
            {{ getContractTypeLabel(contract.type) }} Contract Details
          </p>
        </div>
        <div class="flex gap-3">
          <Button @click="$inertia.visit(route('employee-contracts.index'))" outlined>
            <i class="pi pi-arrow-left mr-2"></i>
            Back to List
          </Button>
          <Button 
            v-if="canEdit('employee_contracts')"
            @click="editContract"
            severity="warning"
          >
            <i class="pi pi-pencil mr-2"></i>
            Edit
          </Button>
          <Button 
            v-if="canEdit('employee_contracts')"
            @click="toggleContractStatus"
            :severity="contract.is_active ? 'danger' : 'success'"
          >
            <i :class="contract.is_active ? 'pi pi-times' : 'pi pi-check'" class="mr-2"></i>
            {{ contract.is_active ? 'Deactivate' : 'Activate' }}
          </Button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Contract Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Contract Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Basic Details</h4>
              <div class="space-y-3">
                <div>
                  <span class="text-sm font-medium text-gray-700">Contract Name:</span>
                  <p class="text-sm text-gray-900">{{ contract.name }}</p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-700">Type:</span>
                  <div class="mt-1">
                    <Badge
                      :value="getContractTypeLabel(contract.type)"
                      :severity="getContractTypeSeverity(contract.type)"
                      class="capitalize"
                    />
                  </div>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-700">Status:</span>
                  <div class="mt-1">
                    <Badge
                      :value="contract.is_active ? 'Active' : 'Inactive'"
                      :severity="contract.is_active ? 'success' : 'danger'"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Salary Information</h4>
              <div class="space-y-3">
                <div>
                  <span class="text-sm font-medium text-gray-700">Base Salary:</span>
                  <p class="text-sm text-gray-900">
                    {{ contract.base_salary ? formatCurrency(contract.base_salary) : 'Not specified' }}
                  </p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-700">Created:</span>
                  <p class="text-sm text-gray-900">{{ formatDateTime(contract.created_at) }}</p>
                </div>
                <div v-if="contract.updated_at !== contract.created_at">
                  <span class="text-sm font-medium text-gray-700">Last Updated:</span>
                  <p class="text-sm text-gray-900">{{ formatDateTime(contract.updated_at) }}</p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="contract.description" class="mt-6">
            <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
            <p class="text-sm text-gray-700">{{ contract.description }}</p>
          </div>
        </div>

        <!-- Benefits & Allowances -->
        <div v-if="contract.benefits && Object.keys(contract.benefits).length > 0" class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Benefits & Allowances</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Monetary Benefits -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Allowances</h4>
              <div class="space-y-3">
                <div v-if="contract.benefits.tunjangan_transport">
                  <span class="text-sm font-medium text-gray-700">Transport Allowance:</span>
                  <p class="text-sm text-gray-900">{{ formatCurrency(contract.benefits.tunjangan_transport) }}</p>
                </div>
                <div v-if="contract.benefits.tunjangan_makan">
                  <span class="text-sm font-medium text-gray-700">Meal Allowance:</span>
                  <p class="text-sm text-gray-900">{{ formatCurrency(contract.benefits.tunjangan_makan) }}</p>
                </div>
                <div v-if="contract.benefits.tunjangan_kesehatan">
                  <span class="text-sm font-medium text-gray-700">Health Allowance:</span>
                  <p class="text-sm text-gray-900">{{ formatCurrency(contract.benefits.tunjangan_kesehatan) }}</p>
                </div>
                <div v-if="contract.benefits.tunjangan_komunikasi">
                  <span class="text-sm font-medium text-gray-700">Communication Allowance:</span>
                  <p class="text-sm text-gray-900">{{ formatCurrency(contract.benefits.tunjangan_komunikasi) }}</p>
                </div>
              </div>
            </div>

            <!-- Other Benefits -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Additional Benefits</h4>
              <div class="space-y-3">
                <div v-if="contract.benefits.cuti_tahunan">
                  <span class="text-sm font-medium text-gray-700">Annual Leave:</span>
                  <p class="text-sm text-gray-900">{{ contract.benefits.cuti_tahunan }} days per year</p>
                </div>
                
                <div class="space-y-2">
                  <div v-if="contract.benefits.asuransi_kesehatan" class="flex items-center">
                    <i class="pi pi-check text-green-600 mr-2"></i>
                    <span class="text-sm text-gray-700">Health Insurance</span>
                  </div>
                  <div v-if="contract.benefits.jamsostek" class="flex items-center">
                    <i class="pi pi-check text-green-600 mr-2"></i>
                    <span class="text-sm text-gray-700">BPJS Ketenagakerjaan</span>
                  </div>
                  <div v-if="contract.benefits.bonus_kinerja" class="flex items-center">
                    <i class="pi pi-check text-green-600 mr-2"></i>
                    <span class="text-sm text-gray-700">Performance Bonus</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assigned Employees -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Assigned Employees</h3>
            <div class="flex gap-2">
              <Button 
                v-if="canEdit('employee_contracts')"
                @click="showAssignModal = true"
                size="small"
                outlined
              >
                <i class="pi pi-plus mr-2"></i>
                Assign Employees
              </Button>
            </div>
          </div>

          <div v-if="contract.employees && contract.employees.length > 0">
            <!-- Search for employees -->
            <div class="mb-4">
              <InputText
                v-model="employeeSearch"
                placeholder="Search employees..."
                class="w-full"
              />
            </div>

            <!-- Employees table -->
            <DataTable
              :value="filteredEmployees"
              stripedRows
              responsiveLayout="scroll"
              class="p-datatable-sm"
            >
              <Column field="employee_id" header="Employee ID" sortable>
                <template #body="{ data }">
                  <span class="font-medium text-gray-900">{{ data.employee_id }}</span>
                </template>
              </Column>

              <Column field="name" header="Name" sortable>
                <template #body="{ data }">
                  <span class="text-gray-900">{{ data.name }}</span>
                </template>
              </Column>

              <Column field="position" header="Position" sortable>
                <template #body="{ data }">
                  <span class="text-gray-700">{{ data.position }}</span>
                </template>
              </Column>

              <Column field="department" header="Department" sortable>
                <template #body="{ data }">
                  <span class="text-gray-700">{{ data.department }}</span>
                </template>
              </Column>

              <Column field="status" header="Status" sortable>
                <template #body="{ data }">
                  <Badge
                    :value="data.status"
                    :severity="getEmployeeStatusSeverity(data.status)"
                    class="capitalize"
                  />
                </template>
              </Column>

              <Column header="Actions" class="w-24">
                <template #body="{ data }">
                  <Button
                    v-if="canEdit('employee_contracts')"
                    @click="removeEmployee(data)"
                    size="small"
                    outlined
                    severity="danger"
                    title="Remove from contract"
                  >
                    <i class="pi pi-times"></i>
                  </Button>
                </template>
              </Column>
            </DataTable>
          </div>

          <div v-else class="text-center py-8">
            <i class="pi pi-users text-4xl text-gray-300 mb-3"></i>
            <h4 class="text-lg font-medium text-gray-500 mb-2">No Employees Assigned</h4>
            <p class="text-sm text-gray-400 mb-4">This contract has no employees assigned to it yet.</p>
            <Button 
              v-if="canEdit('employee_contracts')"
              @click="showAssignModal = true"
              outlined
            >
              <i class="pi pi-plus mr-2"></i>
              Assign Employees
            </Button>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Contract Statistics -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Contract Statistics</h3>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Total Employees</span>
              <span class="text-lg font-bold text-gray-900">{{ report.total_employees }}</span>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Active Employees</span>
              <span class="text-lg font-bold text-green-600">{{ report.active_employees }}</span>
            </div>

            <div v-if="report.department_distribution && Object.keys(report.department_distribution).length > 0" class="pt-4 border-t border-gray-200">
              <h4 class="text-sm font-medium text-gray-500 mb-3">By Department</h4>
              <div class="space-y-2">
                <div v-for="(count, department) in report.department_distribution" :key="department" class="flex justify-between">
                  <span class="text-sm text-gray-600">{{ department }}</span>
                  <span class="text-sm font-medium text-gray-900">{{ count }}</span>
                </div>
              </div>
            </div>

            <div v-if="report.status_distribution && Object.keys(report.status_distribution).length > 0" class="pt-4 border-t border-gray-200">
              <h4 class="text-sm font-medium text-gray-500 mb-3">By Status</h4>
              <div class="space-y-2">
                <div v-for="(count, status) in report.status_distribution" :key="status" class="flex justify-between">
                  <span class="text-sm text-gray-600 capitalize">{{ status }}</span>
                  <span class="text-sm font-medium text-gray-900">{{ count }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          
          <div class="space-y-3">
            <Button 
              v-if="canEdit('employee_contracts')"
              @click="editContract"
              class="w-full"
              severity="warning"
              outlined
            >
              <i class="pi pi-pencil mr-2"></i>
              Edit Contract
            </Button>

            <Button 
              v-if="canEdit('employee_contracts')"
              @click="toggleContractStatus"
              class="w-full"
              :severity="contract.is_active ? 'danger' : 'success'"
              outlined
            >
              <i :class="contract.is_active ? 'pi pi-times' : 'pi pi-check'" class="mr-2"></i>
              {{ contract.is_active ? 'Deactivate' : 'Activate' }}
            </Button>

            <Button 
              @click="exportReport"
              class="w-full"
              outlined
            >
              <i class="pi pi-download mr-2"></i>
              Export Report
            </Button>

            <Button 
              v-if="canCreate('employee_contracts')"
              @click="duplicateContract"
              class="w-full"
              outlined
              severity="info"
            >
              <i class="pi pi-copy mr-2"></i>
              Duplicate Contract
            </Button>

            <Button 
              v-if="canDelete('employee_contracts')"
              @click="deleteContract"
              class="w-full"
              severity="danger"
              outlined
            >
              <i class="pi pi-trash mr-2"></i>
              Delete Contract
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Assign Employees Modal -->
    <Dialog
      v-model:visible="showAssignModal"
      modal
      header="Assign Employees to Contract"
      class="w-full max-w-4xl"
    >
      <div class="space-y-4">
        <div>
          <InputText
            v-model="availableEmployeeSearch"
            placeholder="Search available employees..."
            class="w-full"
            @input="searchAvailableEmployees"
          />
        </div>

        <div class="max-h-96 overflow-y-auto">
          <div v-if="availableEmployees.length > 0" class="space-y-2">
            <div
              v-for="employee in availableEmployees"
              :key="employee.id"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <Checkbox
                v-model="selectedEmployees"
                :value="employee.id"
                :inputId="`employee-${employee.id}`"
              />
              <label :for="`employee-${employee.id}`" class="ml-3 flex-1 cursor-pointer">
                <div class="font-medium text-gray-900">{{ employee.name }}</div>
                <div class="text-sm text-gray-500">
                  {{ employee.employee_id }} • {{ employee.position }} • {{ employee.department }}
                </div>
              </label>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            {{ availableEmployeeSearch ? 'No employees found' : 'No available employees' }}
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2">
          <Button @click="showAssignModal = false" outlined>Cancel</Button>
          <Button 
            @click="assignSelectedEmployees" 
            :loading="submitting"
            :disabled="selectedEmployees.length === 0"
          >
            Assign {{ selectedEmployees.length }} Employee(s)
          </Button>
        </div>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { debounce } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePermissions } from '@/composables/usePermissions'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Badge from 'primevue/badge'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Checkbox from 'primevue/checkbox'

// Props
const props = defineProps({
  contract: Object,
  report: Object
})

// Permissions
const { canEdit, canCreate, canDelete } = usePermissions()

// State
const submitting = ref(false)
const showAssignModal = ref(false)
const employeeSearch = ref('')
const availableEmployeeSearch = ref('')
const availableEmployees = ref([])
const selectedEmployees = ref([])

// Methods
const editContract = () => {
  router.get(route('employee-contracts.edit', props.contract.id))
}

const toggleContractStatus = () => {
  const action = props.contract.is_active ? 'deactivate' : 'activate'
  if (confirm(`Are you sure you want to ${action} this contract?`)) {
    router.post(route('employee-contracts.toggle-status', props.contract.id))
  }
}

const deleteContract = () => {
  if (confirm(`Are you sure you want to delete the contract "${props.contract.name}"? This action cannot be undone.`)) {
    router.delete(route('employee-contracts.destroy', props.contract.id))
  }
}

const duplicateContract = () => {
  const newName = prompt('Enter name for the duplicated contract:', `${props.contract.name} (Copy)`)
  if (newName) {
    router.post(route('employee-contracts.duplicate', props.contract.id), { name: newName })
  }
}

const exportReport = () => {
  router.get(route('employee-contracts.export-report', props.contract.id))
}

const removeEmployee = (employee) => {
  if (confirm(`Remove ${employee.name} from this contract?`)) {
    router.post(route('employee-contracts.remove-employees'), {
      employee_ids: [employee.id]
    })
  }
}

const searchAvailableEmployees = debounce(async () => {
  try {
    const response = await fetch(route('employee-contracts.available-employees') + `?search=${availableEmployeeSearch.value}&contract_id=${props.contract.id}`)
    const data = await response.json()
    availableEmployees.value = data
  } catch (error) {
    console.error('Failed to search employees:', error)
  }
}, 300)

const assignSelectedEmployees = async () => {
  if (selectedEmployees.value.length === 0) return

  submitting.value = true
  try {
    await router.post(route('employee-contracts.assign-employees', props.contract.id), {
      employee_ids: selectedEmployees.value
    }, {
      onSuccess: () => {
        showAssignModal.value = false
        selectedEmployees.value = []
        availableEmployeeSearch.value = ''
        availableEmployees.value = []
      },
      onFinish: () => submitting.value = false
    })
  } catch (error) {
    submitting.value = false
  }
}

// Computed
const filteredEmployees = computed(() => {
  if (!employeeSearch.value) return props.contract.employees || []
  
  const search = employeeSearch.value.toLowerCase()
  return (props.contract.employees || []).filter(employee =>
    employee.name.toLowerCase().includes(search) ||
    employee.employee_id.toLowerCase().includes(search) ||
    employee.position.toLowerCase().includes(search) ||
    employee.department.toLowerCase().includes(search)
  )
})

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return format(new Date(datetime), 'dd/MM/yyyy HH:mm')
}

const getContractTypeLabel = (type) => {
  const types = {
    permanent: 'Permanent',
    contract: 'Contract',
    probation: 'Probation',
    internship: 'Internship'
  }
  return types[type] || type
}

const getContractTypeSeverity = (type) => {
  switch (type) {
    case 'permanent': return 'success'
    case 'contract': return 'info'
    case 'probation': return 'warning'
    case 'internship': return 'secondary'
    default: return null
  }
}

const getEmployeeStatusSeverity = (status) => {
  switch (status) {
    case 'active': return 'success'
    case 'inactive': return 'warning'
    case 'terminated': return 'danger'
    case 'resigned': return 'secondary'
    default: return null
  }
}

// Load available employees when modal opens
const loadAvailableEmployees = async () => {
  if (!showAssignModal.value) return
  
  try {
    const response = await fetch(route('employee-contracts.available-employees') + `?contract_id=${props.contract.id}`)
    const data = await response.json()
    availableEmployees.value = data
  } catch (error) {
    console.error('Failed to load available employees:', error)
  }
}

// Watch for modal open
const watchModal = () => {
  if (showAssignModal.value) {
    loadAvailableEmployees()
  }
}

onMounted(() => {
  // Set up watchers
  const modalWatcher = computed(() => showAssignModal.value)
  modalWatcher.effect = watchModal
})
</script>
