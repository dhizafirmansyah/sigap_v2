<template>
    <Head title="Edit Location" />

    <AppLayout 
        :title="`Edit Location: ${location.name}`" 
        subtitle="Update location information and geographic data"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    icon="pi pi-eye"
                    label="View"
                    severity="info"
                    outlined
                    @click="router.visit(`/locations/${locationId}`)"
                />
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
                <!-- Loading State -->
                <div v-if="loading" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <i class="pi pi-spin pi-spinner text-4xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600">Loading location data...</p>
                    </div>
                </div>

                <!-- Edit Form -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">`
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

                            <!-- Usage Information -->
                            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                                <h3 class="text-sm font-medium text-gray-800 mb-2">
                                    Usage Information
                                </h3>
                                <div class="grid grid-cols-3 gap-4 text-sm text-gray-600">
                                    <div>
                                        <span class="font-medium">Employees:</span> {{ location.employees_count || 0 }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Packs:</span> {{ location.packs_count || 0 }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Kemas:</span> {{ location.kemas_count || 0 }}
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
                                    label="Update Location"
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
import { ref, onMounted } from 'vue';
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

// Get location ID from props or URL path
const locationId = props.location?.id || (() => {
    const pathSegments = window.location.pathname.split('/');
    const editIndex = pathSegments.findIndex(segment => segment === 'edit');
    return editIndex > 0 ? pathSegments[editIndex - 1] : null;
})()

// Props
const props = defineProps({
    location: {
        type: Object,
        required: true
    }
});

// Reactive data
const loading = ref(false);
const location = ref(props.location);

const form = useForm({
    name: props.location?.name || '',
    code: props.location?.code || '',
    latitude: props.location?.latitude || null,
    longitude: props.location?.longitude || null,
    radius: props.location?.radius || null,
    address: props.location?.address || '',
    description: props.location?.description || '',
    is_active: props.location?.is_active ?? true
});

// Methods
const fetchLocation = async () => {
    try {
        loading.value = true;
        console.log('Fetching location with ID:', locationId);
        const response = await locationApi.getLocation(locationId);
        location.value = response.data;
        
        // Update form with fresh data
        form.name = response.data.name || '';
        form.code = response.data.code || '';
        form.latitude = response.data.latitude || null;
        form.longitude = response.data.longitude || null;
        form.radius = response.data.radius || null;
        form.address = response.data.address || '';
        form.description = response.data.description || '';
        form.is_active = response.data.is_active ?? true;
        
    } catch (error) {
        console.error('Error fetching location:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to load location data',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};


const submitForm = () => {
    form.put(`/locations/${locationId}`, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Location updated successfully',
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

// Lifecycle
onMounted(() => {
    // Form is already populated from props
    // Only fetch if we want to ensure fresh data
    if (!props.location || !props.location.id) {
        fetchLocation();
    }
});
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
