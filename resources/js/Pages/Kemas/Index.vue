<template>
    <Head title="Data Kemas" />

    <AppLayout 
        title="Data Kemas" 
        subtitle="Kelola data kemas dan quality control"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    icon="pi pi-upload"
                    label="Import Data"
                    @click="router.visit('/kemas/import')"
                />
                <Button 
                    icon="pi pi-refresh"
                    label="Refresh"
                    severity="secondary"
                    outlined
                    @click="refreshData"
                />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Brand</label>
                                <InputText 
                                    v-model="filters.brand" 
                                    placeholder="Filter by brand..."
                                    class="w-full"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                                <InputText 
                                    v-model="filters.lokasi" 
                                    placeholder="Filter by lokasi..."
                                    class="w-full"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cell</label>
                                <InputText 
                                    v-model="filters.cell" 
                                    placeholder="Filter by cell..."
                                    class="w-full"
                                />
                            </div>
                            <div class="flex items-end">
                                <Button 
                                    label="Clear Filters" 
                                    severity="secondary" 
                                    outlined
                                    @click="clearFilters"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <DataTable 
                            :value="filteredData" 
                            :paginator="true" 
                            :rows="10"
                            :rowsPerPageOptions="[5, 10, 20, 50]"
                            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} entries"
                            :loading="loading"
                            scrollable
                            scrollHeight="600px"
                            responsiveLayout="scroll"
                        >
                            <template #loading>
                                <div class="flex items-center justify-center p-8">
                                    <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
                                    <span class="ml-2">Loading data...</span>
                                </div>
                            </template>
                            
                            <template #empty>
                                <div class="text-center p-8">
                                    <i class="pi pi-inbox text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-gray-500">No data available</p>
                                    <Button 
                                        label="Import Data" 
                                        icon="pi pi-upload"
                                        @click="router.visit('/kemas/import')"
                                        class="mt-4"
                                    />
                                </div>
                            </template>

                            <Column field="brand" header="Brand" sortable style="min-width:120px">
                                <template #body="{ data }">
                                    <Tag :value="data.brand" severity="info" />
                                </template>
                            </Column>
                            
                            <Column field="lokasi" header="Lokasi" sortable style="min-width:120px" />
                            
                            <Column field="cell" header="Cell" sortable style="min-width:100px" />
                            
                            <Column field="no_id" header="No ID" sortable style="min-width:100px" />
                            
                            <Column field="set" header="Set" sortable style="min-width:80px" />
                            
                            <Column header="Quality Issues" style="min-width:300px">
                                <template #body="{ data }">
                                    <div class="grid grid-cols-2 gap-1 text-xs">
                                        <span v-if="data.fw_scratched" class="bg-red-100 text-red-800 px-1 rounded">Scratched</span>
                                        <span v-if="data.fw_tear" class="bg-red-100 text-red-800 px-1 rounded">Tear</span>
                                        <span v-if="data.fw_smeared" class="bg-orange-100 text-orange-800 px-1 rounded">Smeared</span>
                                        <span v-if="data.fw_seam_open" class="bg-red-100 text-red-800 px-1 rounded">Seam Open</span>
                                        <span v-if="data.fw_alignment" class="bg-yellow-100 text-yellow-800 px-1 rounded">Alignment</span>
                                        <span v-if="data.fw_improper_fold" class="bg-yellow-100 text-yellow-800 px-1 rounded">Improper Fold</span>
                                        <span v-if="data.fw_wrinkled" class="bg-orange-100 text-orange-800 px-1 rounded">Wrinkled</span>
                                        <span v-if="data.fw_crushed" class="bg-red-100 text-red-800 px-1 rounded">Crushed</span>
                                    </div>
                                </template>
                            </Column>
                            
                            <Column field="vfi_all" header="VFI All" sortable style="min-width:100px">
                                <template #body="{ data }">
                                    <Tag 
                                        :value="data.vfi_all" 
                                        :severity="data.vfi_all === 'OK' ? 'success' : 'danger'" 
                                    />
                                </template>
                            </Column>
                            
                            <Column field="created_at" header="Created" sortable style="min-width:150px">
                                <template #body="{ data }">
                                    {{ new Date(data.created_at).toLocaleDateString() }}
                                </template>
                            </Column>
                            
                            <Column header="Actions" style="min-width:120px">
                                <template #body="{ data }">
                                    <div class="flex gap-2">
                                        <Button 
                                            icon="pi pi-eye"
                                            severity="info"
                                            outlined
                                            size="small"
                                            @click="viewDetail(data)"
                                        />
                                        <Button 
                                            icon="pi pi-trash"
                                            severity="danger"
                                            outlined
                                            size="small"
                                            @click="confirmDelete(data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            v-model:visible="showDeleteDialog"
            :style="{width: '450px'}"
            header="Confirm Deletion"
            :modal="true"
        >
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>
                    Are you sure you want to delete this record?
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
                    @click="deleteRecord"
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

// PrimeVue Components
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';

const page = usePage();
const toast = useToast();

// Props
const props = defineProps({
    kemas: {
        type: Array,
        default: () => []
    }
});

// Reactive data
const loading = ref(false);
const showDeleteDialog = ref(false);
const deleting = ref(false);
const selectedRecord = ref(null);

const filters = ref({
    brand: '',
    lokasi: '',
    cell: ''
});

// Computed
const filteredData = computed(() => {
    let data = props.kemas;
    
    if (filters.value.brand) {
        data = data.filter(item => 
            item.brand.toLowerCase().includes(filters.value.brand.toLowerCase())
        );
    }
    
    if (filters.value.lokasi) {
        data = data.filter(item => 
            item.lokasi.toLowerCase().includes(filters.value.lokasi.toLowerCase())
        );
    }
    
    if (filters.value.cell) {
        data = data.filter(item => 
            item.cell.toLowerCase().includes(filters.value.cell.toLowerCase())
        );
    }
    
    return data;
});

// Methods
const refreshData = () => {
    router.reload({ only: ['kemas'] });
};

const clearFilters = () => {
    filters.value = {
        brand: '',
        lokasi: '',
        cell: ''
    };
};

const viewDetail = (data) => {
    // TODO: Implement view detail
    toast.add({
        severity: 'info',
        summary: 'Info',
        detail: 'View detail functionality will be implemented',
        life: 3000
    });
};

const confirmDelete = (data) => {
    selectedRecord.value = data;
    showDeleteDialog.value = true;
};

const deleteRecord = () => {
    if (!selectedRecord.value) return;
    
    deleting.value = true;
    
    router.delete(`/kemas/${selectedRecord.value.id}`, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Record deleted successfully',
                life: 3000
            });
            showDeleteDialog.value = false;
            selectedRecord.value = null;
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Failed to delete record',
                life: 3000
            });
        },
        onFinish: () => {
            deleting.value = false;
        }
    });
};

// Lifecycle
onMounted(() => {
    console.log('Kemas data:', props.kemas);
});
</script>

<style scoped>
.confirmation-content {
    display: flex;
    align-items: center;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
    color: #374151;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #f9fafb;
}
</style>
