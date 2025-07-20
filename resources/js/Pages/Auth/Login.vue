<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Logo and Title -->
      <div class="text-center">
        <div class="mx-auto w-16 h-16 bg-primary-600 rounded-2xl flex items-center justify-center mb-4">
          <span class="text-white font-bold text-2xl">S</span>
        </div>
        <h2 class="text-3xl font-bold text-gray-900">Welcome to SIGAP</h2>
        <p class="mt-2 text-gray-600">Sign in to your account to continue</p>
      </div>

      <!-- Login Form -->
      <div class="bg-white rounded-xl shadow-lg p-8 space-y-6">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              :class="[
                'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors',
                form.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-300'
              ]"
              placeholder="Enter your email"
            />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                :class="[
                  'w-full px-4 py-3 pr-12 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors',
                  form.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-300'
                ]"
                placeholder="Enter your password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
              </button>
            </div>
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
              />
              <label for="remember" class="ml-2 text-sm text-gray-700">
                Remember me
              </label>
            </div>
            <a href="#" class="text-sm text-primary-600 hover:text-primary-500">
              Forgot password?
            </a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="form.processing" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Signing in...
            </span>
            <span v-else>Sign in</span>
          </button>
        </form>

        <!-- Demo Accounts -->
        <div class="mt-6 border-t border-gray-200 pt-6">
          <p class="text-sm text-gray-600 text-center mb-4">Demo Accounts:</p>
          <div class="grid grid-cols-1 gap-2 text-xs">
            <div class="bg-gray-50 p-3 rounded-lg">
              <strong class="text-red-600">SuperAdmin:</strong> superadmin@sigap.com / password
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
              <strong class="text-blue-600">Admin:</strong> admin@sigap.com / password
            </div>
            <div class="bg-gray-50 p-3 rounded-lg">
              <strong class="text-green-600">Viewer:</strong> viewer@sigap.com / password
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center text-sm text-gray-500">
        <p>&copy; 2025 SIGAP. All rights reserved.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'

defineProps({
  canResetPassword: Boolean,
  status: String,
})

const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
