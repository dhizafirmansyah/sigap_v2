<template>
  <!-- Background Container with Animation -->
  <div class="login-background">
    <!-- Animated Background -->
    <div class="animated-bg"></div>
    
    <!-- Green Overlay -->
    <div class="green-overlay"></div>
    
    <!-- Login Content -->
    <div class="login-content">
      <div class="max-w-md w-full space-y-8">
        <!-- Logo and Title -->
        <div class="text-center animate-fade-in-up">
          <div class="mx-auto w-16 h-16 bg-green-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg animate-bounce-slow">
            <span class="text-white font-bold text-2xl">S</span>
          </div>
          <h2 class="text-3xl font-bold text-white drop-shadow-lg">Welcome to SIGAP</h2>
          <p class="mt-2 text-green-100 drop-shadow">Sign in to your account to continue</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white/95 backdrop-blur-sm rounded-xl shadow-2xl p-8 space-y-6 animate-fade-in-up-delay">
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
                  'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300',
                  form.errors.email ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-green-300'
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
                    'w-full px-4 py-3 pr-12 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-300',
                    form.errors.password ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-green-300'
                  ]"
                  placeholder="Enter your password"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-green-600 transition-colors duration-200"
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
                  class="h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                />
                <label for="remember" class="ml-2 text-sm text-gray-700">
                  Remember me
                </label>
              </div>
              <a href="#" class="text-sm text-green-600 hover:text-green-500 transition-colors duration-200">
                Forgot password?
              </a>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-lg text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 transform hover:scale-105"
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
              <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                <strong class="text-red-600">SuperAdmin:</strong> superadmin@sigap.com / password
              </div>
              <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                <strong class="text-blue-600">Admin:</strong> admin@sigap.com / password
              </div>
              <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                <strong class="text-green-600">Viewer:</strong> viewer@sigap.com / password
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-green-100 drop-shadow animate-fade-in-up-delay-2">
          <p>&copy; 2025 SIGAP. All rights reserved.</p>
        </div>
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

<style scoped>
/* Background Container */
.login-background {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

/* Animated Background */
.animated-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: url('/images/background_login.png');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  animation: backgroundZoom 20s ease-in-out infinite alternate;
}

/* Green Overlay */
.green-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    135deg,
    rgba(34, 197, 94, 0.7) 0%,
    rgba(22, 163, 74, 0.8) 50%,
    rgba(21, 128, 61, 0.7) 100%
  );
  animation: overlayPulse 15s ease-in-out infinite;
}

/* Login Content */
.login-content {
  position: relative;
  z-index: 10;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
}

/* Background Animation */
@keyframes backgroundZoom {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.1);
  }
}

/* Overlay Animation */
@keyframes overlayPulse {
  0%, 100% {
    opacity: 0.7;
  }
  50% {
    opacity: 0.9;
  }
}

/* Fade in animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s ease-out;
}

.animate-fade-in-up-delay {
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.animate-fade-in-up-delay-2 {
  animation: fadeInUp 0.8s ease-out 0.4s both;
}

/* Slow bounce animation for logo */
@keyframes bounceSlow {
  0%, 20%, 50%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-5px);
  }
  60% {
    transform: translateY(-3px);
  }
}

.animate-bounce-slow {
  animation: bounceSlow 3s infinite;
}

/* Floating animation for background elements */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

/* Add some floating elements */
.login-background::before {
  content: '';
  position: absolute;
  top: 10%;
  left: 10%;
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
  z-index: 5;
}

.login-background::after {
  content: '';
  position: absolute;
  top: 60%;
  right: 15%;
  width: 150px;
  height: 150px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
  animation: float 8s ease-in-out infinite reverse;
  z-index: 5;
}

/* Enhanced input focus effects */
input:focus {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(34, 197, 94, 0.2);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .login-content {
    padding: 2rem 1rem;
  }
  
  .animated-bg {
    background-position: center top;
  }
}
</style>
