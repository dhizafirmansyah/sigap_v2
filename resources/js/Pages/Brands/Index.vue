<template>
    <Head title="Brands" />
    
    <AppLayout 
        title="Brands" 
        subtitle="Manage brand records and information"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    label="Tambah Brand" 
                    icon="pi pi-plus" 
                    @click="router.visit('/brands/create')"
                />
            </div>
        </template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <Card>
                <template #content>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="pi pi-bookmark text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500">Total Brands</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ filteredBrands?.total || 0 }}</dd>
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="pi pi-check-circle text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500">Active Brands</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ getActiveCount() }}</dd>
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="pi pi-pause-circle text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-gray-500">Inactive Brands</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ getInactiveCount() }}</dd>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Search and Filters Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Search & Filters</h3>
                    <Button 
                        :icon="showFilters ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
                        text 
                        @click="showFilters = !showFilters"
                        class="p-button-sm"
                    />
                </div>
                
                <Transition name="slide-down">
                    <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search Input -->
                        <div class="md:col-span-2 relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Brands</label>
                            <div class="relative">
                                <AutoComplete
                                    v-model="searchQuery"
                                    :suggestions="searchSuggestions"
                                    @complete="searchBrands"
                                    @item-select="selectBrand"
                                    placeholder="Search by name, code, or description..."
                                    optionLabel="name"
                                    :loading="searchLoading"
                                    class="w-full"
                                    inputClass="w-full pl-12 pr-4"
                                    @keyup.enter="performSearch"
                                >
                                    <template #option="{ option }">
                                        <div class="flex items-center p-2">
                                            <div>
                                                <div class="font-medium">{{ option.name }}</div>
                                                <div class="text-sm text-gray-500">{{ option.code }}</div>
                                            </div>
                                        </div>
                                    </template>
                                </AutoComplete>
                                <i class="pi pi-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <Dropdown
                                v-model="filters.is_active"
                                :options="statusOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="All Status"
                                class="w-full"
                            />
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col gap-2">
                            <Button 
                                label="Clear Filters" 
                                severity="secondary"
                                outlined
                                icon="pi pi-times"
                                @click="clearFilters"
                                class="w-full"
                                size="small"
                            />
                            <Button 
                                label="Refresh" 
                                severity="info"
                                outlined
                                icon="pi pi-refresh"
                                @click="refreshData"
                                :loading="loading"
                                class="w-full"
                                size="small"
                            />
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Data Table Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Brand List</h3>
                    <div class="flex gap-2">
                        <Button 
                            label="Export" 
                            icon="pi pi-download" 
                            severity="help"
                            outlined
                            size="small"
                            @click="exportData"
                        />
                    </div>
                </div>
                
                <DataTable 
                    :value="paginatedBrands" 
                    :loading="loading"
                    dataKey="id"
                    responsiveLayout="scroll"
                    class="p-datatable-customers"
                >
                    <template #empty>
                        <div class="text-center py-8">
                            <i class="pi pi-info-circle text-gray-400 text-3xl mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Brands Found</h3>
                            <p class="text-gray-500 mb-4">No brands match your current filters.</p>
                            <Button 
                                label="Clear Filters" 
                                icon="pi pi-times" 
                                outlined 
                                @click="clearFilters"
                            />
                        </div>
                    </template>

                    <Column field="code" header="Code" sortable class="w-32">
                        <template #body="{ data }">
                            <div class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                {{ data.code }}
                            </div>
                        </template>
                    </Column>

                    <Column field="name" header="Brand Name" sortable>
                        <template #body="{ data }">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-blue-600 font-medium text-sm">{{ getInitials(data.name) }}</span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900">{{ data.name }}</div>
                                    <div v-if="data.description" class="text-sm text-gray-500 truncate max-w-xs">
                                        {{ data.description }}
                                    </div>
                                    <div v-if="data.category" class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded inline-block mt-1">
                                        {{ data.category }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>

                    <Column field="target_production_per_day" header="Daily Target" sortable class="w-32">
                        <template #body="{ data }">
                            <div v-if="data.target_production_per_day" class="text-center">
                                <div class="font-medium">{{ formatNumber(data.target_production_per_day) }}</div>
                                <div class="text-xs text-gray-500">units/day</div>
                            </div>
                            <div v-else class="text-gray-400 text-center">-</div>
                        </template>
                    </Column>

                    <Column field="is_active" header="Status" sortable class="w-24">
                        <template #body="{ data }">
                            <Tag 
                                :value="data.is_active ? 'Active' : 'Inactive'" 
                                :severity="data.is_active ? 'success' : 'warning'"
                                @click="toggleStatusFilter(data.is_active)"
                                class="cursor-pointer"
                            />
                        </template>
                    </Column>

                    <Column field="created_at" header="Created" sortable class="w-32">
                        <template #body="{ data }">
                            <div class="text-sm text-gray-600">
                                {{ formatDate(data.created_at) }}
                            </div>
                        </template>
                    </Column>

                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex space-x-1">
                                <Button 
                                    icon="pi pi-eye" 
                                    severity="info"
                                    size="small"
                                    outlined
                                    v-tooltip="'View Details'"
                                    @click="viewBrand(data)"
                                />
                                <Button 
                                    icon="pi pi-pencil" 
                                    severity="warning"
                                    size="small"
                                    outlined
                                    v-tooltip="'Edit Brand'"
                                    @click="editBrand(data)"
                                />
                                <Button 
                                    icon="pi pi-trash" 
                                    severity="danger"
                                    size="small"
                                    outlined
                                    v-tooltip="'Delete Brand'"
                                    @click="confirmDelete(data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">
                            Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to 
                            {{ Math.min(currentPage * itemsPerPage, filteredBrands?.data?.length || 0) }} 
                            of {{ filteredBrands?.data?.length || 0 }} results
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <Button 
                            icon="pi pi-chevron-left" 
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                            outlined
                            size="small"
                        />
                        <span class="px-3 py-1 text-sm font-medium">{{ currentPage }} / {{ totalPages }}</span>
                        <Button 
                            icon="pi pi-chevron-right" 
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                            outlined
                            size="small"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Delete Confirmation Dialog -->
        <Dialog 
            v-model:visible="deleteDialog" 
            :style="{ width: '32rem' }" 
            header="Confirm Delete" 
            :modal="true"
            class="p-fluid"
        >
            <div class="flex items-center mb-4">
                <i class="pi pi-exclamation-triangle text-red-500 text-2xl mr-3"></i>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Delete Brand</h3>
                    <p class="text-sm text-gray-500 mt-1">This action cannot be undone</p>
                </div>
            </div>
            
            <div v-if="selectedBrand" class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Name:</span>
                        <span class="text-gray-900">{{ selectedBrand.name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Code:</span>
                        <span class="text-gray-900 font-mono">{{ selectedBrand.code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Status:</span>
                        <Tag 
                            :value="selectedBrand.is_active ? 'Active' : 'Inactive'" 
                            :severity="selectedBrand.is_active ? 'success' : 'warning'"
                        />
                    </div>
                </div>
            </div>
            
            <template #footer>
                <Button 
                    label="Cancel" 
                    severity="secondary"
                    outlined
                    @click="deleteDialog = false" 
                    :disabled="deleting"
                />
                <Button 
                    label="Delete Brand" 
                    severity="danger"
                    @click="deleteBrand" 
                    :loading="deleting"
                />
            </template>
        </Dialog>

        <!-- Floating Action Button -->
        <div class="fixed bottom-6 right-6 z-50">
            <Button 
                icon="pi pi-plus" 
                rounded 
                size="large"
                @click="router.visit('/brands/create')"
                v-tooltip="'Add New Brand'"
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
import { brandApi } from '@/utils/api'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import AutoComplete from 'primevue/autocomplete'
import Dropdown from 'primevue/dropdown'
import Dialog from 'primevue/dialog'
import Card from 'primevue/card'
import { debounce } from 'lodash'

const props = defineProps({
    brands: Object,
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
const selectedBrand = ref(null)
const deleting = ref(false)
const showFilters = ref(false)

// Original data and filtered data
const originalBrands = ref(props.brands)
const filteredBrands = ref(props.brands)

const filters = reactive({
    search: props.filters?.search || '',
    is_active: props.filters?.is_active ?? null
})

// Options for dropdowns
const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Active', value: true },
    { label: 'Inactive', value: false }
]

// Stats computed functions
const getActiveCount = () => {
    if (!filteredBrands.value?.data) return 0
    return filteredBrands.value.data.filter(brand => brand.is_active === true).length
}

const getInactiveCount = () => {
    if (!filteredBrands.value?.data) return 0
    return filteredBrands.value.data.filter(brand => brand.is_active === false).length
}

// JavaScript-based filtering function
const applyClientSideFilters = () => {
    if (!originalBrands.value?.data) return

    let filtered = [...originalBrands.value.data]

    // Apply search filter
    if (filters.search && filters.search.trim()) {
        const searchTerm = filters.search.toLowerCase().trim()
        filtered = filtered.filter(brand => 
            brand.name.toLowerCase().includes(searchTerm) ||
            brand.code.toLowerCase().includes(searchTerm) ||
            (brand.description && brand.description.toLowerCase().includes(searchTerm)) ||
            (brand.category && brand.category.toLowerCase().includes(searchTerm))
        )
    }

    // Apply status filter
    if (filters.is_active !== null) {
        filtered = filtered.filter(brand => brand.is_active === filters.is_active)
    }

    // Update filtered data
    filteredBrands.value = {
        ...originalBrands.value,
        data: filtered,
        total: filtered.length,
        per_page: originalBrands.value.per_page,
        current_page: 1,
        last_page: Math.ceil(filtered.length / (originalBrands.value.per_page || 10))
    }

    // Reset pagination to first page
    currentPage.value = 1
}

// Toggle status filter
const toggleStatusFilter = (status) => {
    if (filters.is_active === status) {
        filters.is_active = null
    } else {
        filters.is_active = status
    }
    applyClientSideFilters()
}

// Export functionality
const exportData = () => {
    toast.add({
        severity: 'info',
        summary: 'Export Started',
        detail: 'Preparing brand data for export...',
        life: 3000
    })
    
    // Simulate export process
    setTimeout(() => {
        toast.add({
            severity: 'success',
            summary: 'Export Complete',
            detail: 'Brand data exported successfully',
            life: 3000
        })
    }, 2000)
}

// Search functionality with local filtering
const searchBrands = debounce(async (event) => {
    if (!event.query.trim()) {
        searchSuggestions.value = []
        return
    }

    // Use local data for suggestions
    if (originalBrands.value?.data) {
        const query = event.query.toLowerCase()
        searchSuggestions.value = originalBrands.value.data
            .filter(brand => 
                brand.name.toLowerCase().includes(query) ||
                brand.code.toLowerCase().includes(query)
            )
            .slice(0, 10)
    }
}, 200)

const selectBrand = (event) => {
    // When user selects a name from autocomplete, trigger search
    if (event.value && typeof event.value === 'string') {
        filters.search = event.value
        applyClientSideFilters()
    } else if (event.value && event.value.name) {
        filters.search = event.value.name
        applyClientSideFilters()
    }
}

// Add manual search trigger
const performSearch = () => {
    filters.search = searchQuery.value
    applyClientSideFilters()
}

// Filter functionality with client-side filtering
const applyFilters = () => {
    applyClientSideFilters()
}

const clearFilters = () => {
    filters.search = ''
    filters.is_active = null
    searchQuery.value = ''
    
    // Reset to show all data
    filteredBrands.value = originalBrands.value
    
    toast.add({
        severity: 'info',
        summary: 'Filters Cleared',
        detail: 'All filters have been reset',
        life: 2000
    })
}

const refreshData = async () => {
    loading.value = true
    try {
        const response = await brandApi.getBrands()
        originalBrands.value = response.data?.data || response.data
        applyClientSideFilters()
        
        toast.add({
            severity: 'success',
            summary: 'Data Refreshed',
            detail: 'Brand data refreshed successfully',
            life: 2000
        })
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to refresh data',
            life: 3000
        })
    } finally {
        loading.value = false
    }
}

// Client-side pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

const paginatedBrands = computed(() => {
    if (!filteredBrands.value?.data) return []
    
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    
    return filteredBrands.value.data.slice(start, end)
})

const totalPages = computed(() => {
    if (!filteredBrands.value?.data) return 0
    return Math.ceil(filteredBrands.value.data.length / itemsPerPage.value)
})

const onPage = (event) => {
    currentPage.value = event.page + 1
    itemsPerPage.value = event.rows
}

// CRUD actions with direct navigation for view and edit
const viewBrand = (brand) => {
    router.visit(`/brands/${brand.id}`)
}

const editBrand = (brand) => {
    router.visit(`/brands/${brand.id}/edit`)
}

// Delete functionality
const confirmDelete = (brand) => {
    selectedBrand.value = brand
    deleteDialog.value = true
}

const deleteBrand = async () => {
    if (!selectedBrand.value) return

    deleting.value = true
    try {
        await brandApi.deleteBrand(selectedBrand.value.id)
        
        // Remove from local data
        if (originalBrands.value?.data) {
            originalBrands.value.data = originalBrands.value.data.filter(
                brand => brand.id !== selectedBrand.value.id
            )
        }
        
        // Refresh filtered data
        applyClientSideFilters()
        
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Brand deleted successfully',
            life: 3000
        })
        
        deleteDialog.value = false
        selectedBrand.value = null
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to delete brand',
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

const formatNumber = (number) => {
    if (!number) return '0'
    return new Intl.NumberFormat('en-US').format(number)
}

const getInitials = (name) => {
    if (!name) return ''
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
}

onMounted(() => {
    if (props.filters?.search) {
        searchQuery.value = props.filters.search
    }
    
    // Initialize filtered data
    applyClientSideFilters()
})

// Watch for filter changes
watch([() => filters.search, () => filters.is_active], () => {
    currentPage.value = 1 // Reset to first page when filters change
    applyClientSideFilters()
}, { deep: true })

// Watch for search query changes
watch(searchQuery, (newValue) => {
    filters.search = newValue
}, { debounce: 300 })
</script>
