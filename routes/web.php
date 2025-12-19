<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - All Authenticated Users
Route::middleware('auth')->group(function () {
    // Dashboard - Everyone can see
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Preferences - Everyone can save their own preferences
    Route::post('preferences/columns', [PreferenceController::class, 'storeColumns'])->name('preferences.columns');

    // Jobs - View for everyone, edit restricted by controller
    Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    
    // Vehicles - View for everyone
    Route::get('vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    // Customers - View for everyone
    Route::get('customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/duplicates', [\App\Http\Controllers\CustomerController::class, 'duplicates'])->name('customers.duplicates');
    Route::post('customers/merge', [\App\Http\Controllers\CustomerController::class, 'merge'])->name('customers.merge');
    Route::post('customers/merge-batch', [\App\Http\Controllers\CustomerController::class, 'mergeBatch'])->name('customers.merge-batch');
    Route::get('customers/show', [\App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/search', [\App\Http\Controllers\CustomerController::class, 'search'])->name('customers.search');

    // Reports - Everyone can view reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('uninvoiced', [ReportController::class, 'uninvoiced'])->name('uninvoiced');
        Route::get('invoiced', [ReportController::class, 'invoiced'])->name('invoiced');
        Route::get('needs-parts', [ReportController::class, 'needsParts'])->name('needs-parts');
        Route::get('customer-merges', [ReportController::class, 'customerMerges'])->name('customer-merges');
        Route::get('customer-merges/export', [ReportController::class, 'exportCustomerMerges'])->name('customer-merges.export');
    });

    // Add Remarks - SA, Foreman, Sparepart, Control Tower, Manager, Admin
    Route::middleware('role:sa,foreman,sparepart,control_tower,manager,admin')->group(function () {
        Route::post('jobs/{job}/remark', [JobController::class, 'addRemark'])->name('jobs.add-remark');
    });

    // Sparepart can update Order & Parts on jobs that need parts
    Route::middleware('role:sparepart,control_tower,manager,admin')->group(function () {
        Route::patch('jobs/{job}/order-parts', [JobController::class, 'updateOrderParts'])->name('jobs.update-order-parts');
    });

    // Edit Operations - Control Tower, Manager, Admin (NO DELETE)
    Route::middleware('role:control_tower,manager,admin')->group(function () {
        // Jobs CRUD (except index/show which are public, and destroy which is admin-only)
        Route::get('jobs/create', [JobController::class, 'create'])->name('jobs.create');
        Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
        Route::get('jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
        Route::put('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
        Route::post('jobs/{job}/mark-invoiced', [JobController::class, 'markInvoiced'])->name('jobs.mark-invoiced');

        // Vehicles CRUD (except destroy)
        Route::get('vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::post('vehicles/{vehicle}/toggle-workshop', [VehicleController::class, 'toggleWorkshop'])->name('vehicles.toggle-workshop');
        Route::post('vehicles/bulk-workshop', [VehicleController::class, 'bulkUpdateWorkshop'])->name('vehicles.bulk-workshop');

        // Bookings, PDI, Towing (except destroy)
        Route::resource('bookings', \App\Http\Controllers\BookingController::class)->except(['destroy']);
        Route::resource('pdi-records', \App\Http\Controllers\PdiRecordController::class)->except(['destroy']);
        Route::resource('towing-records', \App\Http\Controllers\TowingRecordController::class)->except(['destroy']);

        // Master Data (except destroy)
        Route::resource('service-advisors', \App\Http\Controllers\ServiceAdvisorController::class)->except(['destroy']);
        Route::resource('foremen', \App\Http\Controllers\ForemanController::class)->except(['destroy']);

        // Imports
        Route::prefix('imports')->name('imports.')->group(function () {
            Route::get('/', [ImportController::class, 'index'])->name('index');
            Route::get('upload', [ImportController::class, 'showUploadForm'])->name('upload');
            Route::post('progress', [ImportController::class, 'importProgress'])->name('progress');
            Route::post('uninvoiced', [ImportController::class, 'importUninvoiced'])->name('uninvoiced');
            Route::post('invoiced', [ImportController::class, 'importInvoiced'])->name('invoiced');
            Route::get('{import}', [ImportController::class, 'show'])->name('show'); // Must be last!
        });

        // Report Exports
        Route::get('reports/export/uninvoiced', [ReportController::class, 'exportUninvoiced'])->name('reports.export-uninvoiced');
        Route::get('reports/export/needs-parts', [ReportController::class, 'exportNeedsParts'])->name('reports.export-needs-parts');
        
        // Report Builder
        Route::get('reports/builder', [ReportController::class, 'builder'])->name('reports.builder');
        Route::get('reports/preview', [ReportController::class, 'preview'])->name('reports.preview');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::post('reports/save', [ReportController::class, 'saveReport'])->name('reports.save');
        Route::get('reports/{report}/load', [ReportController::class, 'loadReport'])->name('reports.load');
        Route::delete('reports/{report}', [ReportController::class, 'deleteReport'])->name('reports.delete');
    });

    // Sparepart can update need_part field - Sparepart, Control Tower, Manager, Admin
    Route::middleware('role:sparepart,control_tower,manager,admin')->group(function () {
        Route::patch('jobs/{job}/need-part', [JobController::class, 'updateNeedPart'])->name('jobs.update-need-part');
    });

    // Admin Only - User Management, LDAP Settings, DELETE operations
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // User Management
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::post('users/search-ldap', [\App\Http\Controllers\Admin\UserController::class, 'searchLdap'])->name('users.search-ldap');
        Route::post('users/assign-role', [\App\Http\Controllers\Admin\UserController::class, 'assignRole'])->name('users.assign-role');

        // LDAP Settings
        Route::get('ldap', [\App\Http\Controllers\LdapServerController::class, 'index'])->name('ldap.index');
        Route::get('ldap/create', [\App\Http\Controllers\LdapServerController::class, 'create'])->name('ldap.create');
        Route::post('ldap', [\App\Http\Controllers\LdapServerController::class, 'store'])->name('ldap.store');
        Route::get('ldap/{ldapServer}/edit', [\App\Http\Controllers\LdapServerController::class, 'edit'])->name('ldap.edit');
        Route::put('ldap/{ldapServer}', [\App\Http\Controllers\LdapServerController::class, 'update'])->name('ldap.update');
        Route::delete('ldap/{ldapServer}', [\App\Http\Controllers\LdapServerController::class, 'destroy'])->name('ldap.destroy');
        Route::get('ldap/{ldapServer}/test', [\App\Http\Controllers\LdapServerController::class, 'testConnection'])->name('ldap.test');

        // Data Cleanup
        Route::get('data-cleanup', [\App\Http\Controllers\Admin\DataCleanupController::class, 'index'])->name('data-cleanup.index');
        Route::post('data-cleanup', [\App\Http\Controllers\Admin\DataCleanupController::class, 'cleanup'])->name('data-cleanup.execute');
    });

    // Delete operations - Admin only (outside prefix to keep normal route names)
    Route::middleware('role:admin')->group(function () {
        Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
        Route::delete('bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'destroy'])->name('bookings.destroy');
        Route::delete('pdi-records/{pdi_record}', [\App\Http\Controllers\PdiRecordController::class, 'destroy'])->name('pdi-records.destroy');
        Route::delete('towing-records/{towing_record}', [\App\Http\Controllers\TowingRecordController::class, 'destroy'])->name('towing-records.destroy');
        Route::delete('service-advisors/{service_advisor}', [\App\Http\Controllers\ServiceAdvisorController::class, 'destroy'])->name('service-advisors.destroy');
        Route::delete('foremen/{foreman}', [\App\Http\Controllers\ForemanController::class, 'destroy'])->name('foremen.destroy');
    });

    // Audit - Admin and Audit role
    Route::middleware('role:audit,admin')->group(function () {
        Route::get('audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('tracker', [\App\Http\Controllers\TrackerController::class, 'index'])->name('tracker.index');
    });
});
