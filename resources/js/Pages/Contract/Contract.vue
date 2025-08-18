<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Employee Contract Management</h1>
          <p class="text-sm text-gray-600 mt-1">Manage employee contract types and assignments</p>
        </div>
        <div class="flex gap-3">
          <Button 
            v-if="canCreate('employee_contracts')"
            @click="showCreateModal = true" 
            class="bg-primary-600 hover:bg-primary-700"
          >
            <i class="pi pi-plus mr-2"></i>
            Add Contract
          </Button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-file-edit text-2xl text-blue-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Contracts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.total_contracts }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-check-circle text-2xl text-green-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Contracts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.active_contracts }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-times-circle text-2xl text-red-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Inactive Contracts</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.inactive_contracts }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <i class="pi pi-users text-2xl text-purple-600"></i>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Assigned Employees</p>
            <p class="text-2xl font-bold text-gray-900">{{ getTotalAssignedEmployees() }}</p>
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
            placeholder="Search by contract name or description..."
            class="w-full"
            @input="debouncedSearch"
          />
        </div>
        <div class="flex gap-3">
          <Dropdown
            v-model="localFilters.type"
            :options="contractTypeOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Contract Type"
            class="w-40"
            showClear
            @change="applyFilters"
          />
          <Dropdown
            v-model="localFilters.is_active"
            :options="statusOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Status"
            class="w-32"
            showClear
            @change="applyFilters"
          />
          <InputNumber
            v-model="localFilters.salary_min"
            placeholder="Min Salary"
            class="w-40"
            mode="currency"
            currency="IDR"
            locale="id-ID"
            @input="applyFilters"
          />
          <InputNumber
            v-model="localFilters.salary_max"
            placeholder="Max Salary"
            class="w-40"
            mode="currency"
            currency="IDR"
            locale="id-ID"
            @input="applyFilters"
          />
          <Button @click="clearFilters" outlined>
            <i class="pi pi-times mr-2"></i>
            Clear
          </Button>
        </div>
      </div>
    </div>

    <!-- Contracts Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <DataTable
        :value="contracts.data"
        :loading="loading"
        stripedRows
        responsiveLayout="scroll"
        class="p-datatable-sm"
      >
        <Column field="name" header="Contract Name" sortable>
          <template #body="{ data }">
            <div>
              <div class="font-medium text-gray-900">{{ data.name }}</div>
              <div class="text-sm text-gray-500">{{ data.description }}</div>
            </div>
          </template>
        </Column>
        
        <Column field="type" header="Type" sortable>
          <template #body="{ data }">
            <Badge
              :value="getContractTypeLabel(data.type)"
              :severity="getContractTypeSeverity(data.type)"
              class="capitalize"
            />
          </template>
        </Column>

        <Column field="base_salary" header="Base Salary" sortable>
          <template #body="{ data }">
            <div class="text-sm">
              <div class="font-medium text-gray-900">
                {{ data.base_salary ? formatCurrency(data.base_salary) : 'Not set' }}
              </div>
            </div>
          </template>
        </Column>

        <Column field="benefits" header="Benefits" sortable>
          <template #body="{ data }">
            <div class="text-sm">
              <div v-if="data.benefits && Object.keys(data.benefits).length > 0">
                <div v-for="(value, key) in getTopBenefits(data.benefits)" :key="key" class="text-xs text-gray-600">
                  {{ formatBenefitKey(key) }}: {{ formatBenefitValue(value) }}
                </div>
                <div v-if="Object.keys(data.benefits).length > 3" class="text-xs text-blue-600 mt-1">
                  +{{ Object.keys(data.benefits).length - 3 }} more
                </div>
              </div>
              <div v-else class="text-gray-500 italic">No benefits</div>
            </div>
          </template>
        </Column>

        <Column field="employees_count" header="Employees" sortable>
          <template #body="{ data }">
            <div class="flex items-center">
              <span class="text-sm font-medium text-gray-900">{{ data.employees_count || 0 }}</span>
              <Button
                v-if="data.employees_count > 0"
                @click="viewContractEmployees(data)"
                size="small"
                text
                class="ml-2"
              >
                <i class="pi pi-users"></i>
              </Button>
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
        
        <Column header="Actions" class="w-56">
          <template #body="{ data }">
            <div class="flex gap-2">
              <Button
                @click="viewContract(data)"
                size="small"
                outlined
                title="View Details"
              >
                <i class="pi pi-eye"></i>
              </Button>
              <Button
                v-if="canEdit('employee_contracts')"
                @click="editContract(data)"
                size="small"
                outlined
                severity="warning"
                title="Edit Contract"
              >
                <i class="pi pi-pencil"></i>
              </Button>
              <Button
                v-if="canEdit('employee_contracts')"
                @click="toggleContractStatus(data)"
                size="small"
                outlined
                :severity="data.is_active ? 'danger' : 'success'"
                :title="data.is_active ? 'Deactivate' : 'Activate'"
              >
                <i :class="data.is_active ? 'pi pi-times' : 'pi pi-check'"></i>
              </Button>
              <Button
                v-if="canCreate('employee_contracts')"
                @click="duplicateContract(data)"
                size="small"
                outlined
                severity="info"
                title="Duplicate Contract"
              >
                <i class="pi pi-copy"></i>
              </Button>
              <Button
                v-if="canDelete('employee_contracts')"
                @click="deleteContract(data)"
                size="small"
                outlined
                severity="danger"
                title="Delete Contract"
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
          :rows="contracts.per_page"
          :totalRecords="contracts.total"
          :first="(contracts.current_page - 1) * contracts.per_page"
          @page="onPageChange"
          template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown"
          :rowsPerPageOptions="[10, 25, 50]"
        />
      </div>
    </div>

    <!-- Create/Edit Contract Modal -->
    <Dialog
      v-model:visible="showCreateModal"
      modal
      :header="editingContract ? 'Edit Contract' : 'Create Contract'"
      class="w-full max-w-4xl"
    >
      <div class="space-y-6">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Contract Name *</label>
            <InputText
              v-model="contractForm.name"
              placeholder="Enter contract name"
              class="w-full"
              :class="{ 'p-invalid': contractFormErrors.name }"
            />
            <small v-if="contractFormErrors.name" class="p-error">
              {{ contractFormErrors.name }}
            </small>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Contract Type *</label>
            <Dropdown
              v-model="contractForm.type"
              :options="contractTypeOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Select contract type"
              class="w-full"
              :class="{ 'p-invalid': contractFormErrors.type }"
            />
            <small v-if="contractFormErrors.type" class="p-error">
              {{ contractFormErrors.type }}
            </small>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <Textarea
            v-model="contractForm.description"
            placeholder="Enter contract description"
            class="w-full"
            rows="3"
          />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Base Salary</label>
            <InputNumber
              v-model="contractForm.base_salary"
              placeholder="Enter base salary"
              class="w-full"
              mode="currency"
              currency="IDR"
              locale="id-ID"
              :class="{ 'p-invalid': contractFormErrors.base_salary }"
            />
            <small v-if="contractFormErrors.base_salary" class="p-error">
              {{ contractFormErrors.base_salary }}
            </small>
          </div>

          <div class="flex items-center">
            <Checkbox
              v-model="contractForm.is_active"
              inputId="is_active"
              binary
            />
            <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">
              Active Contract
            </label>
          </div>
        </div>

        <!-- Benefits Section -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Benefits & Allowances</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Transport Allowance</label>
              <InputNumber
                v-model="contractForm.benefits.tunjangan_transport"
                placeholder="Transport allowance"
                class="w-full"
                mode="currency"
                currency="IDR"
                locale="id-ID"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Meal Allowance</label>
              <InputNumber
                v-model="contractForm.benefits.tunjangan_makan"
                placeholder="Meal allowance"
                class="w-full"
                mode="currency"
                currency="IDR"
                locale="id-ID"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Health Allowance</label>
              <InputNumber
                v-model="contractForm.benefits.tunjangan_kesehatan"
                placeholder="Health allowance"
                class="w-full"
                mode="currency"
                currency="IDR"
                locale="id-ID"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Communication Allowance</label>
              <InputNumber
                v-model="contractForm.benefits.tunjangan_komunikasi"
                placeholder="Communication allowance"
                class="w-full"
                mode="currency"
                currency="IDR"
                locale="id-ID"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Annual Leave Days</label>
              <InputNumber
                v-model="contractForm.benefits.cuti_tahunan"
                placeholder="Annual leave days"
                class="w-full"
                :min="0"
                :max="365"
              />
            </div>
          </div>

          <div class="mt-4 space-y-3">
            <div class="flex items-center">
              <Checkbox
                v-model="contractForm.benefits.asuransi_kesehatan"
                inputId="health_insurance"
                binary
              />
              <label for="health_insurance" class="ml-2 text-sm font-medium text-gray-700">
                Health Insurance
              </label>
            </div>

            <div class="flex items-center">
              <Checkbox
                v-model="contractForm.benefits.jamsostek"
                inputId="jamsostek"
                binary
              />
              <label for="jamsostek" class="ml-2 text-sm font-medium text-gray-700">
                BPJS Ketenagakerjaan
              </label>
            </div>

            <div class="flex items-center">
              <Checkbox
                v-model="contractForm.benefits.bonus_kinerja"
                inputId="performance_bonus"
                binary
              />
              <label for="performance_bonus" class="ml-2 text-sm font-medium text-gray-700">
                Performance Bonus
              </label>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2">
          <Button @click="showCreateModal = false" outlined>Cancel</Button>
          <Button @click="submitContract" :loading="submitting">
            {{ editingContract ? 'Update' : 'Create' }}
          </Button>
        </div>
      </template>
    </Dialog>

    <!-- Duplicate Contract Modal -->
    <Dialog
      v-model:visible="showDuplicateModal"
      modal
      header="Duplicate Contract"
      class="w-full max-w-md"
    >
      <div class="space-y-4">
        <p>Create a copy of "{{ duplicatingContract?.name }}" with a new name:</p>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">New Contract Name *</label>
          <InputText
            v-model="duplicateForm.name"
            placeholder="Enter new contract name"
            class="w-full"
            :class="{ 'p-invalid': duplicateFormErrors.name }"
          />
          <small v-if="duplicateFormErrors.name" class="p-error">
            {{ duplicateFormErrors.name }}
          </small>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2">
          <Button @click="showDuplicateModal = false" outlined>Cancel</Button>
          <Button @click="submitDuplicate" :loading="submitting">
            Duplicate
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
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePermissions } from '@/composables/usePermissions'

// PrimeVue Components
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Dropdown from 'primevue/dropdown'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Paginator from 'primevue/paginator'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'

// Props
const props = defineProps({
  contracts: Object,
  statistics: Object,
  filters: Object,
  contractTypes: Object
})

// Permissions
const { canView, canCreate, canEdit, canDelete } = usePermissions()

// Reactive state
const loading = ref(false)
const submitting = ref(false)
const showCreateModal = ref(false)
const showDuplicateModal = ref(false)
const editingContract = ref(null)
const duplicatingContract = ref(null)

// Filter state
const localFilters = reactive({
  search: props.filters.search || '',
  type: props.filters.type || null,
  is_active: props.filters.is_active !== undefined ? props.filters.is_active : null,
  salary_min: props.filters.salary_min || null,
  salary_max: props.filters.salary_max || null,
})

// Form states
const contractForm = reactive({
  name: '',
  description: '',
  type: '',
  base_salary: null,
  is_active: true,
  benefits: {
    tunjangan_transport: null,
    tunjangan_makan: null,
    tunjangan_kesehatan: null,
    tunjangan_komunikasi: null,
    asuransi_kesehatan: false,
    jamsostek: false,
    cuti_tahunan: null,
    bonus_kinerja: false
  }
})

const duplicateForm = reactive({
  name: ''
})

const contractFormErrors = ref({})
const duplicateFormErrors = ref({})

// Options
const contractTypeOptions = computed(() => {
  return Object.entries(props.contractTypes).map(([value, label]) => ({
    value,
    label
  }))
})

const statusOptions = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

// Methods
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

const applyFilters = () => {
  loading.value = true
  
  const filterData = {
    search: localFilters.search || undefined,
    type: localFilters.type || undefined,
    is_active: localFilters.is_active !== null ? localFilters.is_active : undefined,
    salary_min: localFilters.salary_min || undefined,
    salary_max: localFilters.salary_max || undefined,
  }

  router.get(route('employee-contracts.index'), filterData, {
    preserveState: true,
    onFinish: () => loading.value = false
  })
}

const clearFilters = () => {
  Object.keys(localFilters).forEach(key => {
    localFilters[key] = key === 'is_active' ? null : ''
  })
  
  router.get(route('employee-contracts.index'), {}, {
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

  router.get(route('employee-contracts.index'), filterData, {
    preserveState: true
  })
}

const viewContract = (contract) => {
  router.get(route('employee-contracts.show', contract.id))
}

const editContract = (contract) => {
  editingContract.value = contract
  contractForm.name = contract.name
  contractForm.description = contract.description || ''
  contractForm.type = contract.type
  contractForm.base_salary = contract.base_salary
  contractForm.is_active = contract.is_active
  
  // Set benefits
  if (contract.benefits) {
    Object.keys(contractForm.benefits).forEach(key => {
      contractForm.benefits[key] = contract.benefits[key] || (key.includes('tunjangan') || key === 'cuti_tahunan' ? null : false)
    })
  }
  
  showCreateModal.value = true
}

const deleteContract = async (contract) => {
  if (confirm(`Are you sure you want to delete the contract "${contract.name}"? This action cannot be undone.`)) {
    try {
      await router.delete(route('employee-contracts.destroy', contract.id))
    } catch (error) {
      console.error('Failed to delete contract:', error)
    }
  }
}

const toggleContractStatus = async (contract) => {
  const action = contract.is_active ? 'deactivate' : 'activate'
  if (confirm(`Are you sure you want to ${action} the contract "${contract.name}"?`)) {
    try {
      await router.post(route('employee-contracts.toggle-status', contract.id))
    } catch (error) {
      console.error('Failed to toggle contract status:', error)
    }
  }
}

const duplicateContract = (contract) => {
  duplicatingContract.value = contract
  duplicateForm.name = `${contract.name} (Copy)`
  showDuplicateModal.value = true
}

const viewContractEmployees = (contract) => {
  router.get(route('employee-contracts.show', contract.id) + '#employees')
}

const submitContract = async () => {
  contractFormErrors.value = {}
  submitting.value = true

  try {
    const url = editingContract.value 
      ? route('employee-contracts.update', editingContract.value.id)
      : route('employee-contracts.store')
    
    const method = editingContract.value ? 'put' : 'post'

    await router[method](url, contractForm, {
      onSuccess: () => {
        showCreateModal.value = false
        resetContractForm()
      },
      onError: (errors) => {
        contractFormErrors.value = errors
      },
      onFinish: () => submitting.value = false
    })
  } catch (error) {
    submitting.value = false
  }
}

const submitDuplicate = async () => {
  duplicateFormErrors.value = {}
  submitting.value = true

  try {
    await router.post(route('employee-contracts.duplicate', duplicatingContract.value.id), duplicateForm, {
      onSuccess: () => {
        showDuplicateModal.value = false
        resetDuplicateForm()
      },
      onError: (errors) => {
        duplicateFormErrors.value = errors
      },
      onFinish: () => submitting.value = false
    })
  } catch (error) {
    submitting.value = false
  }
}

const resetContractForm = () => {
  contractForm.name = ''
  contractForm.description = ''
  contractForm.type = ''
  contractForm.base_salary = null
  contractForm.is_active = true
  
  Object.keys(contractForm.benefits).forEach(key => {
    contractForm.benefits[key] = key.includes('tunjangan') || key === 'cuti_tahunan' ? null : false
  })
  
  editingContract.value = null
}

const resetDuplicateForm = () => {
  duplicateForm.name = ''
  duplicatingContract.value = null
}

// Utility functions
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const getContractTypeLabel = (type) => {
  return props.contractTypes[type] || type
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

const getTotalAssignedEmployees = () => {
  return Object.values(props.statistics.employees_by_type || {}).reduce((sum, count) => sum + count, 0)
}

const getTopBenefits = (benefits) => {
  if (!benefits) return {}
  
  const entries = Object.entries(benefits).slice(0, 3)
  return Object.fromEntries(entries)
}

const formatBenefitKey = (key) => {
  const keyMap = {
    tunjangan_transport: 'Transport',
    tunjangan_makan: 'Meal',
    tunjangan_kesehatan: 'Health',
    tunjangan_komunikasi: 'Communication',
    asuransi_kesehatan: 'Health Insurance',
    jamsostek: 'BPJS',
    cuti_tahunan: 'Annual Leave',
    bonus_kinerja: 'Performance Bonus'
  }
  
  return keyMap[key] || key
}

const formatBenefitValue = (value) => {
  if (typeof value === 'boolean') {
    return value ? 'Yes' : 'No'
  }
  
  if (typeof value === 'number') {
    return value.toString()
  }
  
  return value
}
</script>
