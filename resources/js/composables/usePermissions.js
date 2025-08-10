import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
    const page = usePage()
    
    const user = computed(() => page.props.auth?.user)
    const permissions = computed(() => user.value?.permissions || [])
    const role = computed(() => user.value?.role)
    
    const hasPermission = (permission) => {
        return permissions.value.includes(permission)
    }
    
    const hasAnyPermission = (permissionList) => {
        return permissionList.some(permission => permissions.value.includes(permission))
    }
    
    const hasAllPermissions = (permissionList) => {
        return permissionList.every(permission => permissions.value.includes(permission))
    }
    
    const hasRole = (roleName) => {
        return role.value?.name === roleName
    }
    
    const hasMinLevel = (level) => {
        return role.value?.level >= level
    }
    
    const isSuperAdmin = computed(() => hasRole('superadmin'))
    const isAdmin = computed(() => hasRole('admin') || isSuperAdmin.value)
    const isManager = computed(() => hasRole('manager') || isAdmin.value)
    const isSupervisor = computed(() => hasRole('supervisor') || isManager.value)
    
    // Helper for checking specific resource permissions
    const canView = (resource) => hasPermission(`view_${resource}`)
    const canCreate = (resource) => hasPermission(`create_${resource}`)
    const canEdit = (resource) => hasPermission(`edit_${resource}`)
    const canDelete = (resource) => hasPermission(`delete_${resource}`)
    
    // Common permission combinations
    const canManage = (resource) => {
        return hasAnyPermission([
            `view_${resource}`,
            `create_${resource}`,
            `edit_${resource}`,
            `delete_${resource}`
        ])
    }
    
    const canFullyManage = (resource) => {
        return hasAllPermissions([
            `view_${resource}`,
            `create_${resource}`,
            `edit_${resource}`,
            `delete_${resource}`
        ])
    }
    
    return {
        user,
        permissions,
        role,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasRole,
        hasMinLevel,
        isSuperAdmin,
        isAdmin,
        isManager,
        isSupervisor,
        canView,
        canCreate,
        canEdit,
        canDelete,
        canManage,
        canFullyManage
    }
}
