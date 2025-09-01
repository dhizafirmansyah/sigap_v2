<template>
    <Head title="Import Data Kemas" />

    <AppLayout 
        title="Import Data Kemas" 
        subtitle="Upload file Excel untuk mengimpor data kemas"
        :user="page.props.auth?.user"
    >
        <template #headerActions>
            <div class="flex gap-3">
                <Button 
                    icon="pi pi-arrow-left"
                    label="Back to Kemas"
                    severity="secondary"
                    outlined
                    @click="router.visit('/kemas')"
                />
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Upload Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload File Excel</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <!-- File Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih File Excel (.xlsx, .xls)
                                </label>
                                <FileUpload
                                    mode="basic"
                                    name="file"
                                    accept=".xlsx,.xls"
                                    :maxFileSize="10000000"
                                    chooseLabel="Pilih File"
                                    @select="onFileSelect"
                                    class="w-full"
                                />
                                <small class="text-gray-500 mt-1">
                                    Format: BRAND | LOKASI | CELL | NO ID | SET | [FW-Scratched/Marked] | [FW-Tear/Slit/Hole] | [FW-Smeared/Dirty] | [FW-Seam Open] | [FW-Alignment] | [FW-Improper Fold] | [FW-Wrinkled/Pleated] | [FW-Crushed/Dented] | VFI ALL
                                </small>
                            </div>

                            <!-- Import Button -->
                            <div class="flex gap-3">
                                <Button
                                    icon="pi pi-upload"
                                    label="Import Preview"
                                    @click="importPreview"
                                    :loading="importing"
                                    :disabled="!selectedFile"
                                    severity="info"
                                />
                                <Button
                                    icon="pi pi-save"
                                    label="Save to Database"
                                    @click="saveData"
                                    :loading="saving"
                                    :disabled="!importedData.length"
                                    severity="success"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Data Table -->
                <div v-if="importedData.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Preview Data ({{ importedData.length }} rows)</h3>
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-refresh"
                                    label="Clear"
                                    severity="danger"
                                    outlined
                                    @click="clearData"
                                />
                            </div>
                        </div>
                        
                        <DataTable 
                            :value="importedData" 
                            scrollable
                            scrollHeight="500px"
                            :paginator="true"
                            :rows="50"
                            class="p-datatable-sm"
                        >
                            <Column field="brand" header="Brand" :style="{ minWidth: '120px' }" />
                            <Column field="lokasi" header="Lokasi" :style="{ minWidth: '120px' }" />
                            <Column field="cell" header="Cell" :style="{ minWidth: '80px' }" />
                            <Column field="no_id" header="No ID" :style="{ minWidth: '100px' }" />
                            <Column field="set" header="Set" :style="{ minWidth: '80px' }" />
                            <Column field="fw_scratched" header="FW-Scratched" :style="{ minWidth: '120px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_scratched" :severity="slotProps.data.fw_scratched > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_tear" header="FW-Tear" :style="{ minWidth: '100px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_tear" :severity="slotProps.data.fw_tear > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_smeared" header="FW-Smeared" :style="{ minWidth: '120px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_smeared" :severity="slotProps.data.fw_smeared > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_seam_open" header="FW-Seam Open" :style="{ minWidth: '130px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_seam_open" :severity="slotProps.data.fw_seam_open > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_alignment" header="FW-Alignment" :style="{ minWidth: '130px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_alignment" :severity="slotProps.data.fw_alignment > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_improper_fold" header="FW-Improper Fold" :style="{ minWidth: '150px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_improper_fold" :severity="slotProps.data.fw_improper_fold > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_wrinkled" header="FW-Wrinkled" :style="{ minWidth: '120px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_wrinkled" :severity="slotProps.data.fw_wrinkled > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="fw_crushed" header="FW-Crushed" :style="{ minWidth: '120px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.fw_crushed" :severity="slotProps.data.fw_crushed > 0 ? 'warning' : 'success'" />
                                </template>
                            </Column>
                            <Column field="vfi_all" header="VFI ALL" :style="{ minWidth: '100px' }">
                                <template #body="slotProps">
                                    <Tag :value="slotProps.data.vfi_all" :severity="slotProps.data.vfi_all > 0 ? 'danger' : 'success'" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <!-- Error Display -->
                <div v-if="errorData.length > 0" class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="pi pi-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Error saat import
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li v-for="error in errorData" :key="error">{{ error }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Layouts/AppLayout.vue';

// PrimeVue Components
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const page = usePage();
const toast = useToast();

// Reactive data
const selectedFile = ref(null);
const importing = ref(false);
const saving = ref(false);
const importedData = ref([]);
const errorData = ref([]);

// Methods
const onFileSelect = (event) => {
    selectedFile.value = event.files[0];
    // Clear previous data when new file selected
    importedData.value = [];
    errorData.value = [];
};

const importPreview = async () => {
    if (!selectedFile.value) {
        toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: 'Pilih file Excel terlebih dahulu',
            life: 3000
        });
        return;
    }

    importing.value = true;
    errorData.value = [];

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        const response = await fetch('/kemas/import-preview', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': page.props.csrf_token
            }
        });

        const result = await response.json();
        
        if (response.ok) {
            importedData.value = result.data;
            
            // Check for error messages in data
            const errors = result.data.filter(row => row.error_message);
            if (errors.length > 0) {
                errorData.value = errors.map(error => error.error_message);
            }
            
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: `${result.data.length} baris data berhasil diimport untuk preview`,
                life: 3000
            });
        } else {
            throw new Error(result.message || 'Gagal import file');
        }
    } catch (error) {
        console.error('Import error:', error);
        errorData.value = [error.message];
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Gagal import file: ' + error.message,
            life: 5000
        });
    } finally {
        importing.value = false;
    }
};

const saveData = async () => {
    if (importedData.value.length === 0) {
        toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: 'Tidak ada data untuk disimpan',
            life: 3000
        });
        return;
    }

    saving.value = true;

    try {
        // Filter out error rows
        const validData = importedData.value.filter(row => !row.error_message);
        
        const response = await fetch('/kemas/save-imported', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': page.props.csrf_token
            },
            body: JSON.stringify({
                rows: validData
            })
        });

        const result = await response.json();
        
        if (response.ok) {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: `${validData.length} data berhasil disimpan ke database`,
                life: 3000
            });
            
            // Clear data after successful save
            clearData();
        } else {
            throw new Error(result.message || 'Gagal menyimpan data');
        }
    } catch (error) {
        console.error('Save error:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Gagal menyimpan data: ' + error.message,
            life: 5000
        });
    } finally {
        saving.value = false;
    }
};

const clearData = () => {
    importedData.value = [];
    errorData.value = [];
    selectedFile.value = null;
};
</script>

<style scoped>
.p-datatable-sm {
    font-size: 0.875rem;
}

.p-datatable-sm .p-datatable-thead > tr > th {
    padding: 0.5rem;
}

.p-datatable-sm .p-datatable-tbody > tr > td {
    padding: 0.5rem;
}
</style>
