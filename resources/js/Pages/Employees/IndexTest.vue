<template>
  <Head title="Employees Test" />
  
  <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-2xl font-bold mb-6">Employees List</h1>
    
    <!-- Debug Info -->
    <div class="bg-gray-100 p-4 mb-4 rounded">
      <h3 class="font-bold mb-2">Debug Info:</h3>
      <pre>{{ JSON.stringify({ 
        hasEmployees: !!employees, 
        employeesType: typeof employees,
        employeesData: employees?.data?.length || 'no data',
        hasLocations: !!locations,
        locationsLength: locations?.length || 0,
        filters: filters 
      }, null, 2) }}</pre>
    </div>

    <!-- Simple Table -->
    <div class="bg-white shadow rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="employee in employees?.data || []" :key="employee.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ employee.employee_id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
              <div class="text-sm text-gray-500">{{ employee.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ employee.position }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                {{ employee.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
              <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
              <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
            </td>
          </tr>
          <tr v-if="!employees?.data?.length">
            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
              No employees found
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Simple Navigation -->
    <div class="mt-4 flex justify-between">
      <button 
        @click="$inertia.visit(route('employees.create'))"
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
      >
        Add Employee
      </button>
      
      <div class="text-sm text-gray-500">
        Total: {{ employees?.total || 0 }} employees
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  employees: Object,
  locations: Array,
  filters: Object
})

console.log('Employees Test Page Props:', props)
</script>
