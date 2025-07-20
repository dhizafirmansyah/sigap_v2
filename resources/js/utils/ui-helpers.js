/**
 * Loading states dan error handling utilities untuk UI
 */

import { ref } from 'vue'

// Global loading state
export const useLoading = () => {
    const isLoading = ref(false)
    const loadingText = ref('Loading...')

    const setLoading = (loading, text = 'Loading...') => {
        isLoading.value = loading
        loadingText.value = text
    }

    return {
        isLoading,
        loadingText,
        setLoading
    }
}

// Error handling
export const useErrorHandler = () => {
    const handleApiError = (error, toast) => {
        console.error('API Error:', error)
        
        let message = 'An unexpected error occurred'
        
        if (error.response) {
            // Server responded with error status
            const status = error.response.status
            
            switch (status) {
                case 400:
                    message = 'Invalid request. Please check your input.'
                    break
                case 401:
                    message = 'Authentication required. Please log in.'
                    break
                case 403:
                    message = 'You do not have permission to perform this action.'
                    break
                case 404:
                    message = 'The requested resource was not found.'
                    break
                case 422:
                    message = 'Validation failed. Please check your input.'
                    break
                case 429:
                    message = 'Too many requests. Please try again later.'
                    break
                case 500:
                    message = 'Server error. Please try again later.'
                    break
                default:
                    message = error.message || `Server error (${status})`
            }
        } else if (error.name === 'TypeError' && error.message.includes('fetch')) {
            // Network error
            message = 'Network error. Please check your internet connection.'
        } else {
            message = error.message || message
        }

        if (toast) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: message,
                life: 5000
            })
        }

        return message
    }

    return {
        handleApiError
    }
}

// Form validation helpers
export const useFormValidation = () => {
    const validateRequired = (value, fieldName) => {
        if (!value || (typeof value === 'string' && !value.trim())) {
            return `${fieldName} is required`
        }
        return null
    }

    const validateEmail = (email) => {
        if (!email) return null
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        return emailRegex.test(email) ? null : 'Please enter a valid email address'
    }

    const validatePhone = (phone) => {
        if (!phone) return null
        const phoneRegex = /^[\+]?[0-9\-\s\(\)]+$/
        return phoneRegex.test(phone) ? null : 'Please enter a valid phone number'
    }

    const validateDate = (date, fieldName) => {
        if (!date) return null
        const dateObj = new Date(date)
        return isNaN(dateObj.getTime()) ? `Please enter a valid ${fieldName}` : null
    }

    return {
        validateRequired,
        validateEmail,
        validatePhone,
        validateDate
    }
}

// Success feedback
export const useSuccessFeedback = () => {
    const showSuccess = (toast, title, message, duration = 3000) => {
        toast.add({
            severity: 'success',
            summary: title,
            detail: message,
            life: duration
        })
    }

    const showInfo = (toast, title, message, duration = 3000) => {
        toast.add({
            severity: 'info',
            summary: title,
            detail: message,
            life: duration
        })
    }

    const showWarning = (toast, title, message, duration = 4000) => {
        toast.add({
            severity: 'warn',
            summary: title,
            detail: message,
            life: duration
        })
    }

    return {
        showSuccess,
        showInfo,
        showWarning
    }
}

export default {
    useLoading,
    useErrorHandler,
    useFormValidation,
    useSuccessFeedback
}
