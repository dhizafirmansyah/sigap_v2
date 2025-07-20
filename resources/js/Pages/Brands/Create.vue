<template>
    <Head title="Create Brand" />
    
    <AppLayout 
        title="Create Brand" 
        subtitle="Add new brand to the system"
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

        <!-- Form -->
        <Card>
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
                            label="Save Brand" 
                            icon="pi pi-save"
                            :loading="processing"
                            :disabled="processing"
                        />
                    </div>
                </form>
            </template>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
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
    errors: Object
})

const page = usePage()
const toast = useToast()

// Form data with reactive handling
const form = reactive({
    name: '',
    code: '',
    description: '',
    category: '',
    target_production_per_day: null,
    quality_standards: [],
    is_active: true
})

const processing = ref(false)
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

// Submit form with API helper
const submit = async () => {
    if (processing.value) return
    
    processing.value = true
    errors.value = {}
    
    try {
        const { data } = await brandApi.createBrand(form)
        
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Brand created successfully',
            life: 3000
        })
        
        // Redirect to brand list or detail
        setTimeout(() => {
            window.location.href = '/brands'
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
                detail: error.message || 'An error occurred while saving the brand',
                life: 3000
            })
        }
    } finally {
        processing.value = false
    }
}
</script>
