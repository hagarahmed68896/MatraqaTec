<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Public / User Controllers
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\TermController;
use App\Http\Controllers\Api\SocialLinkController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\IndividualCustomerController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\MaintenanceCompanyController;
use App\Http\Controllers\Api\TechnicianRequestController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\FinancialSettlementController;
use App\Http\Controllers\Api\PlatformProfitController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\PrivacyPolicyController;
use App\Http\Controllers\Api\UserSettingController;

// Admin Controllers
use App\Http\Controllers\Api\Admin\IndividualCustomerController as AdminIndividualCustomerController;
use App\Http\Controllers\Api\Admin\CorporateCustomerController as AdminCorporateCustomerController;
use App\Http\Controllers\Api\Admin\TechnicianController as AdminTechnicianController;
use App\Http\Controllers\Api\Admin\MaintenanceCompanyController as AdminMaintenanceCompanyController;
use App\Http\Controllers\Api\Admin\CityController as AdminCityController;
use App\Http\Controllers\Api\Admin\DistrictController as AdminDistrictController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\Admin\TermController as AdminTermController;
use App\Http\Controllers\Api\Admin\SocialLinkController as AdminSocialLinkController;
use App\Http\Controllers\Api\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Admin\ContractController as AdminContractController;
use App\Http\Controllers\Api\Admin\FinancialSettlementController as AdminFinancialSettlementController;
use App\Http\Controllers\Api\Admin\PlatformProfitController as AdminPlatformProfitController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Api\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\TechnicianRequestController as AdminTechnicianRequestController;
use App\Http\Controllers\Api\Admin\SupervisorController as AdminSupervisorController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\RefundController as AdminRefundController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Api\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Api\Admin\ContractPaymentReceiptController;
// Use alias for Admin Complaint Controller to avoid conflict with Public Complaint Controller if imported
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\Admin\PrivacyPolicyController as AdminPrivacyPolicyController;
use App\Http\Controllers\Api\CompanySetupController;
use App\Http\Controllers\Api\CompanyScheduleController;
use App\Http\Controllers\Api\CompanyReportController;
use App\Http\Controllers\Api\SupportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public Read-Only Resources
Route::get('services/favorites', [\App\Http\Controllers\Api\ServiceController::class, 'favorites'])->middleware('auth:sanctum'); // Must be before apiResource('services')
Route::apiResource('cities', CityController::class)->only(['index', 'show']);
Route::apiResource('districts', DistrictController::class)->only(['index', 'show']);
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
Route::apiResource('contents', ContentController::class)->only(['index', 'show']);
Route::apiResource('terms', TermController::class)->only(['index', 'show']);
Route::apiResource('privacy-policies', PrivacyPolicyController::class)->only(['index', 'show']);
Route::apiResource('social-links', SocialLinkController::class)->only(['index', 'show']);
Route::apiResource('faqs', FaqController::class)->only(['index', 'show']);
Route::apiResource('reviews', ReviewController::class)->only(['index', 'show']); // Often public

Route::post('/technician-requests', [TechnicianRequestController::class, 'store']); // Public Apply

// --- Protected Routes (User Scope) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']); // Generic profile info
    Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);

    // User Profile Management (Self)
    // User Profile Management (Self)
    Route::prefix('profile')->group(function () {
        // Consolidated Client Profile (For Mobile App)
        Route::get('/', [\App\Http\Controllers\Api\ClientProfileController::class, 'show']);
        Route::post('/update', [\App\Http\Controllers\Api\ClientProfileController::class, 'update']);
        Route::post('/change-password', [\App\Http\Controllers\Api\ClientProfileController::class, 'changePassword']);
        Route::post('/update-location', [\App\Http\Controllers\Api\ClientProfileController::class, 'updateLocation']);
        Route::get('/settings', [UserSettingController::class, 'index']);
        Route::post('/settings', [UserSettingController::class, 'update']);

        // Legacy / Specific Controller Routes (Keep if needed for specific granular updates)
        Route::get('individual-customer', [IndividualCustomerController::class, 'show']);
        Route::put('individual-customer', [IndividualCustomerController::class, 'update']);
        
        Route::get('technician', [TechnicianController::class, 'show']);
        Route::put('technician', [TechnicianController::class, 'update']);
        
        Route::get('maintenance-company', [MaintenanceCompanyController::class, 'show']);
        Route::put('maintenance-company', [MaintenanceCompanyController::class, 'update']);
    });

    // User Operations (Scoped to Auth User)
    Route::post('orders/{id}/start-work', [OrderController::class, 'startWork']); // في الطريق
    Route::post('orders/{id}/arrived', [OrderController::class, 'arrived']); // وصل
    Route::post('orders/{id}/work-started', [OrderController::class, 'workStarted']); // بدأ العمل
    Route::post('orders/{id}/update-sub-status', [OrderController::class, 'updateSubStatus']);
    Route::post('orders/{id}/update-spare-parts', [OrderController::class, 'updateSpareParts']);
    Route::post('orders/{id}/additional-visit', [OrderController::class, 'requestAdditionalVisit']);
    Route::post('orders/{id}/send-invoice', [OrderController::class, 'sendInvoiceToClient']);
    Route::post('orders/{id}/save-completion-photos', [OrderController::class, 'saveCompletionPhotos']);
    Route::post('orders/{id}/finish-work', [OrderController::class, 'finishWork']); // تم الإنجاز
    Route::post('orders/{id}/reschedule', [OrderController::class, 'reschedule']);
    Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{id}/resend', [OrderController::class, 'resend']);
    Route::get('orders/{id}/invoice', [OrderController::class, 'getInvoice']);
    Route::get('orders/{id}/technician-location', [OrderController::class, 'getTechnicianLocation']);
    Route::post('orders/{id}/accept', [OrderController::class, 'accept']);
    Route::post('orders/{id}/refuse', [OrderController::class, 'refuse']);

    // --- Technician Specific Routes ---
    Route::prefix('technician')->group(function () {
        Route::get('profile', [\App\Http\Controllers\Api\TechnicianProfileController::class, 'getProfile']);
        Route::post('profile/update', [\App\Http\Controllers\Api\TechnicianProfileController::class, 'updateProfile']);
        Route::post('profile/change-password', [\App\Http\Controllers\Api\TechnicianProfileController::class, 'updatePassword']);
        Route::get('statistics', [\App\Http\Controllers\Api\TechnicianProfileController::class, 'statistics']);
        Route::get('transactions', [\App\Http\Controllers\Api\TechnicianProfileController::class, 'transactions']);
        
        // Technician Support
        Route::get('support', [\App\Http\Controllers\Api\SupportController::class, 'index']);
        Route::get('faqs', [\App\Http\Controllers\Api\SupportController::class, 'faqs']);
        Route::get('terms', [\App\Http\Controllers\Api\SupportController::class, 'terms']);
        Route::get('privacy', [\App\Http\Controllers\Api\SupportController::class, 'privacy']);
        
        // Technician Tickets (Complaints/Inquiries)
        Route::apiResource('tickets', \App\Http\Controllers\Api\ComplaintController::class)->only(['index', 'store', 'show']);
        
        // Technican Order Management (Accept/Refuse)
        Route::post('orders/{id}/accept', [\App\Http\Controllers\Api\OrderController::class, 'technicianAccept']);
        Route::post('orders/{id}/refuse', [\App\Http\Controllers\Api\OrderController::class, 'technicianRefuse']);

        // Availability
        Route::post('toggle-availability', [\App\Http\Controllers\Api\TechnicianProfileController::class, 'toggleAvailability']);
    });
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('appointments', AppointmentController::class);
    // Reviews: Store (Create). Index/Show are public but can be accessed here too. Update/Destroy managed by logic in controller.
    Route::post('reviews', [ReviewController::class, 'store']);

    // User Financials/Misc
    Route::post('payments/pay-order', [PaymentController::class, 'payOrder']);
    Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
    Route::apiResource('inquiries', InquiryController::class)->only(['index', 'store', 'show']);
    Route::apiResource('notifications', NotificationController::class)->only(['index']);
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::delete('notifications/all', [NotificationController::class, 'destroyAll']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);
    
    // Support & Complaints
    Route::apiResource('support/tickets', ComplaintController::class)->only(['index', 'store', 'show']);

    Route::apiResource('contracts', ContractController::class)->only(['index', 'show']);
    Route::apiResource('settlements', FinancialSettlementController::class)->only(['index', 'store', 'show']);
    Route::apiResource('technician-requests', TechnicianRequestController::class)->only(['index', 'show']); // View own requests

    // Wallet
    Route::get('wallet', [WalletController::class, 'index']);
    Route::post('wallet/deposit', [WalletController::class, 'deposit']);

    // Chat
    Route::get('chat/conversations', [ChatController::class, 'conversations']);
    Route::get('chat/{receiver_id}', [ChatController::class, 'index']);
    Route::post('chat', [ChatController::class, 'store']);

    // Company Setup (Services & Coverage)
    Route::prefix('company')->group(function () {
        Route::get('/', [MaintenanceCompanyController::class, 'show']);
        Route::post('update', [MaintenanceCompanyController::class, 'update']);
        Route::post('password/update', [MaintenanceCompanyController::class, 'updatePassword']);
        Route::get('my-services', [CompanySetupController::class, 'myServices']);
        Route::get('services', [CompanySetupController::class, 'listServices']);
        Route::post('services', [CompanySetupController::class, 'updateServices']);
        Route::delete('services/{id}', [CompanySetupController::class, 'removeService']);
        Route::get('coverage', [CompanySetupController::class, 'listCoverageAreas']);
        Route::post('coverage', [CompanySetupController::class, 'updateCoverageAreas']);
        
        // List Technicians (Performance/Show More)
        Route::get('technicians', [MaintenanceCompanyController::class, 'listTechnicians']);
        Route::get('technicians/featured', [MaintenanceCompanyController::class, 'getFeaturedTechnicians']);
        Route::get('technicians/{id}', [MaintenanceCompanyController::class, 'showTechnician']);
        Route::post('technicians/add', [MaintenanceCompanyController::class, 'addTechnician']);
        Route::put('technicians/{id}', [MaintenanceCompanyController::class, 'updateTechnician']);
        Route::delete('technicians/{id}', [MaintenanceCompanyController::class, 'deleteTechnician']);

        // Search Features
        Route::get('search', [MaintenanceCompanyController::class, 'search']);
        Route::get('search-history', [MaintenanceCompanyController::class, 'getSearchHistory']);
        
        // Get Available Technicians for Order Assignment
        Route::get('available-technicians', [MaintenanceCompanyController::class, 'getAvailableTechnicians']);

        // Company Schedule (Availability Slots)
        Route::apiResource('schedules', CompanyScheduleController::class);

        // Company Reports & Transactions
        Route::get('statistics', [CompanyReportController::class, 'statistics']);
        Route::get('transactions', [CompanyReportController::class, 'transactions']);

        // Inventory Management
        Route::apiResource('inventory', InventoryController::class);

        // Support & Legal Info
        Route::get('support', [SupportController::class, 'index']);
        Route::get('faqs', [SupportController::class, 'faqs']);
        Route::get('terms', [SupportController::class, 'terms']);
        Route::get('privacy-policy', [SupportController::class, 'privacy']);
    });

    // Tracking
    Route::post('tracking/update', [TrackingController::class, 'updateLocation']);
    Route::get('tracking/{technician_id}', [TrackingController::class, 'getLocation']);

    // Search History
    Route::delete('search-history/{id}', [ServiceController::class, 'destroySearchHistory']);
    Route::delete('search-history', [ServiceController::class, 'clearSearchHistory']);
    // Search History & Favorites
    Route::post('services/toggle-favorite', [ServiceController::class, 'toggleFavorite']);
    Route::delete('search-history/{id}', [ServiceController::class, 'destroySearchHistory']);

    // --- ADMIN Routes ---
    Route::prefix('admin')->group(function () {
        // Global User Actions
        Route::get('blocked-users/download-all', [AdminUserController::class, 'downloadAllBlocked']);
        Route::post('users/{id}/toggle-block', [AdminUserController::class, 'toggleBlock']);
        Route::post('users/bulk-toggle-block', [AdminUserController::class, 'bulkToggleBlock']);

        // Admin Profile
        Route::get('profile', [AdminProfileController::class, 'show']);
        Route::post('profile', [AdminProfileController::class, 'update']);
        Route::delete('profile/avatar', [AdminProfileController::class, 'deleteAvatar']);
        Route::post('profile/password', [AdminProfileController::class, 'updatePassword']);

        // User Management
        Route::get('individual-customers/download', [AdminIndividualCustomerController::class, 'download']);
        Route::get('individual-customers/blocked', [AdminIndividualCustomerController::class, 'blockedIndex']);
        Route::get('individual-customers/blocked-download', [AdminIndividualCustomerController::class, 'downloadBlocked']);
        Route::get('individual-customers/{id}/statistics', [AdminIndividualCustomerController::class, 'statistics']);
        Route::apiResource('individual-customers', AdminIndividualCustomerController::class);
        
        Route::get('corporate-customers/download', [AdminCorporateCustomerController::class, 'download']);
        Route::get('corporate-customers/blocked', [AdminCorporateCustomerController::class, 'blockedIndex']);
        Route::apiResource('corporate-customers', AdminCorporateCustomerController::class);
        Route::post('individual-customers/bulk-delete', [AdminIndividualCustomerController::class, 'bulkDestroy']);
        
        Route::get('technicians/download', [AdminTechnicianController::class, 'download']);
        Route::get('technicians/blocked', [AdminTechnicianController::class, 'blockedIndex']);
        Route::get('technicians/blocked-download', [AdminTechnicianController::class, 'downloadBlocked']);
        Route::apiResource('technicians', AdminTechnicianController::class);
        Route::post('technicians/bulk-delete', [AdminTechnicianController::class, 'bulkDestroy']);
        
        Route::get('maintenance-companies/download', [AdminMaintenanceCompanyController::class, 'download']);
        Route::get('maintenance-companies/blocked', [AdminMaintenanceCompanyController::class, 'blockedIndex']);
        Route::get('maintenance-companies/blocked-download', [AdminMaintenanceCompanyController::class, 'downloadBlocked']);
        Route::apiResource('maintenance-companies', AdminMaintenanceCompanyController::class);
        Route::post('maintenance-companies/bulk-delete', [AdminMaintenanceCompanyController::class, 'bulkDestroy']);

        // Supervisor & Role Management
        Route::get('supervisors/download', [AdminSupervisorController::class, 'download']);
        Route::get('supervisors/blocked', [AdminSupervisorController::class, 'blockedIndex']);
        Route::get('supervisors/blocked-download', [AdminSupervisorController::class, 'downloadBlocked']);
        Route::apiResource('supervisors', AdminSupervisorController::class);
        Route::get('roles/download', [AdminRoleController::class, 'download']);
        Route::apiResource('roles', AdminRoleController::class);
        Route::get('permissions/grouped', [AdminPermissionController::class, 'grouped']);
        Route::apiResource('permissions', AdminPermissionController::class);

        // Refunds
        Route::get('refunds', [AdminRefundController::class, 'index']);
        Route::get('refunds/export', [AdminRefundController::class, 'export']);
        Route::get('refunds/{id}', [AdminRefundController::class, 'show']);
        Route::post('refunds/{id}/change-status', [AdminRefundController::class, 'changeStatus']);

        // System Management
        Route::apiResource('cities', AdminCityController::class);
        Route::apiResource('districts', AdminDistrictController::class);
        Route::apiResource('services', AdminServiceController::class);
        Route::apiResource('inventory', AdminInventoryController::class);
        Route::apiResource('contents', AdminContentController::class);
        Route::post('terms/bulk-delete', [AdminTermController::class, 'bulkDestroy']);
        Route::apiResource('terms', AdminTermController::class);
        Route::get('privacy-policies/download', [AdminPrivacyPolicyController::class, 'download']);
        Route::post('privacy-policies/bulk-delete', [AdminPrivacyPolicyController::class, 'bulkDestroy']);
        Route::apiResource('privacy-policies', AdminPrivacyPolicyController::class);
        Route::get('social-links', [AdminSocialLinkController::class, 'index']);
        Route::post('social-links', [AdminSocialLinkController::class, 'update']);
        Route::get('faqs/download', [AdminFaqController::class, 'download']);
        Route::post('faqs/bulk-delete', [AdminFaqController::class, 'bulkDestroy']);
        Route::apiResource('faqs', AdminFaqController::class);
        
        // Customer Service & Complaints
        Route::get('complaints/download', [AdminComplaintController::class, 'download']);
        Route::post('complaints/{id}/action', [AdminComplaintController::class, 'takeAction']);
        Route::apiResource('complaints', AdminComplaintController::class);
        
        // Platform Settings
        Route::get('settings', [SettingController::class, 'index']);
        Route::post('settings', [SettingController::class, 'update']);

        // Operational Management
        Route::post('orders/{id}/accept', [AdminOrderController::class, 'accept']);
        Route::post('orders/{id}/refuse', [AdminOrderController::class, 'refuse']);
        Route::apiResource('orders', AdminOrderController::class);
        Route::apiResource('appointments', AdminAppointmentController::class);
        Route::get('reviews/download', [AdminReviewController::class, 'download']);
        Route::apiResource('reviews', AdminReviewController::class);
        Route::get('contracts/download', [AdminContractController::class, 'download']);
        
        // Contract Payment Receipts (MUST be before contracts resource routes)
        Route::prefix('contracts/{contract}')->group(function () {
            Route::get('receipts', [ContractPaymentReceiptController::class, 'index']);
            Route::post('receipts', [ContractPaymentReceiptController::class, 'store']);
            Route::get('receipts/{receipt}', [ContractPaymentReceiptController::class, 'show']);
            Route::delete('receipts/{receipt}', [ContractPaymentReceiptController::class, 'destroy']);
        });
        
        Route::apiResource('contracts', AdminContractController::class);
        
        Route::get('financial-settlements/download', [AdminFinancialSettlementController::class, 'download']);
        Route::post('financial-settlements/{id}/change-status', [AdminFinancialSettlementController::class, 'changeStatus']);
        Route::apiResource('financial-settlements', AdminFinancialSettlementController::class);
        Route::apiResource('platform-profits', AdminPlatformProfitController::class);
        Route::get('payments/download', [AdminPaymentController::class, 'download']);
        Route::apiResource('payments', AdminPaymentController::class)->except(['store']);

        // Invoices
        Route::get('invoices/download', [AdminInvoiceController::class, 'download']);
        Route::post('invoices/{id}/send', [AdminInvoiceController::class, 'send']);
        Route::apiResource('invoices', AdminInvoiceController::class)->only(['index', 'show']);

        Route::apiResource('inquiries', AdminInquiryController::class);
        // Notifications
        Route::post('notifications/bulk-delete', [AdminNotificationController::class, 'bulkDestroy']);
        Route::apiResource('notifications', AdminNotificationController::class);
        Route::post('technician-requests/{id}/accept', [AdminTechnicianRequestController::class, 'accept']);
        Route::post('technician-requests/{id}/refuse', [AdminTechnicianRequestController::class, 'refuse']);
        Route::apiResource('technician-requests', AdminTechnicianRequestController::class);

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('summary', [AdminReportController::class, 'index']);
            Route::get('users', [AdminReportController::class, 'users']);
            Route::get('financials', [AdminReportController::class, 'financials']);
            Route::get('services', [AdminReportController::class, 'services']);
            Route::get('technicians', [AdminReportController::class, 'technicians']);
        });
    });
});
