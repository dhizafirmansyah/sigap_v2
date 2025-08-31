<template>
    <Head title="Edit Brand" />
    
    <AppLayout 
        title="Edit Brand" 
        :subtitle="brand ? `Edit information for ${brand.name}` : 'Loading...'"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
                    outlined
                    @click="router.visit('/brands')"
                />
            </div>
        </template>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-16">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Loading Brand Data...</h3>
                <p class="text-gray-500">Please wait while we fetch the brand information</p>
            </div>
        </div>

        <!-- Form -->
        <Card v-else-if="brand">
            <template #content>
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-bookmark mr-2"></i>
                            Basic Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Brand Name <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="name"
                                    v-model="form.name"
                                    :class="{'border-red-500': errors.name}"
                                    placeholder="Enter brand name"
                                    class="w-full"
                                />
                                <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
                            </div>

                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Brand Code <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="code"
                                    v-model="form.code"
                                    :class="{'border-red-500': errors.code}"
                                    placeholder="e.g. BRD001"
                                    class="w-full"
                                />
                                <small v-if="errors.code" class="text-red-500">{{ errors.code }}</small>
                                <small class="text-gray-500">Code must be unique for each brand</small>
                            </div>
                        </div>
                    </div>

                    <!-- Extended Information Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-info-circle mr-2"></i>
                            Extended Information
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                    Category
                                </label>
                                <InputText
                                    id="category"
                                    v-model="form.category"
                                    :class="{'border-red-500': errors.category}"
                                    placeholder="Brand category"
                                    class="w-full"
                                />
                                <small v-if="errors.category" class="text-red-500">{{ errors.category }}</small>
                            </div>

                            <div>
                                <label for="target_production_per_day" class="block text-sm font-medium text-gray-700 mb-2">
                                    Daily Production Target
                                </label>
                                <InputNumber
                                    id="target_production_per_day"
                                    v-model="form.target_production_per_day"
                                    :class="{'border-red-500': errors.target_production_per_day}"
                                    placeholder="Units per day"
                                    class="w-full"
                                    :min="0"
                                    :maxFractionDigits="2"
                                />
                                <small v-if="errors.target_production_per_day" class="text-red-500">{{ errors.target_production_per_day }}</small>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :class="{'border-red-500': errors.description}"
                                placeholder="Enter brand description (optional)"
                                rows="4"
                                class="w-full"
                            />
                            <small v-if="errors.description" class="text-red-500">{{ errors.description }}</small>
                        </div>
                    </div>

                    <!-- Quality Standards Section -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-check-circle mr-2"></i>
                            Quality Standards
                        </h3>
                        
                        <div class="space-y-4">
                            <div v-for="(standard, index) in form.quality_standards" :key="index" class="flex items-center space-x-3">
                                <InputText
                                    v-model="standard.name"
                                    placeholder="Standard name"
                                    class="flex-1"
                                />
                                <InputText
                                    v-model="standard.value"
                                    placeholder="Standard value"
                                    class="flex-1"
                                />
                                <Button 
                                    icon="pi pi-times" 
                                    severity="danger"
                                    outlined
                                    size="small"
                                    @click="removeQualityStandard(index)"
                                />
                            </div>
                            
                            <Button 
                                label="Add Quality Standard" 
                                icon="pi pi-plus"
                                severity="secondary"
                                outlined
                                size="small"
                                @click="addQualityStandard"
                            />
                        </div>
                    </div>

                    <!-- Status Section -->
                    <div class="pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="pi pi-cog mr-2"></i>
                            Status
                        </h3>
                        
                        <div class="flex items-center space-x-3">
                            <Checkbox 
                                v-model="form.is_active" 
                                inputId="is_active" 
                                :binary="true"
                            />
                            <label for="is_active" class="text-sm text-gray-700">
                                Brand is active and can be used
                            </label>
                        </div>
                        <small v-if="errors.is_active" class="text-red-500">{{ errors.is_active }}</small>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <Button 
                            type="button"
                            label="Cancel" 
                            severity="secondary"
                            outlined
                            @click="router.visit('/brands')"
                        />
                        <Button 
                            type="submit"
                            label="Update Brand" 
                            icon="pi pi-save"
                            :loading="form.processing"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </template>
        </Card>

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
                    :loading="isLoading"
                    @click="reloadBrand"
                />
                <Button 
                    label="Back to List" 
                    icon="pi pi-arrow-left" 
                    severity="secondary"
                    @click="router.visit('/brands')"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Head, router, usePage, useForm } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import { brandApi } from '@/utils/api'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'

const props = defineProps({
    brand: {
        type: Object,
        default: () => null
    },
    errors: Object
})

const page = usePage()
const toast = useToast()

// Loading states
const isLoading = ref(true)
const brand = ref(null)

// Get brand ID from URL
const getBrandId = () => {
    const path = window.location.pathname
    const matches = path.match(/\/brands\/(\d+)\/edit/)
    return matches ? parseInt(matches[1]) : null
}

// Load brand data via fetch API
const loadBrand = async (brandId) => {
    if (!brandId) return
    
    isLoading.value = true
    try {
        const response = await brandApi.getBrand(brandId)
        // Handle response structure (API returns data.data)
        brand.value = response.data?.data || response.data
        
        // Update form with loaded data
        updateFormWithBrandData(brand.value)
        
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
            updateFormWithBrandData(brand.value)
        }
    } finally {
        isLoading.value = false
    }
}

// Form data - use Inertia useForm for CSRF handling
const form = useForm({
    name: '',
    code: '',
    description: '',
    category: '',
    target_production_per_day: null,
    quality_standards: [],
    is_active: true
})

// Update form with brand data
const updateFormWithBrandData = (brandData) => {
    if (!brandData) return
    
    form.name = brandData.name || ''
    form.code = brandData.code || ''
    form.description = brandData.description || ''
    form.category = brandData.category || ''
    form.target_production_per_day = brandData.target_production_per_day || null
    form.quality_standards = brandData.quality_standards || []
    form.is_active = brandData.is_active !== undefined ? brandData.is_active : true
}

const errors = ref({})

// Quality standards management
const addQualityStandard = () => {
    form.quality_standards.push({
        name: '',
        value: ''
    })
}

const removeQualityStandard = (index) => {
    form.quality_standards.splice(index, 1)
}

// Submit form with Inertia (handles CSRF automatically)
const submit = () => {
    if (form.processing) return
    
    // Clear errors
    errors.value = {}
    
    form.put(`/brands/${brand.value.id}`, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Brand updated successfully',
                life: 3000
            })
            // Redirect to brand detail or list
            router.visit(`/brands/${brand.value.id}`)
        },
        onError: (formErrors) => {
            errors.value = formErrors
            toast.add({
                severity: 'warn',
                summary: 'Validation Error',
                detail: 'Please check the form fields and try again',
                life: 5000
            })
        },
        onFinish: () => {
            // Form processing will be automatically handled by Inertia
        }
    })
}

// Reload brand data
const reloadBrand = async () => {
    const brandId = getBrandId()
    if (brandId) {
        await loadBrand(brandId)
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
        updateFormWithBrandData(brand.value)
        isLoading.value = false
    } else {
        isLoading.value = false
    }
})
</script>
