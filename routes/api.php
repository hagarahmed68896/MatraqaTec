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
use App\Http\Controllers\Api\CorporateCustomerController;
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

// Admin Controllers
use App\Http\Controllers\Api\Admin\IndividualCustomerController as AdminIndividualCustomerController;
use App\Http\Controllers\Api\Admin\CorporateCustomerController as AdminCorporateCustomerController;
use App\Http\Controllers\Api\Admin\TechnicianController as AdminTechnicianController;
use App\Http\Controllers\Api\Admin\MaintenanceCompanyController as AdminMaintenanceCompanyController;
use App\Http\Controllers\Api\Admin\CityController as AdminCityController;
use App\Http\Controllers\Api\Admin\DistrictController as AdminDistrictController;
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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Read-Only Resources
Route::apiResource('cities', CityController::class)->only(['index', 'show']);
Route::apiResource('districts', DistrictController::class)->only(['index', 'show']);
Route::apiResource('services', ServiceController::class)->only(['index', 'show']);
Route::apiResource('contents', ContentController::class)->only(['index', 'show']);
Route::apiResource('terms', TermController::class)->only(['index', 'show']);
Route::apiResource('social-links', SocialLinkController::class)->only(['index', 'show']);
Route::apiResource('faqs', FaqController::class)->only(['index', 'show']);
Route::apiResource('reviews', ReviewController::class)->only(['index', 'show']); // Often public

Route::post('/technician-requests', [TechnicianRequestController::class, 'store']); // Public Apply

// --- Protected Routes (User Scope) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']); // Generic profile info

    // User Profile Management (Self)
    Route::prefix('profile')->group(function () {
        Route::get('individual-customer', [IndividualCustomerController::class, 'show']);
        Route::put('individual-customer', [IndividualCustomerController::class, 'update']);
        
        Route::get('corporate-customer', [CorporateCustomerController::class, 'show']);
        Route::put('corporate-customer', [CorporateCustomerController::class, 'update']);
        
        Route::get('technician', [TechnicianController::class, 'show']);
        Route::put('technician', [TechnicianController::class, 'update']);
        
        Route::get('maintenance-company', [MaintenanceCompanyController::class, 'show']);
        Route::put('maintenance-company', [MaintenanceCompanyController::class, 'update']);
    });

    // User Operations (Scoped to Auth User)
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('appointments', AppointmentController::class);
    // Reviews: Store (Create). Index/Show are public but can be accessed here too. Update/Destroy managed by logic in controller.
    Route::post('reviews', [ReviewController::class, 'store']);

    // User Financials/Misc
    Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
    Route::apiResource('inquiries', InquiryController::class)->only(['index', 'store', 'show']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'show']);
    Route::apiResource('contracts', ContractController::class)->only(['index', 'show']);
    Route::apiResource('settlements', FinancialSettlementController::class)->only(['index', 'store', 'show']);
    Route::apiResource('technician-requests', TechnicianRequestController::class)->only(['index', 'show']); // View own requests

    // --- ADMIN Routes ---
    Route::prefix('admin')->group(function () {
        // Global User Actions
        Route::get('blocked-users/download-all', [AdminUserController::class, 'downloadAllBlocked']);
        Route::post('users/{id}/toggle-block', [AdminUserController::class, 'toggleBlock']);
        Route::post('users/bulk-toggle-block', [AdminUserController::class, 'bulkToggleBlock']);

        // User Management
        Route::get('individual-customers/download', [AdminIndividualCustomerController::class, 'download']);
        Route::get('individual-customers/blocked', [AdminIndividualCustomerController::class, 'blockedIndex']);
        Route::get('individual-customers/blocked-download', [AdminIndividualCustomerController::class, 'downloadBlocked']);
        Route::apiResource('individual-customers', AdminIndividualCustomerController::class);
        Route::post('individual-customers/bulk-delete', [AdminIndividualCustomerController::class, 'bulkDestroy']);
        
        Route::get('corporate-customers/download', [AdminCorporateCustomerController::class, 'download']);
        Route::get('corporate-customers/blocked', [AdminCorporateCustomerController::class, 'blockedIndex']);
        Route::get('corporate-customers/blocked-download', [AdminCorporateCustomerController::class, 'downloadBlocked']);
        Route::apiResource('corporate-customers', AdminCorporateCustomerController::class);
        Route::post('corporate-customers/bulk-delete', [AdminCorporateCustomerController::class, 'bulkDestroy']);
        
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
        Route::apiResource('terms', AdminTermController::class);
        Route::apiResource('social-links', AdminSocialLinkController::class);
        Route::apiResource('faqs', AdminFaqController::class);

        // Operational Management
        Route::post('orders/{id}/accept', [AdminOrderController::class, 'accept']);
        Route::post('orders/{id}/refuse', [AdminOrderController::class, 'refuse']);
        Route::apiResource('orders', AdminOrderController::class);
        Route::apiResource('appointments', AdminAppointmentController::class);
        Route::get('reviews/download', [AdminReviewController::class, 'download']);
        Route::apiResource('reviews', AdminReviewController::class);
        Route::get('contracts/download', [AdminContractController::class, 'download']);
        Route::apiResource('contracts', AdminContractController::class);
        Route::get('financial-settlements/download', [AdminFinancialSettlementController::class, 'download']);
        Route::post('financial-settlements/{id}/change-status', [AdminFinancialSettlementController::class, 'changeStatus']);
        Route::apiResource('financial-settlements', AdminFinancialSettlementController::class);
        Route::apiResource('profits', AdminPlatformProfitController::class);
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
