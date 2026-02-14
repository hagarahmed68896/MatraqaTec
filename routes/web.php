<?php

use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'postLogin'])->name('admin.post-login');
    
    Route::get('/forgot-password', [AdminController::class, 'forgotPassword'])->name('admin.forgot-password');
    Route::post('/forgot-password', [AdminController::class, 'postForgotPassword'])->name('admin.post-forgot-password');
    
    Route::get('/reset-password', [AdminController::class, 'resetPassword'])->name('admin.reset-password');
    Route::post('/reset-password', [AdminController::class, 'postResetPassword'])->name('admin.post-reset-password');
    
    Route::get('/verify', [AdminController::class, 'verify'])->name('admin.verify');
    Route::post('/verify', [AdminController::class, 'postVerify'])->name('admin.post-verify');
    
    Route::get('/success', [AdminController::class, 'success'])->name('admin.success');
    
    Route::get('/switch-language/{lang}', [AdminController::class, 'switchLanguage'])->name('admin.switch-language');

    // Protected Routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard/orders', [App\Http\Controllers\AdminController::class, 'dashboardOrders'])->name('admin.dashboard.orders');
    Route::get('/dashboard/categories', [App\Http\Controllers\AdminController::class, 'dashboardCategories'])->name('admin.dashboard.categories');
    Route::get('/dashboard/revenue', [App\Http\Controllers\AdminController::class, 'dashboardRevenue'])->name('admin.dashboard.revenue');
    Route::get('/dashboard/users', [App\Http\Controllers\AdminController::class, 'dashboardUsers'])->name('admin.dashboard.users');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        
        // Admin Notifications
        Route::get('/notifications', [AdminController::class, 'notifications'])->name('admin.notifications.index');
        Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsRead'])->name('admin.notifications.markAllRead');
        Route::post('/notifications/{id}/read', [AdminController::class, 'markNotificationRead'])->name('admin.notifications.markRead');
        Route::delete('/notifications/{id}', [AdminController::class, 'deleteNotification'])->name('admin.notifications.destroy');
        
        // Resource Management (CRUD)
        $resources = [
            'users' => App\Http\Controllers\Admin\UserController::class,
            'services' => App\Http\Controllers\Admin\ServiceController::class,
            'orders' => App\Http\Controllers\Admin\OrderController::class,
            'technicians' => App\Http\Controllers\Admin\TechnicianController::class,
            'maintenance-companies' => App\Http\Controllers\Admin\MaintenanceCompanyController::class,
            'cities' => App\Http\Controllers\Admin\CityController::class,
            'districts' => App\Http\Controllers\Admin\DistrictController::class,
            'corporate-customers' => App\Http\Controllers\Admin\CorporateCustomerController::class,
            'individual-customers' => App\Http\Controllers\Admin\IndividualCustomerController::class,
            'contracts' => App\Http\Controllers\Admin\ContractController::class,
            'payments' => App\Http\Controllers\Admin\PaymentController::class,
            'invoices' => App\Http\Controllers\Admin\InvoiceController::class,
            'financial-settlements' => App\Http\Controllers\Admin\FinancialSettlementController::class,
            'platform-profits' => App\Http\Controllers\Admin\PlatformProfitController::class,
            'refunds' => App\Http\Controllers\Admin\RefundController::class,
            'appointments' => App\Http\Controllers\Admin\AppointmentController::class,
            'reviews' => App\Http\Controllers\Admin\ReviewController::class,
            'complaints' => App\Http\Controllers\Admin\ComplaintController::class,
            'technician-requests' => App\Http\Controllers\Admin\TechnicianRequestController::class,
            'inquiries' => App\Http\Controllers\Admin\InquiryController::class,
            'contents' => App\Http\Controllers\Admin\ContentController::class,
            'faqs' => App\Http\Controllers\Admin\FaqController::class,
            'terms' => App\Http\Controllers\Admin\TermController::class,
            'privacy-policies' => App\Http\Controllers\Admin\PrivacyPolicyController::class,
            'social-links' => App\Http\Controllers\Admin\SocialLinkController::class,
            'settings' => App\Http\Controllers\Admin\SettingController::class,
            'inventory' => App\Http\Controllers\Admin\InventoryController::class,
            'roles' => App\Http\Controllers\Admin\RoleController::class,
            'permissions' => App\Http\Controllers\Admin\PermissionController::class,
            'supervisors' => App\Http\Controllers\Admin\SupervisorController::class,
            'contract-receipts' => App\Http\Controllers\Admin\ContractPaymentReceiptController::class,
            'wallet-transactions' => App\Http\Controllers\Admin\WalletTransactionController::class,
            'broadcast-notifications' => App\Http\Controllers\Admin\NotificationController::class,
        ];

        // Reports
        Route::prefix('reports')->name('admin.reports.')->group(function () {
            Route::get('/summary', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/users', [App\Http\Controllers\Admin\ReportController::class, 'users'])->name('users');
            Route::get('/financials', [App\Http\Controllers\Admin\ReportController::class, 'financials'])->name('financials');
            Route::get('/services', [App\Http\Controllers\Admin\ReportController::class, 'services'])->name('services');
            Route::get('/technicians', [App\Http\Controllers\Admin\ReportController::class, 'technicians'])->name('technicians');
        });

        // Profile
        Route::prefix('profile')->name('admin.profile.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('show');
            Route::get('/edit', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('edit');
            Route::post('/update', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
            Route::post('/delete-avatar', [App\Http\Controllers\Admin\ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
            Route::post('/update-password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('update-password');
        });

        // Top Technicians Route (before resource routes)
        Route::get('/technicians/top', [App\Http\Controllers\Admin\TechnicianController::class, 'top'])->name('admin.technicians.top');

        foreach ($resources as $prefix => $controller) {
            Route::prefix($prefix)->name("admin.{$prefix}.")->group(function () use ($controller, $prefix) {
                // Extra actions for specific controllers (Register before parameter routes)
                if (method_exists($controller, 'download')) {
                    Route::get('/download', [$controller, 'download'])->name('download');
                }

                Route::get('/', [$controller, 'index'])->name('index');
                Route::get('/create', [$controller, 'create'])->name('create');
                Route::post('/', [$controller, 'store'])->name('store');
                Route::get('/{id}', [$controller, 'show'])->name('show');
                Route::get('/{id}/edit', [$controller, 'edit'])->name('edit');
                Route::put('/{id}', [$controller, 'update'])->name('update');
                Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');

                if ($prefix === 'supervisors') {
                    Route::post('/bulk-block', [$controller, 'bulkBlock'])->name('bulk-block');
                    Route::post('/bulk-unblock', [$controller, 'bulkUnblock'])->name('bulk-unblock');
                }

                if (method_exists($controller, 'toggleBlock')) {
                    Route::post('/{id}/toggle-block', [$controller, 'toggleBlock'])->name('toggle-block');
                }
                if (method_exists($controller, 'accept')) {
                    Route::post('/{id}/accept', [$controller, 'accept'])->name('accept');
                }
                if (method_exists($controller, 'refuse')) {
                    Route::post('/{id}/refuse', [$controller, 'refuse'])->name('refuse');
                }
                if ($prefix === 'technicians') {
                    Route::post('/bulk-destroy', [$controller, 'bulkDestroy'])->name('bulk-destroy');
                }
                if ($prefix === 'roles') {
                    Route::post('/bulk-delete', [$controller, 'bulkDelete'])->name('bulk-delete');
                }
            });
        }

        // Blocked Users Management
        Route::prefix('blocked')->name('admin.blocked.')->group(function () {
            Route::get('/customers', [App\Http\Controllers\Admin\BlockedUserController::class, 'customers'])->name('customers');
            Route::get('/companies', [App\Http\Controllers\Admin\BlockedUserController::class, 'companies'])->name('companies');
            Route::get('/technicians', [App\Http\Controllers\Admin\BlockedUserController::class, 'technicians'])->name('technicians');
            Route::get('/supervisors', [App\Http\Controllers\Admin\BlockedUserController::class, 'supervisors'])->name('supervisors');
            Route::get('/download', [App\Http\Controllers\Admin\BlockedUserController::class, 'download'])->name('download');
            Route::post('/bulk-unblock', [App\Http\Controllers\Admin\BlockedUserController::class, 'bulkUnblock'])->name('bulk-unblock');
        });
    });
});
