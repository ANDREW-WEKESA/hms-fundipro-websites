<?php

namespace HMS\Backend\Middleware;

use HMS\Backend\Models\User;

/**
 * Role-Based Access Control Middleware
 * 
 * Manages permissions and access control based on user roles
 * Implements granular permissions for HMS operations
 * 
 * @package HMS\Backend\Middleware
 * @version 1.0.0
 */
class RoleMiddleware
{
    /**
     * Permission definitions for each role
     */
    private const PERMISSIONS = [
        User::ROLE_ADMIN => [
            // User management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.manage_roles',
            
            // Patient management
            'patients.view',
            'patients.create',
            'patients.update',
            'patients.delete',
            'patients.view_records',
            'patients.edit_records',
            
            // Doctor management
            'doctors.view',
            'doctors.create',
            'doctors.update',
            'doctors.delete',
            'doctors.manage_schedule',
            
            // Appointments
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.delete',
            'appointments.assign',
            
            // Medical records
            'medical_records.view',
            'medical_records.create',
            'medical_records.update',
            'medical_records.delete',
            
            // Prescriptions
            'prescriptions.view',
            'prescriptions.create',
            'prescriptions.update',
            'prescriptions.delete',
            
            // Billing
            'billing.view',
            'billing.create',
            'billing.update',
            'billing.delete',
            'billing.process_payment',
            'billing.generate_invoice',
            
            // Inventory
            'inventory.view',
            'inventory.create',
            'inventory.update',
            'inventory.delete',
            'inventory.manage_stock',
            
            // Reports
            'reports.view',
            'reports.generate',
            'reports.export',
            
            // System settings
            'settings.view',
            'settings.update',
            'settings.manage_hospital',
            
            // Audit logs
            'audit.view',
            'audit.export'
        ],
        
        User::ROLE_DOCTOR => [
            // Patient management (limited)
            'patients.view',
            'patients.view_records',
            'patients.search',
            
            // Appointments
            'appointments.view',
            'appointments.view_own',
            'appointments.update_own',
            'appointments.manage_own_schedule',
            
            // Medical records
            'medical_records.view',
            'medical_records.create',
            'medical_records.update',
            'medical_records.view_own_patients',
            
            // Prescriptions
            'prescriptions.view',
            'prescriptions.create',
            'prescriptions.update',
            'prescriptions.view_own',
            
            // Clinical notes
            'clinical_notes.view',
            'clinical_notes.create',
            'clinical_notes.update',
            'clinical_notes.view_own',
            
            // Diagnosis
            'diagnosis.create',
            'diagnosis.update',
            'diagnosis.view',
            
            // Lab orders
            'lab_orders.create',
            'lab_orders.view',
            'lab_orders.view_results',
            
            // Imaging orders
            'imaging_orders.create',
            'imaging_orders.view',
            'imaging_orders.view_results',
            
            // Referrals
            'referrals.create',
            'referrals.view',
            
            // Reports (limited)
            'reports.view_own',
            'reports.generate_own'
        ],
        
        User::ROLE_NURSE => [
            // Patient management
            'patients.view',
            'patients.view_records',
            'patients.search',
            
            // Appointments
            'appointments.view',
            'appointments.check_in',
            'appointments.update_status',
            
            // Vital signs
            'vitals.view',
            'vitals.create',
            'vitals.update',
            
            // Medical records (read-only)
            'medical_records.view',
            
            // Prescriptions (read-only)
            'prescriptions.view',
            'prescriptions.administer',
            
            // Lab orders
            'lab_orders.view',
            'lab_orders.collect_sample',
            
            // Imaging orders
            'imaging_orders.view',
            
            // Visits
            'visits.view',
            'visits.create',
            'visits.update',
            
            // Triage
            'triage.create',
            'triage.view',
            'triage.update',
            
            // Nursing notes
            'nursing_notes.create',
            'nursing_notes.view',
            'nursing_notes.update',
            
            // Inventory (limited)
            'inventory.view',
            'inventory.request_items'
        ],
        
        User::ROLE_RECEPTIONIST => [
            // Patient management
            'patients.view',
            'patients.create',
            'patients.update',
            'patients.search',
            'patients.register',
            
            // Appointments
            'appointments.view',
            'appointments.create',
            'appointments.update',
            'appointments.cancel',
            'appointments.reschedule',
            'appointments.check_in',
            
            // Billing
            'billing.view',
            'billing.create',
            'billing.generate_invoice',
            'billing.process_payment',
            'billing.print_receipt',
            
            // Insurance
            'insurance.view',
            'insurance.verify',
            'insurance.create_claim',
            
            // Reports (limited)
            'reports.view_daily',
            'reports.generate_daily',
            
            // Queue management
            'queue.view',
            'queue.manage',
            
            // Communication
            'communication.send_sms',
            'communication.send_notification'
        ],
        
        User::ROLE_PATIENT => [
            // Own records
            'patients.view_own',
            'patients.update_own_profile',
            
            // Appointments
            'appointments.view_own',
            'appointments.book',
            'appointments.cancel_own',
            'appointments.reschedule_own',
            
            // Medical records (own)
            'medical_records.view_own',
            
            // Prescriptions (own)
            'prescriptions.view_own',
            
            // Lab results (own)
            'lab_results.view_own',
            
            // Imaging results (own)
            'imaging_results.view_own',
            
            // Billing (own)
            'billing.view_own',
            'billing.pay_own',
            
            // Doctors
            'doctors.view',
            'doctors.search',
            
            // Communication
            'communication.contact_support'
        ]
    ];

    /**
     * Handle role-based access control
     * 
     * @param array $request Request data with user information
     * @param array $allowedRoles Array of allowed roles
     * @return array Response with access decision
     */
    public function handle(array $request, array $allowedRoles): array
    {
        // Check if user is authenticated
        if (!isset($request['user'])) {
            return $this->forbiddenResponse('Authentication required');
        }

        $user = $request['user'];
        $userRole = $user['role'] ?? null;

        // Check if user has one of the allowed roles
        if (!in_array($userRole, $allowedRoles, true)) {
            return $this->forbiddenResponse('Insufficient permissions');
        }

        return [
            'allowed' => true,
            'user' => $user
        ];
    }

    /**
     * Check if user has specific permission
     * 
     * @param string $userRole User role
     * @param string $permission Required permission
     * @return bool True if user has permission
     */
    public function checkPermission(string $userRole, string $permission): bool
    {
        // Admin has all permissions
        if ($userRole === User::ROLE_ADMIN) {
            return true;
        }

        // Check if permission exists for role
        if (!isset(self::PERMISSIONS[$userRole])) {
            return false;
        }

        return in_array($permission, self::PERMISSIONS[$userRole], true);
    }

    /**
     * Check if user has any of the required permissions
     * 
     * @param string $userRole User role
     * @param array $permissions Required permissions
     * @return bool True if user has at least one permission
     */
    public function checkAnyPermission(string $userRole, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->checkPermission($userRole, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all required permissions
     * 
     * @param string $userRole User role
     * @param array $permissions Required permissions
     * @return bool True if user has all permissions
     */
    public function checkAllPermissions(string $userRole, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->checkPermission($userRole, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for a role
     * 
     * @param string $role User role
     * @return array Array of permissions
     */
    public function getPermissions(string $role): array
    {
        return self::PERMISSIONS[$role] ?? [];
    }

    /**
     * Get all available roles
     * 
     * @return array Array of roles
     */
    public function getAllRoles(): array
    {
        return [
            User::ROLE_ADMIN,
            User::ROLE_DOCTOR,
            User::ROLE_NURSE,
            User::ROLE_RECEPTIONIST,
            User::ROLE_PATIENT
        ];
    }

    /**
     * Check if user can access specific resource
     * 
     * @param array $user User data
     * @param string $resource Resource type
     * @param string $action Action to perform
     * @param array $resourceData Resource-specific data
     * @return bool True if access allowed
     */
    public function canAccessResource(
        array $user, 
        string $resource, 
        string $action, 
        array $resourceData = []
    ): bool {
        $permission = "{$resource}.{$action}";
        $userRole = $user['role'];

        // Check basic permission
        if (!$this->checkPermission($userRole, $permission)) {
            return false;
        }

        // Additional checks for "own" permissions
        if (str_ends_with($permission, '_own')) {
            return $this->checkOwnership($user, $resourceData);
        }

        return true;
    }

    /**
     * Check resource ownership
     * 
     * @param array $user User data
     * @param array $resourceData Resource data
     * @return bool True if user owns the resource
     */
    private function checkOwnership(array $user, array $resourceData): bool
    {
        // Patient checking own records
        if ($user['role'] === User::ROLE_PATIENT) {
            return isset($resourceData['patient_id']) && 
                   $resourceData['patient_id'] === $user['id'];
        }

        // Doctor checking own appointments/prescriptions
        if ($user['role'] === User::ROLE_DOCTOR) {
            return isset($resourceData['doctor_id']) && 
                   $resourceData['doctor_id'] === $user['id'];
        }

        return false;
    }

    /**
     * Validate role hierarchy
     * 
     * @param string $currentRole Current user role
     * @param string $targetRole Target role to assign/modify
     * @return bool True if current role can manage target role
     */
    public function canManageRole(string $currentRole, string $targetRole): bool
    {
        // Only admin can manage roles
        if ($currentRole !== User::ROLE_ADMIN) {
            return false;
        }

        // Admin can manage all roles
        return true;
    }

    /**
     * Get role display name
     * 
     * @param string $role Role constant
     * @return string Display name
     */
    public function getRoleDisplayName(string $role): string
    {
        return match($role) {
            User::ROLE_ADMIN => 'Administrator',
            User::ROLE_DOCTOR => 'Doctor',
            User::ROLE_NURSE => 'Nurse',
            User::ROLE_RECEPTIONIST => 'Receptionist',
            User::ROLE_PATIENT => 'Patient',
            default => 'Unknown'
        };
    }

    /**
     * Get role description
     * 
     * @param string $role Role constant
     * @return string Role description
     */
    public function getRoleDescription(string $role): string
    {
        return match($role) {
            User::ROLE_ADMIN => 'Full system access and management',
            User::ROLE_DOCTOR => 'Medical staff with patient care responsibilities',
            User::ROLE_NURSE => 'Nursing staff with patient care support',
            User::ROLE_RECEPTIONIST => 'Front desk and administrative staff',
            User::ROLE_PATIENT => 'Hospital patient with limited access',
            default => 'No description available'
        };
    }

    /**
     * Create forbidden response
     * 
     * @param string $message Error message
     * @return array Response array
     */
    private function forbiddenResponse(string $message): array
    {
        return [
            'allowed' => false,
            'status' => 403,
            'data' => [
                'message' => $message,
                'error' => 'Forbidden'
            ]
        ];
    }

    /**
     * Log access attempt
     * 
     * @param array $user User data
     * @param string $resource Resource accessed
     * @param string $action Action attempted
     * @param bool $allowed Whether access was allowed
     * @return void
     */
    public function logAccess(
        array $user, 
        string $resource, 
        string $action, 
        bool $allowed
    ): void {
        error_log(sprintf(
            "Access %s: User %d (Role: %s) attempted %s.%s",
            $allowed ? 'GRANTED' : 'DENIED',
            $user['id'],
            $user['role'],
            $resource,
            $action
        ));
    }
}
