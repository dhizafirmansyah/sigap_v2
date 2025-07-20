/**
 * API Helper untuk Fetch API calls yang konsisten
 */

// Get CSRF token function with multiple fallbacks
const getCsrfToken = () => {
    // Try meta tag first
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.content
    if (metaToken) return metaToken
    
    // Try from form input as fallback
    const inputToken = document.querySelector('input[name="_token"]')?.value
    if (inputToken) return inputToken
    
    // Try from window.Laravel if available
    if (window.Laravel && window.Laravel.csrfToken) {
        return window.Laravel.csrfToken
    }
    
    return ''
}

// Base fetch wrapper dengan error handling
const apiRequest = async (url, options = {}) => {
    const csrfToken = getCsrfToken()
    
    const defaultOptions = {
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            ...options.headers
        },
        credentials: 'same-origin', // Include cookies for CSRF protection
        ...options
    }

    try {
        const response = await fetch(url, defaultOptions)
        
        // Always try to parse response as JSON
        let data
        try {
            data = await response.json()
        } catch (parseError) {
            data = { message: 'Invalid response format' }
        }

        if (!response.ok) {
            // Create error object with response details
            const error = new Error(data.message || `HTTP error! status: ${response.status}`)
            error.response = response
            error.data = data
            error.status = response.status
            throw error
        }

        return { data, response }
    } catch (error) {
        // Network or parsing errors
        if (!error.response) {
            error.message = error.message || 'Network error occurred'
        }
        
        console.error('API Request Error:', error)
        throw error
    }
}

// GET request
const apiGet = (url, params = {}) => {
    const searchParams = new URLSearchParams(params).toString()
    const fullUrl = searchParams ? `${url}?${searchParams}` : url
    
    return apiRequest(fullUrl, {
        method: 'GET'
    })
}

// POST request
const apiPost = (url, data = {}) => {
    return apiRequest(url, {
        method: 'POST',
        body: JSON.stringify(data)
    })
}

// PUT request
const apiPut = (url, data = {}) => {
    return apiRequest(url, {
        method: 'PUT',
        body: JSON.stringify(data)
    })
}

// DELETE request
const apiDelete = (url) => {
    return apiRequest(url, {
        method: 'DELETE'
    })
}

// Format dates for API
const formatDateForApi = (date) => {
    if (!date) return null
    if (date instanceof Date) {
        return date.toISOString().split('T')[0]
    }
    return date
}

// Employee specific API calls
const employeeApi = {
    // Get all employees with filters
    getEmployees: (filters = {}) => {
        // Use test API for now (without auth)
        return apiGet('/api-test/employees', filters)
    },

    // Get single employee
    getEmployee: (id) => {
        return apiGet(`/employees/${id}`)
    },

    // Create employee
    createEmployee: (data) => {
        const formattedData = {
            ...data,
            birth_date: formatDateForApi(data.birth_date),
            hire_date: formatDateForApi(data.hire_date),
            contract_start: formatDateForApi(data.contract_start),
            contract_end: formatDateForApi(data.contract_end)
        }
        return apiPost('/employees', formattedData)
    },

    // Update employee
    updateEmployee: (id, data) => {
        const formattedData = {
            ...data,
            birth_date: formatDateForApi(data.birth_date),
            hire_date: formatDateForApi(data.hire_date),
            contract_start: formatDateForApi(data.contract_start),
            contract_end: formatDateForApi(data.contract_end)
        }
        return apiPut(`/employees/${id}`, formattedData)
    },

    // Delete employee
    deleteEmployee: (id) => {
        return apiDelete(`/employees/${id}`)
    },

    // Search employees
    searchEmployees: (query) => {
        // Use test API for now (without auth)
        return apiGet('/api-test/employees-search', { q: query })
    }
}

// Brand specific API calls
const brandApi = {
    // Get all brands with filters
    getBrands: (filters = {}) => {
        return apiGet('/brands', filters)
    },

    // Get single brand
    getBrand: (id) => {
        return apiGet(`/brands/${id}`)
    },

    // Create brand
    createBrand: (data) => {
        return apiPost('/brands', data)
    },

    // Update brand
    updateBrand: (id, data) => {
        return apiPut(`/brands/${id}`, data)
    },

    // Delete brand
    deleteBrand: (id) => {
        return apiDelete(`/brands/${id}`)
    },

    // Search brands
    searchBrands: (query) => {
        return apiGet('/brands/search', { q: query })
    }
}

// Location specific API calls
const locationApi = {
    // Get all locations with filters
    getLocations: (filters = {}) => {
        return apiGet('/locations', filters)
    },

    // Get single location
    getLocation: (id) => {
        return apiGet(`/locations/${id}`)
    },

    // Create location
    createLocation: (data) => {
        return apiPost('/locations', data)
    },

    // Update location
    updateLocation: (id, data) => {
        return apiPut(`/locations/${id}`, data)
    },

    // Delete location
    deleteLocation: (id) => {
        return apiDelete(`/locations/${id}`)
    },

    // Search locations
    searchLocations: (query) => {
        return apiGet('/locations/search', { q: query })
    }
}

// Named exports for individual utilities
export { apiGet, apiPost, apiPut, apiDelete, employeeApi, brandApi, locationApi, formatDateForApi }

// Export employeeApi as api for consistency with other components
export const api = {
    employees: employeeApi,
    brands: brandApi,
    locations: locationApi
}

// Default export
export default {
    apiGet,
    apiPost,
    apiPut,
    apiDelete,
    employeeApi,
    brandApi,
    locationApi,
    formatDateForApi,
    api: {
        employees: employeeApi,
        brands: brandApi,
        locations: locationApi
    }
}
