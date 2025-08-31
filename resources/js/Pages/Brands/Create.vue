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
                                    :class="{'border-red-500': form.errors.name}"
                                    placeholder="Enter brand name"
                                    class="w-full"
                                />
                                <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                            </div>

                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Brand Code <span class="text-red-500">*</span>
                                </label>
                                <InputText
                                    id="code"
                                    v-model="form.code"
                                    :class="{'border-red-500': form.errors.code}"
                                    placeholder="e.g. BRD001"
                                    class="w-full"
                                />
                                <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>
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
                                    :class="{'border-red-500': form.errors.category}"
                                    placeholder="Brand category"
                                    class="w-full"
                                />
                                <small v-if="form.errors.category" class="text-red-500">{{ form.errors.category }}</small>
                            </div>

                            <div>
                                <label for="target_production_per_day" class="block text-sm font-medium text-gray-700 mb-2">
                                    Daily Production Target
                                </label>
                                <InputNumber
                                    id="target_production_per_day"
                                    v-model="form.target_production_per_day"
                                    :class="{'border-red-500': form.errors.target_production_per_day}"
                                    placeholder="Units per day"
                                    class="w-full"
                                    :min="0"
                                    :maxFractionDigits="2"
                                />
                                <small v-if="form.errors.target_production_per_day" class="text-red-500">{{ form.errors.target_production_per_day }}</small>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                :class="{'border-red-500': form.errors.description}"
                                placeholder="Enter brand description (optional)"
                                rows="4"
                                class="w-full"
                            />
                            <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
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
                        <small v-if="form.errors.is_active" class="text-red-500">{{ form.errors.is_active }}</small>
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
                            :loading="form.processing"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </template>
        </Card>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Checkbox from 'primevue/checkbox'

const page = usePage()
const toast = useToast()

// Use Inertia form for CSRF protection
const form = useForm({
    name: '',
    code: '',
    description: '',
    category: '',
    target_production_per_day: null,
    quality_standards: [],
    is_active: true
})

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

// Submit form using Inertia
const submit = () => {
    form.post('/brands', {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Brand created successfully',
                life: 3000
            })
        },
        onError: (errors) => {
            toast.add({
                severity: 'warn',
                summary: 'Validation Error',
                detail: 'Please check the form fields and try again',
                life: 5000
            })
        }
    })
}
</script>
