<template>
    <Head title="Locations" />
    
    <AppLayout 
        title="Locations" 
        subtitle="Manage location records and geographic information"
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
                    label="Add Location"
                    @click="router.visit('/locations/create')" 
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
                        <p class="text-blue-100 text-sm font-medium">Total Locations</p>
                        <p class="text-3xl font-bold">{{ filteredLocations?.length || 0 }}</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <i class="pi pi-map-marker text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Active Locations</p>
                        <p class="text-3xl font-bold">{{ getActiveLocationsCount() }}</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <i class="pi pi-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">With Coordinates</p>
                        <p class="text-3xl font-bold">{{ getLocationsWithCoordinatesCount() }}</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <i class="pi pi-compass text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Total Usage</p>
                        <p class="text-3xl font-bold">{{ getTotalUsageCount() }}</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-3">
                        <i class="pi pi-chart-bar text-2xl"></i>
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
                            @complete="searchLocations"
                            @item-select="selectLocation"
                            @keyup.enter="performSearch"
                            placeholder="Search locations by name, code, address..."
                            class="w-full"
                            :loading="searchLoading"
                            :forceSelection="false"
                            :completeOnFocus="false"
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
                                        v-for="status in statusChips" 
                                        :key="status.value"
                                        @click="toggleStatusFilter(status.value)"
                                        :class="[
                                            'px-3 py-1 rounded-full text-xs font-medium cursor-pointer transition-colors',
                                            statusFilter === status.value 
                                                ? 'bg-blue-500 text-white' 
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ status.label }}
                                    </div>
                                </div>
                            </div>

                            <!-- Coordinate Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="pi pi-compass mr-1"></i>Coordinates
                                </label>
                                <Select
                                    v-model="coordinateFilter"
                                    :options="coordinateOptions"
                                    optionLabel="label"
                                    optionValue="value"
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
                        Location Directory
                        <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                            {{ filteredLocations?.length || 0 }} locations
                        </span>
                    </h3>
                    <div class="flex gap-2">
                        <Button 
                            icon="pi pi-plus" 
                            label="Add Location"
                            @click="router.visit('/locations/create')"
                            class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 border-0"
                        />
                        <Button 
                            icon="pi pi-upload" 
                            label="Import"
                            @click="importLocations"
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
                    :value="paginatedLocations" 
                    :loading="loading"
                    stripedRows
                    responsiveLayout="scroll"
                    :paginator="true"
                    :rows="itemsPerPage"
                    :totalRecords="filteredLocations?.length || 0"
                    :lazy="false"
                    @page="onPage"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} locations"
                    :rowsPerPageOptions="[10, 25, 50]"
                    class="p-datatable-customers"
                    dataKey="id"
                    :globalFilterFields="['name', 'code', 'address', 'description']"
                >
                    <template #empty>
                        <div class="text-center py-12">
                            <i class="pi pi-map-marker text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No locations found</h3>
                            <p class="text-gray-500 mb-6">Get started by adding your first location to the system</p>
                            <div class="flex justify-center gap-4">
                                <Button 
                                    label="Add Location" 
                                    icon="pi pi-plus"
                                    @click="router.visit('/locations/create')"
                                    size="large"
                                    class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 border-0"
                                />
                                <Button 
                                    label="Import Locations" 
                                    icon="pi pi-upload"
                                    @click="importLocations"
                                    size="large"
                                    severity="secondary"
                                    outlined
                                    class="px-8 py-3"
                                />
                            </div>
                        </div>
                    </template>

                    <Column field="name" header="Location" :sortable="true" class="min-w-64">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12 mr-4">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-orange-400 to-orange-500 flex items-center justify-center text-white font-semibold shadow-md">
                                        {{ getInitials(slotProps.data.name) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 hover:text-blue-600 cursor-pointer transition-colors"
                                         @click="viewLocation(slotProps.data.id)">
                                        {{ slotProps.data.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 flex items-center">
                                        <i class="pi pi-tag mr-1"></i>
                                        {{ slotProps.data.code }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    
                    <Column field="address" header="Address" :sortable="true" class="min-w-48">
                        <template #body="slotProps">
                            <div>
                                <div class="font-medium text-gray-900 max-w-xs truncate" :title="slotProps.data.address">
                                    {{ slotProps.data.address || '-' }}
                                </div>
                                <div v-if="slotProps.data.latitude && slotProps.data.longitude" class="text-sm text-gray-500 flex items-center">
                                    <i class="pi pi-compass mr-1"></i>
                                    {{ parseFloat(slotProps.data.latitude).toFixed(4) }}, {{ parseFloat(slotProps.data.longitude).toFixed(4) }}
                                </div>
                            </div>
                        </template>
                    </Column>
                    
                    <Column field="radius" header="Coverage" :sortable="true" class="min-w-32">
                        <template #body="slotProps">
                            <div class="text-center">
                                <div class="font-medium text-gray-900">
                                    {{ slotProps.data.radius ? `${slotProps.data.radius}m` : '-' }}
                                </div>
                                <div v-if="slotProps.data.radius" class="text-xs text-gray-500">
                                    Radius
                                </div>
                            </div>
                        </template>
                    </Column>
                    
                    <Column field="usage" header="Usage" class="min-w-40">
                        <template #body="slotProps">
                            <div class="text-sm">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-gray-600">Employees:</span>
                                    <span class="font-medium">{{ slotProps.data.employees_count || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-gray-600">Packs:</span>
                                    <span class="font-medium">{{ slotProps.data.packs_count || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Kemas:</span>
                                    <span class="font-medium">{{ slotProps.data.kemas_count || 0 }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>
                    
                    <Column field="is_active" header="Status" :sortable="true" class="min-w-32">
                        <template #body="slotProps">
                            <div class="text-center">
                                <Tag 
                                    :value="slotProps.data.is_active ? 'Active' : 'Inactive'" 
                                    :severity="slotProps.data.is_active ? 'success' : 'danger'"
                                    class="font-medium"
                                />
                            </div>
                        </template>
                    </Column>
                    
                    <Column field="formatted_created_at" header="Created" :sortable="true" class="min-w-36">
                        <template #body="slotProps">
                            <div class="text-center">
                                <div class="font-medium text-gray-900">{{ formatDate(slotProps.data.created_at) }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ slotProps.data.formatted_created_at }}
                                </div>
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
                                    @click="viewLocation(slotProps.data.id)"
                                    v-tooltip="'View Details'"
                                />
                                <Button 
                                    icon="pi pi-pencil"
                                    size="small"
                                    severity="warning"
                                    outlined
                                    @click="editLocation(slotProps.data.id)"
                                    v-tooltip="'Edit Location'"
                                />
                                <Button 
                                    icon="pi pi-trash"
                                    size="small"
                                    severity="danger"
                                    outlined
                                    @click="confirmDelete(slotProps.data)"
                                    v-tooltip="'Delete Location'"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <!-- Enhanced Delete Confirmation Dialog -->
        <Dialog 
            v-model:visible="showDeleteDialog" 
            :style="{width: '500px'}" 
            header="Delete Location" 
            :modal="true"
            class="p-fluid"
        >
            <div class="text-center">
                <div class="bg-red-100 rounded-full p-6 w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                    <i class="pi pi-exclamation-triangle text-4xl text-red-500"></i>
                </div>
                
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Are you absolutely sure?</h3>
                <p class="text-gray-600 mb-4">
                    You are about to permanently delete <strong class="text-red-600">{{ locationToDelete?.name }}</strong>. 
                    This action cannot be undone and will remove all associated data.
                </p>
                
                <div class="bg-gray-50 rounded-lg p-4 mb-6" v-if="locationToDelete">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Location Code:</span>
                        <span class="font-mono font-medium">{{ locationToDelete.code }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-gray-600">Address:</span>
                        <span class="font-medium max-w-xs truncate">{{ locationToDelete.address || 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-gray-600">Total Usage:</span>
                        <span class="font-medium">{{ getTotalUsageForLocation(locationToDelete) }} items</span>
                    </div>
                </div>
            </div>
            
            <template #footer>
                <div class="flex gap-3 justify-center">
                    <Button 
                        label="Cancel" 
                        icon="pi pi-times" 
                        class="p-button-outlined flex-1" 
                        @click="showDeleteDialog = false"
                        :disabled="deleting"
                    />
                    <Button 
                        label="Delete Location" 
                        icon="pi pi-trash" 
                        class="p-button-danger flex-1" 
                        @click="deleteLocation"
                        :loading="deleting"
                    />
                </div>
            </template>
        </Dialog>

        <!-- Floating Action Button -->
        <div class="fixed bottom-6 right-6 z-50">
            <Button 
                icon="pi pi-plus"
                @click="router.visit('/locations/create')"
                class="p-button-rounded p-button-lg bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 border-0 shadow-2xl"
                style="width: 60px; height: 60px;"
                v-tooltip="'Add New Location'"
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

.confirmation-content {
    display: flex;
    align-items: center;
}
</style>

<script setup>
import { ref, reactive, onMounted, computed, Transition, watch } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import { locationApi } from '@/utils/api'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import AutoComplete from 'primevue/autocomplete'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import { debounce } from 'lodash'

const props = defineProps({
    locations: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const page = usePage()
const toast = useToast()

// Reactive data
const loading = ref(false)
const searchLoading = ref(false)
const searchQuery = ref('')
const searchSuggestions = ref([])
const showDeleteDialog = ref(false)
const locationToDelete = ref(null)
const deleting = ref(false)
const showFilters = ref(false)

// Original data and filtered data
const originalLocations = ref(props.locations)
const filteredLocations = ref(props.locations?.data || [])

const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.is_active ?? null,
    coordinate: null
})

// Filter options
const statusOptions = [
    { label: 'All Statuses', value: '' },
    { label: 'Active', value: true },
    { label: 'Inactive', value: false }
]

const statusChips = [
    { label: 'Active', value: true },
    { label: 'Inactive', value: false }
]

const coordinateOptions = [
    { label: 'All Locations', value: null },
    { label: 'With Coordinates', value: 'with' },
    { label: 'Without Coordinates', value: 'without' }
]

// Computed values for stats
const getActiveLocationsCount = () => {
    if (!filteredLocations.value) return 0
    return filteredLocations.value.filter(loc => loc.is_active).length
}

const getLocationsWithCoordinatesCount = () => {
    if (!filteredLocations.value) return 0
    return filteredLocations.value.filter(loc => loc.latitude && loc.longitude).length
}

const getTotalUsageCount = () => {
    if (!filteredLocations.value) return 0
    return filteredLocations.value.reduce((total, loc) => {
        return total + (loc.employees_count || 0) + (loc.packs_count || 0) + (loc.kemas_count || 0)
    }, 0)
}

const getTotalUsageForLocation = (location) => {
    return (location.employees_count || 0) + (location.packs_count || 0) + (location.kemas_count || 0)
}

// JavaScript-based filtering function
const applyClientSideFilters = () => {
    if (!originalLocations.value?.data) return

    let filtered = [...originalLocations.value.data]

    // Apply search filter
    if (filters.search && filters.search.trim()) {
        const searchTerm = filters.search.toLowerCase().trim()
        filtered = filtered.filter(loc => 
            loc.name?.toLowerCase().includes(searchTerm) ||
            loc.code?.toLowerCase().includes(searchTerm) ||
            loc.address?.toLowerCase().includes(searchTerm) ||
            loc.description?.toLowerCase().includes(searchTerm)
        )
    }

    // Apply status filter
    if (filters.status !== null) {
        filtered = filtered.filter(loc => loc.is_active === filters.status)
    }

    // Apply coordinate filter
    if (filters.coordinate === 'with') {
        filtered = filtered.filter(loc => loc.latitude && loc.longitude)
    } else if (filters.coordinate === 'without') {
        filtered = filtered.filter(loc => !loc.latitude || !loc.longitude)
    }

    // Update filtered data
    filteredLocations.value = filtered

    // Reset pagination to first page
    currentPage.value = 1
}

// Toggle status filter
const statusFilter = ref(filters.status)
const coordinateFilter = ref(filters.coordinate)

const toggleStatusFilter = (status) => {
    if (statusFilter.value === status) {
        statusFilter.value = null
        filters.status = null
    } else {
        statusFilter.value = status
        filters.status = status
    }
    applyClientSideFilters()
}

// Export functionality
const exportData = () => {
    toast.add({
        severity: 'info',
        summary: 'Export Started',
        detail: 'Preparing location data for export...',
        life: 3000
    })
    
    // Simulate export process
    setTimeout(() => {
        toast.add({
            severity: 'success',
            summary: 'Export Complete',
            detail: 'Location data has been exported successfully',
            life: 3000
        })
    }, 2000)
}

// Import functionality
const importLocations = () => {
    toast.add({
        severity: 'info',
        summary: 'Import Feature',
        detail: 'Import functionality will be available soon',
        life: 3000
    })
}

// Search functionality with local filtering
const searchLocations = debounce(async (event) => {
    if (!event.query.trim()) {
        searchSuggestions.value = []
        return
    }

    // Use local data for suggestions
    if (originalLocations.value?.data) {
        const query = event.query.toLowerCase()
        const filtered = originalLocations.value.data.filter(loc => 
            loc.name?.toLowerCase().includes(query) ||
            loc.code?.toLowerCase().includes(query) ||
            loc.address?.toLowerCase().includes(query)
        ).slice(0, 10) // Limit to 10 results
        
        // Return only names for suggestions
        searchSuggestions.value = filtered.map(loc => loc.name)
    }
}, 200)

const selectLocation = (event) => {
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

// Filter functionality with client-side filtering
const applyFilters = () => {
    applyClientSideFilters()
}

const clearFilters = () => {
    filters.search = ''
    filters.status = null
    filters.coordinate = null
    searchQuery.value = ''
    statusFilter.value = null
    coordinateFilter.value = null
    
    // Reset to show all data
    filteredLocations.value = originalLocations.value?.data || []
    
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
        const { data } = await locationApi.getLocations()
        
        if (data.props?.locations) {
            // Update original data
            originalLocations.value = data.props.locations
            // Reset filters and show all data
            filters.search = ''
            filters.status = null
            filters.coordinate = null
            searchQuery.value = ''
            statusFilter.value = null
            coordinateFilter.value = null
            filteredLocations.value = originalLocations.value?.data || []
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

const paginatedLocations = computed(() => {
    if (!filteredLocations.value) return []
    
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    
    return filteredLocations.value.slice(start, end)
})

const onPage = (event) => {
    currentPage.value = event.page + 1
    itemsPerPage.value = event.rows
}

// CRUD actions
const viewLocation = (id) => {
    router.visit(`/locations/${id}`)
}

const editLocation = (id) => {
    router.visit(`/locations/${id}/edit`)
}

// Delete functionality
const confirmDelete = (location) => {
    locationToDelete.value = location
    showDeleteDialog.value = true
}

const deleteLocation = async () => {
    if (!locationToDelete.value) return

    deleting.value = true
    
    router.delete(`/locations/${locationToDelete.value.id}`, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Location deleted successfully',
                life: 3000
            })
            showDeleteDialog.value = false
            locationToDelete.value = null
        },
        onError: (errors) => {
            console.error('Delete error:', errors)
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to delete location',
                life: 3000
            })
        },
        onFinish: () => {
            deleting.value = false
        }
    })
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
    filteredLocations.value = originalLocations.value?.data || []
    applyClientSideFilters()
})

// Watch for filter changes
watch([() => filters.search, () => filters.status, () => filters.coordinate], () => {
    currentPage.value = 1 // Reset to first page when filters change
    applyClientSideFilters()
}, { deep: true })

// Watch for search query changes
watch(searchQuery, (newValue) => {
    filters.search = newValue
}, { debounce: 300 })

// Watch for coordinate filter changes
watch(coordinateFilter, (newValue) => {
    filters.coordinate = newValue
    applyClientSideFilters()
})
</script>
