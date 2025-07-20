<template>
    <Head title="Employee Details" />
    
    <AppLayout 
        title="Employee Details" 
        :subtitle="employee ? `Complete information for ${employee.name}` : 'Loading...'"
        :user="$page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    label="Edit" 
                    icon="pi pi-pencil" 
                    severity="warning"
                    @click="editEmployee"
                    :disabled="!employee || isLoading || isInitialLoading"
                    :loading="isLoading"
                />
                <Button 
                    label="Refresh" 
                    icon="pi pi-refresh" 
                    severity="info"
                    outlined
                    @click="refreshEmployee"
                    :disabled="isLoading || isInitialLoading"
                    :loading="isLoading"
                />
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
                    outlined
                    @click="backToIndex"
                />
            </div>
        </template>

        <div v-if="employee && !isInitialLoading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-user mr-2"></i>
                            Informasi Pribadi
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">ID Karyawan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ employee?.employee_id || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</label>
                                <p class="text-lg font-semibold text-gray-900">{{ employee?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                                <p class="text-gray-900">{{ employee?.email || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Telepon</label>
                                <p class="text-gray-900">{{ employee?.phone || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                                <p class="text-gray-900">{{ formatGender(employee?.gender) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Lahir</label>
                                <p class="text-gray-900">{{ formatDate(employee?.birth_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status Pernikahan</label>
                                <p class="text-gray-900">{{ formatMaritalStatus(employee?.marital_status) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Pendidikan</label>
                                <p class="text-gray-900">{{ formatEducation(employee?.education) }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Alamat</label>
                                <p class="text-gray-900">{{ employee?.address || '-' }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Employment Information -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-briefcase mr-2"></i>
                            Informasi Pekerjaan
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Jabatan</label>
                                <p class="text-lg font-semibold text-gray-900">{{ employee?.position || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Departemen</label>
                                <p class="text-lg font-semibold text-gray-900">{{ employee?.department || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi Kerja</label>
                                <p class="text-gray-900">{{ employee?.location?.name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Jenis PKWT</label>
                                <p class="text-gray-900">{{ employee?.pkwt?.name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Masuk</label>
                                <p class="text-gray-900">{{ formatDate(employee?.hire_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <Tag 
                                    :value="formatStatus(employee?.status).label" 
                                    :severity="formatStatus(employee?.status).severity"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Mulai Kontrak</label>
                                <p class="text-gray-900">{{ formatDate(employee?.contract_start) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Akhir Kontrak</label>
                                <p class="text-gray-900">{{ formatDate(employee?.contract_end) }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Gaji Pokok</label>
                                <p class="text-lg font-semibold text-green-600">{{ formatCurrency(employee?.salary) }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Emergency Contact -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-phone mr-2"></i>
                            Kontak Darurat
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nama</label>
                                <p class="text-gray-900">{{ employee?.emergency_contact_name || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Telepon</label>
                                <p class="text-gray-900">{{ employee?.emergency_contact_phone || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Hubungan</label>
                                <p class="text-gray-900">{{ employee?.emergency_contact_relation || '-' }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Notes -->
                <Card v-if="employee?.notes">
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-file-edit mr-2"></i>
                            Catatan
                        </div>
                    </template>
                    <template #content>
                        <p class="text-gray-900 whitespace-pre-line">{{ employee?.notes }}</p>
                    </template>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-chart-line mr-2"></i>
                            Statistik Singkat
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm font-medium text-blue-600">Masa Kerja</p>
                                <p class="text-2xl font-bold text-blue-700">{{ workingPeriod }}</p>
                            </div>
                            
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <p class="text-sm font-medium text-green-600">Status Kontrak</p>
                                <p class="text-lg font-semibold text-green-700">{{ contractStatus }}</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Recent Activities -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-clock mr-2"></i>
                            Aktivitas Terakhir
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div class="text-center text-gray-500 py-4">
                                <i class="pi pi-info-circle text-gray-400 text-2xl mb-2"></i>
                                <p class="text-sm">Data aktivitas akan ditampilkan di sini</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-cog mr-2"></i>
                            Aksi Cepat
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-2">
                            <Button 
                                label="Edit Karyawan" 
                                icon="pi pi-pencil" 
                                severity="warning"
                                outlined
                                class="w-full"
                                :loading="isLoading"
                                @click="editEmployee"
                            />
                            <Button 
                                label="Lihat Presensi" 
                                icon="pi pi-calendar" 
                                severity="info"
                                outlined
                                class="w-full"
                                @click="viewPresence"
                            />
                            <Button 
                                label="Generate Report" 
                                icon="pi pi-file-pdf" 
                                severity="secondary"
                                outlined
                                class="w-full"
                                @click="generateReport"
                            />
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Loading State -->
        <div v-else-if="isInitialLoading" class="flex items-center justify-center py-16">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Loading Employee Data...</h3>
                <p class="text-gray-500">Please wait while we fetch the employee information</p>
            </div>
        </div>

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
                    :loading="isInitialLoading"
                />
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
                    outlined
                    @click="backToIndex"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import { employeeApi } from '@/utils/api'

const props = defineProps({
    employee: {
        type: Object,
        default: () => null
    }
})

const toast = useToast()
const isLoading = ref(false)
const isInitialLoading = ref(true)

// Use reactive employee data - will be loaded via fetch API
const employee = ref(null)

// Get employee ID from URL
const getEmployeeId = () => {
    const path = window.location.pathname
    const matches = path.match(/\/employees\/(\d+)/)
    return matches ? parseInt(matches[1]) : null
}

// Load employee data via fetch API
const loadEmployee = async (employeeId) => {
    if (!employeeId) return
    
    isInitialLoading.value = true
    try {
        const response = await employeeApi.getEmployee(employeeId)
        // Handle response structure (API returns data.data)
        employee.value = response.data?.data || response.data
        
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
        }
    } finally {
        isInitialLoading.value = false
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
        isInitialLoading.value = false
    } else {
        isInitialLoading.value = false
    }
})

// Format functions
const formatDate = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const formatCurrency = (amount) => {
    if (!amount) return '-'
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount)
}

const formatGender = (gender) => {
    const genderMap = {
        'male': 'Laki-laki',
        'female': 'Perempuan',
        'other': 'Lainnya'
    }
    return genderMap[gender] || '-'
}

const formatMaritalStatus = (status) => {
    const statusMap = {
        'single': 'Belum Menikah',
        'married': 'Menikah',
        'divorced': 'Cerai',
        'other': 'Lainnya'
    }
    return statusMap[status] || '-'
}

const formatEducation = (education) => {
    const educationMap = {
        'sd': 'SD',
        'smp': 'SMP',
        'sma': 'SMA',
        'diploma': 'Diploma',
        'sarjana': 'Sarjana',
        'other': 'Lainnya'
    }
    return educationMap[education] || '-'
}

const formatStatus = (status) => {
    const statusMap = {
        'active': { label: 'Aktif', severity: 'success' },
        'inactive': { label: 'Tidak Aktif', severity: 'warning' },
        'resigned': { label: 'Resign', severity: 'info' },
        'terminated': { label: 'Terminate', severity: 'danger' }
    }
    return statusMap[status] || { label: '-', severity: 'secondary' }
}

// Computed properties
const workingPeriod = computed(() => {
    if (!employee.value?.hire_date) return '-'
    
    const hireDate = new Date(employee.value.hire_date)
    const now = new Date()
    const diffTime = Math.abs(now - hireDate)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    const years = Math.floor(diffDays / 365)
    const months = Math.floor((diffDays % 365) / 30)
    
    if (years > 0) {
        return months > 0 ? `${years} tahun ${months} bulan` : `${years} tahun`
    }
    return months > 0 ? `${months} bulan` : `${diffDays} hari`
})

const contractStatus = computed(() => {
    if (!employee.value?.contract_end) return 'Permanen'
    
    const endDate = new Date(employee.value.contract_end)
    const now = new Date()
    
    if (endDate < now) return 'Habis'
    
    const diffTime = endDate - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays <= 30) return `${diffDays} hari lagi`
    if (diffDays <= 90) return 'Segera berakhir'
    return 'Aktif'
})

// Action functions
const editEmployee = async () => {
    if (!employee.value?.id) return
    
    isLoading.value = true
    try {
        router.visit(`/employees/${employee.value.id}/edit`)
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Gagal membuka halaman edit',
            life: 3000
        })
    } finally {
        isLoading.value = false
    }
}

const refreshEmployee = async () => {
    const employeeId = getEmployeeId()
    if (!employeeId) return
    
    isLoading.value = true
    try {
        const response = await employeeApi.getEmployee(employeeId)
        // Handle response structure (API returns data.data)
        employee.value = response.data?.data || response.data
        toast.add({
            severity: 'success',
            summary: 'Refreshed',
            detail: 'Employee data refreshed successfully',
            life: 3000
        })
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to refresh employee data',
            life: 3000
        })
    } finally {
        isLoading.value = false
    }
}

const backToIndex = () => {
    router.visit('/employees')
}

const viewPresence = () => {
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Fitur presensi akan segera tersedia',
        life: 3000
    })
}

const generateReport = () => {
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Fitur generate report akan segera tersedia',
        life: 3000
    })
}
</script>
