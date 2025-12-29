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
use App\Http\Controllers\Api\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Admin\TechnicianRequestController as AdminTechnicianRequestController;

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
        // User Management
        Route::get('individual-customers/download', [AdminIndividualCustomerController::class, 'download']);
        Route::apiResource('individual-customers', AdminIndividualCustomerController::class);
        Route::post('individual-customers/bulk-delete', [AdminIndividualCustomerController::class, 'bulkDestroy']);
        
        Route::get('corporate-customers/download', [AdminCorporateCustomerController::class, 'download']);
        Route::apiResource('corporate-customers', AdminCorporateCustomerController::class);
        Route::post('corporate-customers/bulk-delete', [AdminCorporateCustomerController::class, 'bulkDestroy']);
        
        Route::get('technicians/download', [AdminTechnicianController::class, 'download']);
        Route::apiResource('technicians', AdminTechnicianController::class);
        Route::post('technicians/bulk-delete', [AdminTechnicianController::class, 'bulkDestroy']);
        
        Route::get('maintenance-companies/download', [AdminMaintenanceCompanyController::class, 'download']);
        Route::apiResource('maintenance-companies', AdminMaintenanceCompanyController::class);
        Route::post('maintenance-companies/bulk-delete', [AdminMaintenanceCompanyController::class, 'bulkDestroy']);

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
        Route::apiResource('orders', AdminOrderController::class);
        Route::apiResource('appointments', AdminAppointmentController::class);
        Route::apiResource('reviews', AdminReviewController::class);
        Route::apiResource('contracts', AdminContractController::class);
        Route::apiResource('settlements', AdminFinancialSettlementController::class);
        Route::apiResource('profits', AdminPlatformProfitController::class);
        Route::apiResource('payments', AdminPaymentController::class);
        Route::apiResource('inquiries', AdminInquiryController::class);
        Route::apiResource('notifications', AdminNotificationController::class);
        Route::apiResource('technician-requests', AdminTechnicianRequestController::class);
    });
});
