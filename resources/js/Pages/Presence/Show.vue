<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Presence Details</h1>
          <p class="text-sm text-gray-600 mt-1">
            {{ presence.employee?.name }} - {{ formatDate(presence.check_in) }}
          </p>
        </div>
        <div class="flex gap-3">
          <Button @click="$inertia.visit(route('presences.index'))" outlined>
            <i class="pi pi-arrow-left mr-2"></i>
            Back to List
          </Button>
          <Button 
            v-if="canEdit('presences')"
            @click="editPresence"
            severity="warning"
          >
            <i class="pi pi-pencil mr-2"></i>
            Edit
          </Button>
          <Button 
            v-if="canEdit('presences') && !presence.check_out"
            @click="checkOut"
            severity="success"
          >
            <i class="pi pi-sign-out mr-2"></i>
            Check Out
          </Button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Employee & Shift Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Employee & Shift Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Employee Info -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Employee Details</h4>
              <div class="space-y-3">
                <div class="flex items-center">
                  <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                    <i class="pi pi-user text-gray-500 text-lg"></i>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ presence.employee?.name }}</div>
                    <div class="text-sm text-gray-500">ID: {{ presence.employee?.employee_id }}</div>
                  </div>
                </div>
                <div class="text-sm text-gray-600">
                  <strong>Email:</strong> {{ presence.employee?.email || 'Not provided' }}
                </div>
                <div class="text-sm text-gray-600">
                  <strong>Phone:</strong> {{ presence.employee?.phone || 'Not provided' }}
                </div>
              </div>
            </div>

            <!-- Shift Info -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Shift Details</h4>
              <div class="space-y-3">
                <div class="flex items-center">
                  <Badge
                    :value="getShiftType(presence.shift?.start_time)"
                    :severity="getShiftTypeSeverity(presence.shift?.start_time)"
                    class="mr-3"
                  />
                  <div>
                    <div class="font-medium text-gray-900">{{ presence.shift?.name }}</div>
                    <div class="text-sm text-gray-500">{{ presence.shift?.description }}</div>
                  </div>
                </div>
                <div class="text-sm text-gray-600">
                  <strong>Schedule:</strong> 
                  {{ formatTime(presence.shift?.start_time) }} - {{ formatTime(presence.shift?.end_time) }}
                </div>
                <div class="text-sm text-gray-600">
                  <strong>Duration:</strong> {{ presence.shift?.duration_hours }}h
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance Timeline -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Attendance Timeline</h3>
          
          <div class="space-y-4">
            <!-- Check-in Event -->
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <i class="pi pi-sign-in text-green-600 text-sm"></i>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                  <h4 class="text-sm font-medium text-gray-900">Check In</h4>
                  <span class="text-sm text-gray-500">{{ formatDateTime(presence.check_in) }}</span>
                </div>
                <div class="mt-1 flex items-center gap-4">
                  <Badge
                    :value="presence.is_late ? 'Late' : 'On Time'"
                    :severity="presence.is_late ? 'warning' : 'success'"
                  />
                  <span v-if="presence.is_late" class="text-sm text-orange-600">
                    {{ calculateLateDuration(presence.check_in, presence.shift?.start_time) }} minutes late
                  </span>
                </div>
                <p v-if="presence.notes_checkin" class="mt-2 text-sm text-gray-600">
                  <strong>Notes:</strong> {{ presence.notes_checkin }}
                </p>
              </div>
            </div>

            <!-- Check-out Event -->
            <div v-if="presence.check_out" class="flex items-start">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <i class="pi pi-sign-out text-blue-600 text-sm"></i>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                  <h4 class="text-sm font-medium text-gray-900">Check Out</h4>
                  <span class="text-sm text-gray-500">{{ formatDateTime(presence.check_out) }}</span>
                </div>
                <div class="mt-1 flex items-center gap-4">
                  <Badge
                    :value="presence.is_early_checkout ? 'Early' : 'Regular'"
                    :severity="presence.is_early_checkout ? 'info' : 'success'"
                  />
                  <span v-if="presence.is_early_checkout" class="text-sm text-blue-600">
                    {{ calculateEarlyDuration(presence.check_out, presence.shift?.end_time) }} minutes early
                  </span>
                </div>
                <p v-if="presence.notes_checkout" class="mt-2 text-sm text-gray-600">
                  <strong>Notes:</strong> {{ presence.notes_checkout }}
                </p>
              </div>
            </div>

            <!-- Current Status -->
            <div v-else class="flex items-start">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <i class="pi pi-clock text-yellow-600 text-sm"></i>
                </div>
              </div>
              <div class="ml-4 flex-1">
                <h4 class="text-sm font-medium text-gray-900">Currently Working</h4>
                <div class="mt-1">
                  <Badge value="Active" severity="info" />
                </div>
                <p class="mt-2 text-sm text-gray-600">
                  Employee has not checked out yet
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Location Information -->
        <div v-if="presence.check_in_lat || presence.check_out_lat" class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Location Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Check-in Location -->
            <div v-if="presence.check_in_lat">
              <h4 class="text-sm font-medium text-gray-500 mb-3">Check-in Location</h4>
              <div class="space-y-2">
                <div class="text-sm text-gray-600">
                  <strong>Coordinates:</strong><br>
                  {{ presence.check_in_lat }}, {{ presence.check_in_lng }}
                </div>
                <Button 
                  @click="openMap(presence.check_in_lat, presence.check_in_lng, 'Check-in')"
                  size="small"
                  outlined
                >
                  <i class="pi pi-map-marker mr-2"></i>
                  View on Map
                </Button>
              </div>
            </div>

            <!-- Check-out Location -->
            <div v-if="presence.check_out_lat">
              <h4 class="text-sm font-medium text-gray-500 mb-3">Check-out Location</h4>
              <div class="space-y-2">
                <div class="text-sm text-gray-600">
                  <strong>Coordinates:</strong><br>
                  {{ presence.check_out_lat }}, {{ presence.check_out_lng }}
                </div>
                <Button 
                  @click="openMap(presence.check_out_lat, presence.check_out_lng, 'Check-out')"
                  size="small"
                  outlined
                >
                  <i class="pi pi-map-marker mr-2"></i>
                  View on Map
                </Button>
              </div>
            </div>
          </div>
        </div>

        <!-- General Notes -->
        <div v-if="presence.notes" class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">General Notes</h3>
          <p class="text-gray-700">{{ presence.notes }}</p>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Status & Statistics -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Statistics</h3>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Status</span>
              <Badge
                :value="presence.status"
                :severity="getStatusSeverity(presence.status)"
                class="capitalize"
              />
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Work Hours</span>
              <span class="text-sm font-medium text-gray-900">
                {{ presence.work_hours || '0' }}h
              </span>
            </div>

            <div v-if="presence.overtime_hours > 0" class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Overtime</span>
              <span class="text-sm font-medium text-blue-600">
                +{{ presence.overtime_hours }}h
              </span>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-500">Break Duration</span>
              <span class="text-sm font-medium text-gray-900">
                {{ presence.break_duration || '0' }}m
              </span>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-500">Created</span>
                <span class="text-sm text-gray-600">
                  {{ formatDateTime(presence.created_at) }}
                </span>
              </div>
              <div v-if="presence.updated_at !== presence.created_at" class="flex items-center justify-between mt-2">
                <span class="text-sm font-medium text-gray-500">Last Updated</span>
                <span class="text-sm text-gray-600">
                  {{ formatDateTime(presence.updated_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          
          <div class="space-y-3">
            <Button 
              v-if="canEdit('presences') && !presence.check_out"
              @click="checkOut"
              class="w-full"
              severity="success"
            >
              <i class="pi pi-sign-out mr-2"></i>
              Check Out Employee
            </Button>

            <Button 
              v-if="canEdit('presences')"
              @click="editPresence"
              class="w-full"
              severity="warning"
              outlined
            >
              <i class="pi pi-pencil mr-2"></i>
              Edit Presence
            </Button>

            <Button 
              @click="generateReport"
              class="w-full"
              outlined
            >
              <i class="pi pi-download mr-2"></i>
              Download Report
            </Button>

            <Button 
              v-if="canDelete('presences')"
              @click="deletePresence"
              class="w-full"
              severity="danger"
              outlined
            >
              <i class="pi pi-trash mr-2"></i>
              Delete Presence
            </Button>
          </div>
        </div>

        <!-- Photo Evidence -->
        <div v-if="presence.check_in_photo || presence.check_out_photo" class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Photo Evidence</h3>
          
          <div class="space-y-4">
            <div v-if="presence.check_in_photo">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Check-in Photo</h4>
              <div class="aspect-w-16 aspect-h-9">
                <img 
                  :src="presence.check_in_photo" 
                  alt="Check-in photo"
                  class="w-full h-32 object-cover rounded border"
                  @click="viewPhoto(presence.check_in_photo, 'Check-in Photo')"
                />
              </div>
            </div>

            <div v-if="presence.check_out_photo">
              <h4 class="text-sm font-medium text-gray-500 mb-2">Check-out Photo</h4>
              <div class="aspect-w-16 aspect-h-9">
                <img 
                  :src="presence.check_out_photo" 
                  alt="Check-out photo"
                  class="w-full h-32 object-cover rounded border"
                  @click="viewPhoto(presence.check_out_photo, 'Check-out Photo')"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Photo Modal -->
    <Dialog
      v-model:visible="showPhotoModal"
      modal
      :header="photoModalTitle"
      class="w-full max-w-2xl"
    >
      <img :src="selectedPhoto" alt="Photo evidence" class="w-full" />
    </Dialog>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { format, differenceInMinutes } from 'date-fns'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePermissions } from '@/composables/usePermissions'

// PrimeVue Components
import Button from 'primevue/button'
import Badge from 'primevue/badge'
import Dialog from 'primevue/dialog'

// Props
const props = defineProps({
  presence: Object
})

// Permissions
const { canEdit, canDelete } = usePermissions()

// Modal state
const showPhotoModal = ref(false)
const selectedPhoto = ref('')
const photoModalTitle = ref('')

// Methods
const editPresence = () => {
  router.get(route('presences.edit', props.presence.id))
}

const checkOut = () => {
  if (confirm(`Check out ${props.presence.employee?.name}?`)) {
    router.post(route('presences.check-out', props.presence.id), {
      notes_checkout: 'Manual check-out from details page'
    })
  }
}

const deletePresence = () => {
  if (confirm(`Are you sure you want to delete this presence record for ${props.presence.employee?.name}?`)) {
    router.delete(route('presences.destroy', props.presence.id))
  }
}

const generateReport = () => {
  window.open(route('presences.report', props.presence.id), '_blank')
}

const viewPhoto = (photoUrl, title) => {
  selectedPhoto.value = photoUrl
  photoModalTitle.value = title
  showPhotoModal.value = true
}

const openMap = (lat, lng, title) => {
  const url = `https://www.google.com/maps?q=${lat},${lng}&z=15&t=m`
  window.open(url, '_blank')
}

// Utility functions
const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return format(new Date(datetime), 'dd/MM/yyyy HH:mm')
}

const formatDate = (datetime) => {
  if (!datetime) return '-'
  return format(new Date(datetime), 'dd MMMM yyyy')
}

const formatTime = (time) => {
  if (!time) return '-'
  return format(new Date(`2000-01-01 ${time}`), 'HH:mm')
}

const getShiftType = (startTime) => {
  if (!startTime) return 'Unknown'
  const hour = new Date(`2000-01-01 ${startTime}`).getHours()
  
  if (hour >= 6 && hour < 14) return 'Morning'
  if (hour >= 14 && hour < 22) return 'Afternoon'
  return 'Night'
}

const getShiftTypeSeverity = (startTime) => {
  const type = getShiftType(startTime)
  switch (type) {
    case 'Morning': return 'info'
    case 'Afternoon': return 'warning'
    case 'Night': return 'secondary'
    default: return null
  }
}

const getStatusSeverity = (status) => {
  switch (status) {
    case 'present': return 'success'
    case 'late': return 'warning'
    case 'absent': return 'danger'
    case 'partial': return 'info'
    default: return null
  }
}

const calculateLateDuration = (checkIn, shiftStart) => {
  if (!checkIn || !shiftStart) return 0
  
  const checkInTime = new Date(checkIn)
  const today = format(checkInTime, 'yyyy-MM-dd')
  const shiftStartTime = new Date(`${today} ${shiftStart}`)
  
  return Math.max(0, differenceInMinutes(checkInTime, shiftStartTime))
}

const calculateEarlyDuration = (checkOut, shiftEnd) => {
  if (!checkOut || !shiftEnd) return 0
  
  const checkOutTime = new Date(checkOut)
  const today = format(checkOutTime, 'yyyy-MM-dd')
  const shiftEndTime = new Date(`${today} ${shiftEnd}`)
  
  return Math.max(0, differenceInMinutes(shiftEndTime, checkOutTime))
}
</script>
