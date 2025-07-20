<template>
    <Head title="Employee Details Test" />
    
    <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Employee Details Test</h1>
        
        <div class="bg-gray-100 p-4 rounded mb-4">
            <h2>Debug Info:</h2>
            <p>Employee exists: {{ !!employee }}</p>
            <p>Employee type: {{ typeof employee }}</p>
            <p>Employee keys: {{ employee ? Object.keys(employee) : 'No keys' }}</p>
            <p>Employee name: {{ employee?.name || 'No name' }}</p>
            <p>Employee ID: {{ employee?.id || 'No ID' }}</p>
        </div>
        
        <div v-if="employee && employee.name" class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-semibold mb-4">{{ employee.name }}</h2>
            <p><strong>Employee ID:</strong> {{ employee.employee_id }}</p>
            <p><strong>Email:</strong> {{ employee.email }}</p>
            <p><strong>Position:</strong> {{ employee.position }}</p>
            <p><strong>Department:</strong> {{ employee.department }}</p>
            <p><strong>Status:</strong> {{ employee.status }}</p>
        </div>
        
        <div v-else class="bg-red-100 p-4 rounded">
            <p>No employee data available</p>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    employee: Object
})

console.log('Props received:', props)
console.log('Employee data:', props.employee)
</script>
