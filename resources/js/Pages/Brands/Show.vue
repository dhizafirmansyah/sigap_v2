<template>
    <Head title="Brand Details" />
    
    <AppLayout 
        title="Brand Details" 
        :subtitle="brand ? `Complete information for ${brand.name}` : 'Loading...'"
        :user="$page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    label="Edit" 
                    icon="pi pi-pencil" 
                    severity="warning"
                    @click="editBrand"
                    :disabled="!brand || isLoading || isInitialLoading"
                    :loading="isLoading"
                />
                <Button 
                    label="Refresh" 
                    icon="pi pi-refresh" 
                    severity="info"
                    outlined
                    :loading="isLoading"
                    @click="refreshBrand"
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

        <div v-if="brand && !isInitialLoading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-bookmark text-blue-500 mr-2"></i>
                            Basic Information
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Brand Name</label>
                                <div class="text-lg font-semibold text-gray-900">{{ brand.name || '-' }}</div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Brand Code</label>
                                <div class="font-mono text-sm bg-gray-100 px-3 py-2 rounded">
                                    {{ brand.code || '-' }}
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Category</label>
                                <div class="text-gray-900">
                                    <Tag v-if="brand.category" :value="brand.category" severity="info" />
                                    <span v-else class="text-gray-400">-</span>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <Tag 
                                    :value="brand.is_active ? 'Active' : 'Inactive'" 
                                    :severity="brand.is_active ? 'success' : 'warning'"
                                />
                            </div>
                            
                            <div v-if="brand.description" class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                                <div class="text-gray-900 bg-gray-50 p-3 rounded">{{ brand.description }}</div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Production Information -->
                <Card v-if="brand.target_production_per_day">
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-chart-line text-green-500 mr-2"></i>
                            Production Information
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Daily Production Target</label>
                                <div class="text-2xl font-bold text-green-600">
                                    {{ formatNumber(brand.target_production_per_day) }}
                                    <span class="text-sm font-normal text-gray-500">units/day</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quality Standards -->
                <Card v-if="brand.quality_standards && brand.quality_standards.length > 0">
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-check-circle text-purple-500 mr-2"></i>
                            Quality Standards
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div v-for="(standard, index) in brand.quality_standards" :key="index" 
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <div class="font-medium text-gray-900">{{ standard.name }}</div>
                                </div>
                                <div class="text-gray-600">{{ standard.value }}</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-chart-bar text-blue-500 mr-2"></i>
                            Quick Stats
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Created</span>
                                <span class="font-medium">{{ formatDate(brand.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Last Updated</span>
                                <span class="font-medium">{{ formatDate(brand.updated_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Active Period</span>
                                <span class="font-medium">{{ getActivePeriod() }}</span>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Related Information -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-sitemap text-orange-500 mr-2"></i>
                            Related Information
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Products</span>
                                <span class="font-medium">{{ brand.packs_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Packages</span>
                                <span class="font-medium">{{ brand.kemas_count || 0 }}</span>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Quick Actions -->
                <Card>
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-cog text-gray-500 mr-2"></i>
                            Quick Actions
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <Button 
                                label="View Products" 
                                icon="pi pi-box"
                                outlined
                                class="w-full"
                                @click="viewProducts"
                            />
                            <Button 
                                label="View Packages" 
                                icon="pi pi-package"
                                outlined
                                class="w-full"
                                @click="viewPackages"
                            />
                            <Button 
                                label="Generate Report" 
                                icon="pi pi-file-pdf"
                                severity="help"
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
                <h3 class="text-lg font-medium text-gray-900 mb-2">Loading Brand Data...</h3>
                <p class="text-gray-500">Please wait while we fetch the brand information</p>
            </div>
        </div>

        <!-- No Data State -->
        <div v-else class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-medium text-red-800 mb-2">No Brand Data Found</h3>
            <div class="text-sm text-red-700 mb-4">
                <p>Unable to load brand data. This could be due to:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>The brand may have been deleted</li>
                    <li>You may not have permission to view this brand</li>
                    <li>The brand ID in the URL may be incorrect</li>
                    <li>There may be a network connection issue</li>
                </ul>
            </div>
            <div class="flex gap-3">
                <Button 
                    label="Try Again" 
                    icon="pi pi-refresh" 
                    outlined 
                    :loading="isInitialLoading"
                    @click="reloadBrand"
                />
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
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
import { brandApi } from '@/utils/api'

const props = defineProps({
    brand: {
        type: Object,
        default: () => null
    }
})

const toast = useToast()
const isLoading = ref(false)
const isInitialLoading = ref(true)

// Use reactive brand data - will be loaded via fetch API
const brand = ref(null)

// Get brand ID from URL
const getBrandId = () => {
    const path = window.location.pathname
    const matches = path.match(/\/brands\/(\d+)/)
    return matches ? parseInt(matches[1]) : null
}

// Load brand data via fetch API
const loadBrand = async (brandId) => {
    if (!brandId) return
    
    isInitialLoading.value = true
    try {
        const response = await brandApi.getBrand(brandId)
        // Handle response structure (API returns data.data)
        brand.value = response.data?.data || response.data
        
        toast.add({
            severity: 'success',
            summary: 'Data Loaded',
            detail: 'Brand data loaded successfully',
            life: 2000
        })
    } catch (error) {
        console.error('Failed to load brand:', error)
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to load brand data',
            life: 5000
        })
        
        // Fallback to props data if available
        if (props.brand) {
            brand.value = props.brand
        }
    } finally {
        isInitialLoading.value = false
    }
}

// Initialize brand data on mount
onMounted(async () => {
    const brandId = getBrandId()
    
    if (brandId) {
        // Load data via fetch API
        await loadBrand(brandId)
    } else if (props.brand) {
        // Fallback to props if no ID in URL
        brand.value = props.brand
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

const formatNumber = (number) => {
    if (!number) return '0'
    return new Intl.NumberFormat('en-US').format(number)
}

// Computed properties
const getActivePeriod = () => {
    if (!brand.value?.created_at) return '-'
    
    const createdDate = new Date(brand.value.created_at)
    const now = new Date()
    const diffTime = Math.abs(now - createdDate)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    const years = Math.floor(diffDays / 365)
    const months = Math.floor((diffDays % 365) / 30)
    
    if (years > 0) {
        return months > 0 ? `${years} years ${months} months` : `${years} years`
    }
    return months > 0 ? `${months} months` : `${diffDays} days`
}

// Action functions
const editBrand = async () => {
    if (!brand.value?.id) return
    
    isLoading.value = true
    try {
        router.visit(`/brands/${brand.value.id}/edit`)
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to open edit page',
            life: 3000
        })
    } finally {
        isLoading.value = false
    }
}

const refreshBrand = async () => {
    const brandId = getBrandId()
    if (!brandId) return
    
    isLoading.value = true
    try {
        const response = await brandApi.getBrand(brandId)
        // Handle response structure (API returns data.data)
        brand.value = response.data?.data || response.data
        toast.add({
            severity: 'success',
            summary: 'Refreshed',
            detail: 'Brand data refreshed successfully',
            life: 3000
        })
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.message || 'Failed to refresh brand data',
            life: 3000
        })
    } finally {
        isLoading.value = false
    }
}

const reloadBrand = async () => {
    const brandId = getBrandId()
    if (brandId) {
        await loadBrand(brandId)
    }
}

const backToIndex = () => {
    router.visit('/brands')
}

const viewProducts = () => {
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Product view feature will be available soon',
        life: 3000
    })
}

const viewPackages = () => {
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Package view feature will be available soon',
        life: 3000
    })
}

const generateReport = () => {
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'Report generation feature will be available soon',
        life: 3000
    })
}
</script>
