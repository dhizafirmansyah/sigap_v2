<template>
    <Head title="Add New Location" />

    <AppLayout 
        title="Add New Location" 
        subtitle="Create a new location with geographic information"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    icon="pi pi-arrow-left"
                    label="Back to Locations"
                    severity="secondary"
                    outlined
                    @click="router.visit('/locations')"
                />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Name <span class="text-red-500">*</span>
                                    </label>
                                    <InputText
                                        id="name"
                                        v-model="form.name"
                                        :class="{ 'p-invalid': form.errors.name }"
                                        placeholder="Enter location name"
                                        class="w-full"
                                    />
                                    <small v-if="form.errors.name" class="p-error">{{ form.errors.name }}</small>
                                </div>

                                <!-- Code -->
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Code <span class="text-red-500">*</span>
                                    </label>
                                    <InputText
                                        id="code"
                                        v-model="form.code"
                                        :class="{ 'p-invalid': form.errors.code }"
                                        placeholder="Enter location code"
                                        class="w-full"
                                    />
                                    <small v-if="form.errors.code" class="p-error">{{ form.errors.code }}</small>
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Address
                                </label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    :class="{ 'p-invalid': form.errors.address }"
                                    placeholder="Enter full address"
                                    rows="3"
                                    class="w-full"
                                />
                                <small v-if="form.errors.address" class="p-error">{{ form.errors.address }}</small>
                            </div>

                            <!-- Coordinates & Radius -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Latitude -->
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Latitude
                                    </label>
                                    <InputNumber
                                        id="latitude"
                                        v-model="form.latitude"
                                        :class="{ 'p-invalid': form.errors.latitude }"
                                        placeholder="e.g., -6.200000"
                                        :min="-90"
                                        :max="90"
                                        :maxFractionDigits="6"
                                        class="w-full"
                                    />
                                    <small v-if="form.errors.latitude" class="p-error">{{ form.errors.latitude }}</small>
                                </div>

                                <!-- Longitude -->
                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                                        Longitude
                                    </label>
                                    <InputNumber
                                        id="longitude"
                                        v-model="form.longitude"
                                        :class="{ 'p-invalid': form.errors.longitude }"
                                        placeholder="e.g., 106.816666"
                                        :min="-180"
                                        :max="180"
                                        :maxFractionDigits="6"
                                        class="w-full"
                                    />
                                    <small v-if="form.errors.longitude" class="p-error">{{ form.errors.longitude }}</small>
                                </div>

                                <!-- Radius -->
                                <div>
                                    <label for="radius" class="block text-sm font-medium text-gray-700 mb-2">
                                        Radius (meters)
                                    </label>
                                    <InputNumber
                                        id="radius"
                                        v-model="form.radius"
                                        :class="{ 'p-invalid': form.errors.radius }"
                                        placeholder="e.g., 100"
                                        :min="0"
                                        suffix=" m"
                                        class="w-full"
                                    />
                                    <small v-if="form.errors.radius" class="p-error">{{ form.errors.radius }}</small>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    :class="{ 'p-invalid': form.errors.description }"
                                    placeholder="Enter description (optional)"
                                    rows="4"
                                    class="w-full"
                                />
                                <small v-if="form.errors.description" class="p-error">{{ form.errors.description }}</small>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <div class="flex items-center">
                                    <Checkbox
                                        id="is_active"
                                        v-model="form.is_active"
                                        :binary="true"
                                    />
                                    <label for="is_active" class="ml-2 text-sm text-gray-600">
                                        Active location
                                    </label>
                                </div>
                            </div>

                            <!-- Coordinate Helper -->
                            <div v-if="form.latitude || form.longitude" class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="pi pi-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">
                                            Coordinate Information
                                        </h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p v-if="form.latitude && form.longitude">
                                                Location: {{ form.latitude }}, {{ form.longitude }}
                                                <br>
                                                <a 
                                                    :href="`https://www.google.com/maps?q=${form.latitude},${form.longitude}`"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-800 underline"
                                                >
                                                    View on Google Maps
                                                </a>
                                            </p>
                                            <p v-else>
                                                Please enter both latitude and longitude to see map preview.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                                <Button
                                    icon="pi pi-times"
                                    label="Cancel"
                                    severity="secondary"
                                    outlined
                                    @click="router.visit('/locations')"
                                />
                                <Button
                                    type="submit"
                                    label="Save Location"
                                    icon="pi pi-check"
                                    :loading="form.processing"
                                    :disabled="form.processing"
                                />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Layouts/AppLayout.vue';
import { locationApi } from '@/utils/api';

// PrimeVue Components
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';

const page = usePage()
const toast = useToast()

// Reactive data
const form = useForm({
    name: '',
    code: '',
    latitude: null,
    longitude: null,
    radius: null,
    address: '',
    description: '',
    is_active: true
});

// Methods
const submitForm = () => {
    form.post('/locations', {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Location created successfully',
                life: 3000
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Validation Error',
                detail: 'Please check the form for errors',
                life: 3000
            });
        }
    });
};

// Auto-generate code from name
const generateCode = () => {
    if (form.name && !form.code) {
        form.code = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '')
            .substring(0, 20);
    }
};

// Watch name changes to auto-generate code
import { watch } from 'vue';
watch(() => form.name, generateCode);
</script>

<style scoped>
.p-error {
    color: #e24c4c;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
}

.p-invalid {
    border-color: #e24c4c;
}
</style>
