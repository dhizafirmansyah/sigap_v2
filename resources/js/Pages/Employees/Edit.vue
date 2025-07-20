<template>
    <Head title="Edit Employee" />
    
    <AppLayout 
        title="Edit Employee" 
        :subtitle="employee ? `Edit informasi karyawan ${employee.name}` : 'Loading...'"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
                    outlined
                    @click="router.visit('/employees')"
                />
            </div>
        </template>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-16">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Loading Employee Data...</h3>
                <p class="text-gray-500">Please wait while we fetch the employee information</p>
            </div>
        </div>

        <!-- Form -->
        <Card v-else-if="employee">
            <template #content>
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Personal Information Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-user mr-2"></i>
                            Informasi Pribadi
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="col-span-1">
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    ID Karyawan <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="employee_id"
                                    v-model="form.employee_id"
                                    :class="{'border-red-500': errors.employee_id}"
                                    placeholder="Contoh: EMP001"
                                    class="w-full"
                                />
                                <small v-if="errors.employee_id" class="text-red-500">{{ errors.employee_id }}</small>
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="name"
                                    v-model="form.name"
                                    :class="{'border-red-500': errors.name}"
                                    placeholder="Masukkan nama lengkap"
                                    class="w-full"
                                />
                                <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email
                                </label>
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    :class="{'border-red-500': errors.email}"
                                    placeholder="nama@perusahaan.com"
                                    type="email"
                                    class="w-full"
                                />
                                <small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon
                                </label>
                                <InputText
                                    id="phone"
                                    v-model="form.phone"
                                    :class="{'border-red-500': errors.phone}"
                                    placeholder="08123456789"
                                    class="w-full"
                                />
                                <small v-if="errors.phone" class="text-red-500">{{ errors.phone }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jenis Kelamin
                                </label>
                                <Dropdown
                                    id="gender"
                                    v-model="form.gender"
                                    :options="genderOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih jenis kelamin"
                                    :class="{'border-red-500': errors.gender}"
                                    class="w-full"
                                />
                                <small v-if="errors.gender" class="text-red-500">{{ errors.gender }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Lahir
                                </label>
                                <Calendar
                                    id="birth_date"
                                    v-model="form.birth_date"
                                    :class="{'border-red-500': errors.birth_date}"
                                    placeholder="Pilih tanggal lahir"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full"
                                />
                                <small v-if="errors.birth_date" class="text-red-500">{{ errors.birth_date }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status Pernikahan
                                </label>
                                <Dropdown
                                    id="marital_status"
                                    v-model="form.marital_status"
                                    :options="maritalStatusOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih status pernikahan"
                                    :class="{'border-red-500': errors.marital_status}"
                                    class="w-full"
                                />
                                <small v-if="errors.marital_status" class="text-red-500">{{ errors.marital_status }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="education" class="block text-sm font-medium text-gray-700 mb-2">
                                    Pendidikan
                                </label>
                                <Dropdown
                                    id="education"
                                    v-model="form.education"
                                    :options="educationOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih tingkat pendidikan"
                                    :class="{'border-red-500': errors.education}"
                                    class="w-full"
                                />
                                <small v-if="errors.education" class="text-red-500">{{ errors.education }}</small>
                            </div>

                            <div class="col-span-1 md:col-span-3">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Alamat
                                </label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    :class="{'border-red-500': errors.address}"
                                    placeholder="Masukkan alamat lengkap"
                                    rows="3"
                                    class="w-full"
                                />
                                <small v-if="errors.address" class="text-red-500">{{ errors.address }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-briefcase mr-2"></i>
                            Informasi Pekerjaan
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="col-span-1">
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jabatan <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="position"
                                    v-model="form.position"
                                    :class="{'border-red-500': errors.position}"
                                    placeholder="Contoh: Production Manager"
                                    class="w-full"
                                />
                                <small v-if="errors.position" class="text-red-500">{{ errors.position }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                    Departemen <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="department"
                                    v-model="form.department"
                                    :class="{'border-red-500': errors.department}"
                                    placeholder="Contoh: Production"
                                    class="w-full"
                                />
                                <small v-if="errors.department" class="text-red-500">{{ errors.department }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Lokasi Kerja <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="location_id"
                                    v-model="form.location_id"
                                    :options="locations"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Pilih lokasi kerja"
                                    :class="{'border-red-500': errors.location_id}"
                                    class="w-full"
                                />
                                <small v-if="errors.location_id" class="text-red-500">{{ errors.location_id }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="pkwt_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Jenis PKWT
                                </label>
                                <Dropdown
                                    id="pkwt_id"
                                    v-model="form.pkwt_id"
                                    :options="pkwts"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Pilih jenis PKWT"
                                    :class="{'border-red-500': errors.pkwt_id}"
                                    class="w-full"
                                />
                                <small v-if="errors.pkwt_id" class="text-red-500">{{ errors.pkwt_id }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Masuk <span class="text-red-500">*</span>
                                </label>
                                <Calendar
                                    id="hire_date"
                                    v-model="form.hire_date"
                                    :class="{'border-red-500': errors.hire_date}"
                                    placeholder="Pilih tanggal masuk"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full"
                                />
                                <small v-if="errors.hire_date" class="text-red-500">{{ errors.hire_date }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="salary" class="block text-sm font-medium text-gray-700 mb-2">
                                    Gaji Pokok
                                </label>
                                <InputNumber
                                    id="salary"
                                    v-model="form.salary"
                                    :class="{'border-red-500': errors.salary}"
                                    placeholder="0"
                                    mode="currency"
                                    currency="IDR"
                                    locale="id-ID"
                                    class="w-full"
                                />
                                <small v-if="errors.salary" class="text-red-500">{{ errors.salary }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="contract_start" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mulai Kontrak
                                </label>
                                <Calendar
                                    id="contract_start"
                                    v-model="form.contract_start"
                                    :class="{'border-red-500': errors.contract_start}"
                                    placeholder="Pilih tanggal mulai kontrak"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full"
                                />
                                <small v-if="errors.contract_start" class="text-red-500">{{ errors.contract_start }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="contract_end" class="block text-sm font-medium text-gray-700 mb-2">
                                    Akhir Kontrak
                                </label>
                                <Calendar
                                    id="contract_end"
                                    v-model="form.contract_end"
                                    :class="{'border-red-500': errors.contract_end}"
                                    placeholder="Pilih tanggal akhir kontrak"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full"
                                />
                                <small v-if="errors.contract_end" class="text-red-500">{{ errors.contract_end }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status Karyawan <span class="text-red-500">*</span>
                                </label>
                                <Dropdown
                                    id="status"
                                    v-model="form.status"
                                    :options="statusOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih status karyawan"
                                    :class="{'border-red-500': errors.status}"
                                    class="w-full"
                                />
                                <small v-if="errors.status" class="text-red-500">{{ errors.status }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-phone mr-2"></i>
                            Kontak Darurat
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="col-span-1">
                                <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nama Kontak Darurat
                                </label>
                                <InputText
                                    id="emergency_contact_name"
                                    v-model="form.emergency_contact_name"
                                    :class="{'border-red-500': errors.emergency_contact_name}"
                                    placeholder="Nama kontak darurat"
                                    class="w-full"
                                />
                                <small v-if="errors.emergency_contact_name" class="text-red-500">{{ errors.emergency_contact_name }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Telepon Darurat
                                </label>
                                <InputText
                                    id="emergency_contact_phone"
                                    v-model="form.emergency_contact_phone"
                                    :class="{'border-red-500': errors.emergency_contact_phone}"
                                    placeholder="08123456789"
                                    class="w-full"
                                />
                                <small v-if="errors.emergency_contact_phone" class="text-red-500">{{ errors.emergency_contact_phone }}</small>
                            </div>

                            <div class="col-span-1">
                                <label for="emergency_contact_relation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Hubungan
                                </label>
                                <InputText
                                    id="emergency_contact_relation"
                                    v-model="form.emergency_contact_relation"
                                    :class="{'border-red-500': errors.emergency_contact_relation}"
                                    placeholder="Contoh: Istri, Suami, Orang Tua"
                                    class="w-full"
                                />
                                <small v-if="errors.emergency_contact_relation" class="text-red-500">{{ errors.emergency_contact_relation }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div class="pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-file-edit mr-2"></i>
                            Catatan Tambahan
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div class="col-span-1">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Catatan
                                </label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    :class="{'border-red-500': errors.notes}"
                                    placeholder="Catatan tambahan mengenai karyawan..."
                                    rows="4"
                                    class="w-full"
                                />
                                <small v-if="errors.notes" class="text-red-500">{{ errors.notes }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <Button 
                            type="button"
                            label="Cancel" 
                            severity="secondary"
                            outlined
                            @click="router.visit('/employees')"
                        />
                        <Button 
                            type="submit"
                            label="Update Employee" 
                            icon="pi pi-save"
                            :loading="processing"
                            :disabled="processing"
                        />
                    </div>
                </form>
            </template>
        </Card>

        <!-- No Data State -->
        <div v-else class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-red-800 mb-2">No Employee Data Found</h3>
            <div class="text-sm text-red-700 mb-4">
                <p>Unable to load employee data. This could be due to:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Invalid employee ID</li>
                    <li>Network connection issues</li>
                    <li>Employee not found in database</li>
                </ul>
            </div>
            <div class="flex gap-3">
                <Button 
                    label="Retry" 
                    icon="pi pi-refresh" 
                    @click="loadEmployee(getEmployeeId())"
                    :loading="isLoading"
                />
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
                    outlined
                    @click="router.visit('/employees')"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import { employeeApi } from '@/utils/api'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Dropdown from 'primevue/dropdown'
import Calendar from 'primevue/calendar'

const props = defineProps({
    employee: {
        type: Object,
        default: () => null
    },
    locations: Array,
    pkwts: Array,
    errors: Object
})

const page = usePage()
const toast = useToast()

// Loading states
const isLoading = ref(true)
const employee = ref(null)

// Get employee ID from URL
const getEmployeeId = () => {
    const path = window.location.pathname
    const matches = path.match(/\/employees\/(\d+)\/edit/)
    return matches ? parseInt(matches[1]) : null
}

// Load employee data via fetch API
const loadEmployee = async (employeeId) => {
    if (!employeeId) return
    
    isLoading.value = true
    try {
        const response = await employeeApi.getEmployee(employeeId)
        // Handle response structure (API returns data.data)
        employee.value = response.data?.data || response.data
        
        // Update form with loaded data
        updateFormWithEmployeeData(employee.value)
        
        toast.add({
            severity: 'success',
            summary: 'Data Loaded',
            detail: 'Employee data loaded successfully',
            life: 2000
        })
    } catch (error) {
        console.error('Failed to load employee:', error)
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to load employee data',
            life: 5000
        })
        
        // Fallback to props data if available
        if (props.employee) {
            employee.value = props.employee
            updateFormWithEmployeeData(employee.value)
        }
    } finally {
        isLoading.value = false
    }
}

// Form data - initialize with empty values
const form = reactive({
    employee_id: '',
    name: '',
    email: '',
    phone: '',
    gender: '',
    birth_date: null,
    address: '',
    marital_status: '',
    education: '',
    pkwt_id: null,
    location_id: null,
    hire_date: null,
    contract_start: null,
    contract_end: null,
    salary: null,
    position: '',
    department: '',
    status: '',
    notes: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    emergency_contact_relation: ''
})

// Update form with employee data
const updateFormWithEmployeeData = (employeeData) => {
    if (!employeeData) return
    
    form.employee_id = employeeData.employee_id || ''
    form.name = employeeData.name || ''
    form.email = employeeData.email || ''
    form.phone = employeeData.phone || ''
    form.gender = employeeData.gender || ''
    form.birth_date = employeeData.birth_date ? new Date(employeeData.birth_date) : null
    form.address = employeeData.address || ''
    form.marital_status = employeeData.marital_status || ''
    form.education = employeeData.education || ''
    form.pkwt_id = employeeData.pkwt_id || null
    form.location_id = employeeData.location_id || null
    form.hire_date = employeeData.hire_date ? new Date(employeeData.hire_date) : null
    form.contract_start = employeeData.contract_start ? new Date(employeeData.contract_start) : null
    form.contract_end = employeeData.contract_end ? new Date(employeeData.contract_end) : null
    form.salary = employeeData.salary || null
    form.position = employeeData.position || ''
    form.department = employeeData.department || ''
    form.status = employeeData.status || ''
    form.notes = employeeData.notes || ''
    form.emergency_contact_name = employeeData.emergency_contact_name || ''
    form.emergency_contact_phone = employeeData.emergency_contact_phone || ''
    form.emergency_contact_relation = employeeData.emergency_contact_relation || ''
}

const processing = ref(false)
const errors = ref({})

// Options for dropdowns
const genderOptions = [
    { label: 'Laki-laki', value: 'male' },
    { label: 'Perempuan', value: 'female' },
    { label: 'Lainnya', value: 'other' }
]

const maritalStatusOptions = [
    { label: 'Belum Menikah', value: 'single' },
    { label: 'Menikah', value: 'married' },
    { label: 'Cerai', value: 'divorced' },
    { label: 'Lainnya', value: 'other' }
]

const educationOptions = [
    { label: 'SD', value: 'sd' },
    { label: 'SMP', value: 'smp' },
    { label: 'SMA', value: 'sma' },
    { label: 'Diploma', value: 'diploma' },
    { label: 'Sarjana', value: 'sarjana' },
    { label: 'Lainnya', value: 'other' }
]

const statusOptions = [
    { label: 'Aktif', value: 'active' },
    { label: 'Tidak Aktif', value: 'inactive' },
    { label: 'Resign', value: 'resigned' },
    { label: 'Terminate', value: 'terminated' }
]

// Submit form dengan API helper
const submit = async () => {
    if (processing.value) return
    
    processing.value = true
    errors.value = {}
    
    try {
        const { data } = await employeeApi.updateEmployee(employee.value.id, form)
        
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Employee data updated successfully',
            life: 3000
        })
        
        // Redirect to employee detail or list
        setTimeout(() => {
            window.location.href = `/employees/${employee.value.id}`
        }, 1500)
        
    } catch (error) {
        console.error('Submit error:', error)
        
        // Handle validation errors
        if (error.status === 422 && error.data?.errors) {
            errors.value = error.data.errors
            toast.add({
                severity: 'warn',
                summary: 'Validation Error',
                detail: 'Please check the form fields and try again',
                life: 5000
            })
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.message || 'Failed to update employee data',
                life: 3000
            })
        }
    } finally {
        processing.value = false
    }
}

// Initialize employee data on mount
onMounted(async () => {
    const employeeId = getEmployeeId()
    
    if (employeeId) {
        // Load data via fetch API
        await loadEmployee(employeeId)
    } else if (props.employee) {
        // Fallback to props if no ID in URL
        employee.value = props.employee
        updateFormWithEmployeeData(employee.value)
        isLoading.value = false
    } else {
        isLoading.value = false
    }
})
</script>
