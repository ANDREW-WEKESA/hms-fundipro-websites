<?php
/**
 * API Routes - FundiPro
 * REST API endpoint definitions
 */

// Authentication
$router->post('/api/auth/register',    [AuthController::class, 'register']);
$router->post('/api/auth/login',       [AuthController::class, 'login']);
$router->post('/api/auth/verify-otp',  [AuthController::class, 'verifyOTP']);
$router->post('/api/auth/resend-otp',  [AuthController::class, 'resendOTP']);

// Professionals
$router->get('/api/professionals',         [ProfessionalController::class, 'search']);
$router->get('/api/professionals/{id}',    [ProfessionalController::class, 'getProfile']);
$router->put('/api/professionals/{id}',    [ProfessionalController::class, 'update'], ['auth']);
$router->post('/api/professionals/verify', [ProfessionalController::class, 'submitVerification'], ['auth']);

// Portfolio
$router->get('/api/professionals/{id}/portfolio',  [PortfolioController::class, 'get']);
$router->post('/api/professionals/{id}/portfolio', [PortfolioController::class, 'create'], ['auth']);

// Jobs
$router->get('/api/jobs',               [JobController::class, 'list']);
$router->post('/api/jobs',              [JobController::class, 'postJob'], ['auth']);
$router->get('/api/jobs/{id}',          [JobController::class, 'getById']);
$router->post('/api/jobs/{id}/quote',   [JobController::class, 'submitQuote'], ['auth', 'verified']);
$router->get('/api/jobs/{id}/quotes',   [JobController::class, 'getQuotes'], ['auth']);

// Quotes
$router->post('/api/quotes/{id}/accept', [JobController::class, 'acceptQuote'], ['auth']);

// Bookings
$router->get('/api/bookings',             [BookingController::class, 'list'],     ['auth']);
$router->post('/api/bookings/{id}/start', [BookingController::class, 'start'],    ['auth']);
$router->post('/api/bookings/{id}/complete', [BookingController::class, 'complete'], ['auth']);

// Payments (M-Pesa)
$router->post('/api/payments/initiate',  [PaymentController::class, 'initiate'], ['auth']);
$router->post('/api/payments/callback',  [PaymentController::class, 'callback']); // Webhook
$router->get('/api/payments/{id}',       [PaymentController::class, 'status'],   ['auth']);

// Subscriptions
$router->get('/api/subscriptions/plans',     [SubscriptionController::class, 'plans']);
$router->post('/api/subscriptions/subscribe',[SubscriptionController::class, 'subscribe'], ['auth']);
$router->post('/api/subscriptions/cancel',   [SubscriptionController::class, 'cancel'],    ['auth']);

// Reviews
$router->post('/api/reviews',             [ReviewController::class, 'create'],        ['auth']);
$router->get('/api/professionals/{id}/reviews', [ReviewController::class, 'getByProfessional']);

// Admin (requires admin role)
$router->get('/api/admin/stats',          [AdminController::class, 'getDashboardStats'], ['admin']);
$router->get('/api/admin/verifications',  [AdminController::class, 'getVerificationQueue'], ['admin']);
$router->post('/api/admin/verify/{id}',   [AdminController::class, 'approve'], ['admin']);
$router->post('/api/admin/reject/{id}',   [AdminController::class, 'reject'],  ['admin']);
$router->get('/api/admin/revenue',        [AdminController::class, 'getSubscriptionRevenue'], ['admin']);
