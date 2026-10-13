<?php

namespace HMS\Backend\Routes;

use HMS\Backend\Controllers\AuthController;
use HMS\Backend\Middleware\AuthMiddleware;
use HMS\Backend\Middleware\RoleMiddleware;

/**
 * API Routes Configuration
 * 
 * Defines all API endpoints and their middleware
 * 
 * @package HMS\Backend\Routes
 * @version 1.0.0
 */
class ApiRoutes
{
    private array $routes = [];
    private array $middleware = [];

    /**
     * Define all API routes
     * 
     * @return array Routes configuration
     */
    public static function define(): array
    {
        return [
            // Authentication routes (public)
            'POST:/api/auth/login' => [
                'controller' => AuthController::class,
                'method' => 'login',
                'middleware' => [],
                'public' => true
            ],
            
            'POST:/api/auth/register' => [
                'controller' => AuthController::class,
                'method' => 'register',
                'middleware' => [],
                'public' => true
            ],
            
            'POST:/api/auth/logout' => [
                'controller' => AuthController::class,
                'method' => 'logout',
                'middleware' => [AuthMiddleware::class],
                'public' => false
            ],
            
            'POST:/api/auth/refresh' => [
                'controller' => AuthController::class,
                'method' => 'refreshToken',
                'middleware' => [AuthMiddleware::class],
                'public' => false
            ],
            
            'POST:/api/auth/forgot-password' => [
                'controller' => AuthController::class,
                'method' => 'forgotPassword',
                'middleware' => [],
                'public' => true
            ],
            
            'POST:/api/auth/reset-password' => [
                'controller' => AuthController::class,
                'method' => 'resetPassword',
                'middleware' => [],
                'public' => true
            ],
            
            'POST:/api/auth/change-password' => [
                'controller' => AuthController::class,
                'method' => 'changePassword',
                'middleware' => [AuthMiddleware::class],
                'public' => false
            ],
            
            'POST:/api/auth/send-otp' => [
                'controller' => AuthController::class,
                'method' => 'sendOTP',
                'middleware' => [],
                'public' => true
            ],
            
            'POST:/api/auth/verify-otp' => [
                'controller' => AuthController::class,
                'method' => 'verifyOTP',
                'middleware' => [],
                'public' => true
            ],
            
            // User routes
            'GET:/api/users' => [
                'controller' => 'UserController',
                'method' => 'index',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor'],
                'permissions' => ['users.view']
            ],
            
            'GET:/api/users/:id' => [
                'controller' => 'UserController',
                'method' => 'show',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor'],
                'permissions' => ['users.view']
            ],
            
            'POST:/api/users' => [
                'controller' => 'UserController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin'],
                'permissions' => ['users.create']
            ],
            
            'PUT:/api/users/:id' => [
                'controller' => 'UserController',
                'method' => 'update',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin'],
                'permissions' => ['users.update']
            ],
            
            'DELETE:/api/users/:id' => [
                'controller' => 'UserController',
                'method' => 'destroy',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin'],
                'permissions' => ['users.delete']
            ],
            
            // Patient routes
            'GET:/api/patients' => [
                'controller' => 'PatientController',
                'method' => 'index',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor', 'nurse', 'receptionist']
            ],
            
            'GET:/api/patients/:id' => [
                'controller' => 'PatientController',
                'method' => 'show',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor', 'nurse', 'receptionist', 'patient']
            ],
            
            'POST:/api/patients' => [
                'controller' => 'PatientController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'receptionist']
            ],
            
            // Appointment routes
            'GET:/api/appointments' => [
                'controller' => 'AppointmentController',
                'method' => 'index',
                'middleware' => [AuthMiddleware::class]
            ],
            
            'GET:/api/appointments/:id' => [
                'controller' => 'AppointmentController',
                'method' => 'show',
                'middleware' => [AuthMiddleware::class]
            ],
            
            'POST:/api/appointments' => [
                'controller' => 'AppointmentController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class]
            ],
            
            'PUT:/api/appointments/:id' => [
                'controller' => 'AppointmentController',
                'method' => 'update',
                'middleware' => [AuthMiddleware::class]
            ],
            
            'DELETE:/api/appointments/:id' => [
                'controller' => 'AppointmentController',
                'method' => 'destroy',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'receptionist']
            ],
            
            // Medical records routes
            'GET:/api/medical-records' => [
                'controller' => 'MedicalRecordController',
                'method' => 'index',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor', 'nurse']
            ],
            
            'GET:/api/medical-records/:id' => [
                'controller' => 'MedicalRecordController',
                'method' => 'show',
                'middleware' => [AuthMiddleware::class]
            ],
            
            'POST:/api/medical-records' => [
                'controller' => 'MedicalRecordController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor']
            ],
            
            // Prescription routes
            'GET:/api/prescriptions' => [
                'controller' => 'PrescriptionController',
                'method' => 'index',
                'middleware' => [AuthMiddleware::class]
            ],
            
            'POST:/api/prescriptions' => [
                'controller' => 'PrescriptionController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'doctor']
            ],
            
            // Billing routes
            'GET:/api/billing/invoices' => [
                'controller' => 'BillingController',
                'method' => 'index',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'receptionist']
            ],
            
            'POST:/api/billing/invoices' => [
                'controller' => 'BillingController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'receptionist']
            ],
            
            'POST:/api/billing/payments' => [
                'controller' => 'PaymentController',
                'method' => 'store',
                'middleware' => [AuthMiddleware::class, RoleMiddleware::class],
                'roles' => ['admin', 'receptionist']
            ],
            
            // Health check
            'GET:/api/health' => [
                'controller' => 'HealthController',
                'method' => 'check',
                'middleware' => [],
                'public' => true
            ],
            
            // API version info
            'GET:/api/version' => [
                'controller' => 'VersionController',
                'method' => 'show',
                'middleware' => [],
                'public' => true
            ]
        ];
    }

    /**
     * Get middleware groups
     * 
     * @return array Middleware groups
     */
    public static function middlewareGroups(): array
    {
        return [
            'api' => [
                'throttle:60,1',
                'cors',
                'json'
            ],
            
            'auth' => [
                AuthMiddleware::class
            ],
            
            'admin' => [
                AuthMiddleware::class,
                RoleMiddleware::class . ':admin'
            ],
            
            'doctor' => [
                AuthMiddleware::class,
                RoleMiddleware::class . ':admin,doctor'
            ],
            
            'staff' => [
                AuthMiddleware::class,
                RoleMiddleware::class . ':admin,doctor,nurse,receptionist'
            ]
        ];
    }

    /**
     * Get rate limiting rules
     * 
     * @return array Rate limit configuration
     */
    public static function rateLimits(): array
    {
        return [
            'api' => [
                'limit' => 60,
                'per' => 'minute'
            ],
            
            'auth' => [
                'limit' => 10,
                'per' => 'minute'
            ],
            
            'search' => [
                'limit' => 30,
                'per' => 'minute'
            ]
        ];
    }

    /**
     * Get CORS configuration
     * 
     * @return array CORS settings
     */
    public static function corsConfig(): array
    {
        return [
            'allowed_origins' => [
                'https://hms.fundipro.co.ke',
                'https://app.fundipro.co.ke',
                'http://localhost:3000',
                'http://localhost:5173'
            ],
            
            'allowed_methods' => [
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE',
                'OPTIONS'
            ],
            
            'allowed_headers' => [
                'Content-Type',
                'Authorization',
                'X-Requested-With',
                'X-API-Key',
                'Accept'
            ],
            
            'exposed_headers' => [
                'Authorization',
                'X-Total-Count',
                'X-Page-Number',
                'X-Page-Size'
            ],
            
            'max_age' => 3600,
            
            'supports_credentials' => true
        ];
    }
}
