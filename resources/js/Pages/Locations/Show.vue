<template>
    <Head :title="`Location: ${location.name}`" />

    <AppLayout 
        :title="`Location: ${location.name}`" 
        subtitle="View detailed location information and usage statistics"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    icon="pi pi-pencil"
                    label="Edit"
                    severity="warning"
                    outlined
                    @click="router.visit(`/locations/${locationId}/edit`)"
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
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Loading State -->
                <div v-if="loading" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <i class="pi pi-spin pi-spinner text-4xl text-blue-500 mb-4"></i>
                        <p class="text-gray-600">Loading location details...</p>
                    </div>
                </div>

                <!-- Location Details -->
                <template v-else>
                <!-- Basic Information Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                                <p class="text-base text-gray-900">{{ location.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Code</label>
                                <p class="text-base text-gray-900">{{ location.code }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Address</label>
                                <p class="text-base text-gray-900">{{ location.address || '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                                <p class="text-base text-gray-900">{{ location.description || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                                <Tag
                                    :value="location.is_active ? 'Active' : 'Inactive'"
                                    :severity="location.is_active ? 'success' : 'danger'"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Information Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Location Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Latitude</label>
                                <p class="text-base text-gray-900">
                                    {{ location.latitude ? parseFloat(location.latitude).toFixed(6) : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Longitude</label>
                                <p class="text-base text-gray-900">
                                    {{ location.longitude ? parseFloat(location.longitude).toFixed(6) : '-' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Radius</label>
                                <p class="text-base text-gray-900">
                                    {{ location.radius ? `${location.radius} meters` : '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Map Link -->
                        <div v-if="location.latitude && location.longitude" class="mt-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <i class="pi pi-map-marker text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-blue-800">
                                            Map Location
                                        </h4>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p>Coordinates: {{ location.latitude }}, {{ location.longitude }}</p>
                                            <div class="mt-2 space-x-4">
                                                <a 
                                                    :href="`https://www.google.com/maps?q=${location.latitude},${location.longitude}`"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-800 underline"
                                                >
                                                    View on Google Maps
                                                </a>
                                                <a 
                                                    :href="`https://www.openstreetmap.org/?mlat=${location.latitude}&mlon=${location.longitude}&zoom=15`"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-800 underline"
                                                >
                                                    View on OpenStreetMap
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Statistics Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Usage Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="pi pi-users text-blue-600 text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-blue-600">Employees</p>
                                        <p class="text-2xl font-bold text-blue-900">{{ location.employees_count || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="pi pi-box text-green-600 text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-green-600">Packs</p>
                                        <p class="text-2xl font-bold text-green-900">{{ location.packs_count || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="pi pi-briefcase text-purple-600 text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-purple-600">Kemas</p>
                                        <p class="text-2xl font-bold text-purple-900">{{ location.kemas_count || 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metadata Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Metadata</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Created At</label>
                                <p class="text-base text-gray-900">{{ location.formatted_created_at || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                                <p class="text-base text-gray-900">{{ location.formatted_updated_at || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        <div class="flex flex-wrap gap-3">
                            <Button
                                icon="pi pi-pencil"
                                label="Edit Location"
                                severity="warning"
                                @click="router.visit(`/locations/${locationId}/edit`)"
                            />
                            <Button
                                icon="pi pi-trash"
                                label="Delete Location"
                                severity="danger"
                                @click="confirmDelete"
                                :disabled="hasUsage"
                            />
                            <Button
                                icon="pi pi-plus"
                                label="Create New Location"
                                @click="router.visit('/locations/create')"
                                severity="success"
                            />
                        </div>
                        <div v-if="hasUsage" class="mt-2">
                            <small class="text-orange-600">
                                <i class="pi pi-exclamation-triangle mr-1"></i>
                                This location cannot be deleted because it is being used by {{ totalUsage }} item(s).
                            </small>
                        </div>
                    </div>
                </div>
                </template>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            v-model:visible="showDeleteDialog"
            :style="{width: '450px'}"
            header="Confirm Deletion"
            :modal="true"
            class="p-fluid"
        >
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>
                    Are you sure you want to delete <strong>{{ location.name }}</strong>?
                    This action cannot be undone.
                </span>
            </div>
            <template #footer>
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    @click="showDeleteDialog = false"
                    :disabled="deleting"
                    class="p-button-text"
                />
                <Button
                    label="Delete"
                    icon="pi pi-check"
                    @click="deleteLocation"
                    :loading="deleting"
                    severity="danger"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Layouts/AppLayout.vue';
import { locationApi } from '@/utils/api';

// PrimeVue Components
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const page = usePage()
const toast = useToast()

// Get location ID from route
const locationId = page.props.location?.id || window.location.pathname.split('/').pop()

// Props
const props = defineProps({
    location: {
        type: Object,
        required: true
    }
});

// Reactive data
const showDeleteDialog = ref(false);
const deleting = ref(false);
const loading = ref(false);
const location = ref(props.location);

// Methods
const fetchLocation = async () => {
    try {
        loading.value = true;
        console.log('Fetching location with ID:', locationId);
        const response = await locationApi.getLocation(locationId);
        location.value = response.data;
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

// Computed
const hasUsage = computed(() => {
    return (location.value.employees_count > 0) || 
           (location.value.packs_count > 0) || 
           (location.value.kemas_count > 0);
});

const totalUsage = computed(() => {
    return (location.value.employees_count || 0) + 
           (location.value.packs_count || 0) + 
           (location.value.kemas_count || 0);
});

// Methods
const confirmDelete = () => {
    showDeleteDialog.value = true;
};

const deleteLocation = async () => {
    try {
        deleting.value = true;
        await locationApi.deleteLocation(locationId);
        
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'Location deleted successfully',
            life: 3000
        });

        // Redirect to index
        router.visit('/locations');
        
    } catch (error) {
        console.error('Error deleting location:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to delete location',
            life: 3000
        });
    } finally {
        deleting.value = false;
        showDeleteDialog.value = false;
    }
};

// Lifecycle
onMounted(() => {
    // Use prop data initially, then fetch fresh data
    fetchLocation();
});
</script>

<style scoped>
.confirmation-content {
    display: flex;
    align-items: center;
}
</style>
