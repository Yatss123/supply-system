<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SupplyRequestController;
use App\Http\Controllers\BorrowedItemController;
use App\Http\Controllers\IssuedItemController;
use App\Http\Controllers\RestockRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\ManualReceiptController;
use App\Http\Controllers\InterDepartmentLoanController;

use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QRActionController;
use App\Http\Controllers\SupplyVariantController;
use App\Http\Controllers\SupplyRequestBatchController;
use App\Http\Controllers\LoanRequestBatchController;
use App\Http\Controllers\DepartmentAllocationController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\ProfileCompletionController;
use App\Http\Controllers\TemporaryPrivilegeController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\DepartmentCartController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LocationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Health check is handled by physical file: public/health.php

// Root route - show welcome page
Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Profile completion routes (must be accessible before profile completion middleware)
    Route::get('/profile/complete', [ProfileCompletionController::class, 'show'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileCompletionController::class, 'store'])->name('profile.complete.store');
    
    // Apply profile completion middleware to all other authenticated routes
    Route::middleware('profile.complete')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Dean-only departments overview
        Route::get('/dean/departments', [DepartmentController::class, 'dean'])->name('dean.departments');
        // Dean members search endpoint (AJAX)
        Route::get('/dean/departments/members/search', [DepartmentController::class, 'searchMembers'])->name('dean.departments.members.search');
        
        // Profile routes
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/cancel-request', [ProfileController::class, 'cancelRequest'])->name('profile.cancel-request');

        // Location routes (index, create, store, show, ajax search)
        Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
        Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
        Route::get('/locations/parents', [LocationController::class, 'parents'])->name('locations.parents');
        Route::get('/locations/{location}/children', [LocationController::class, 'children'])->name('locations.children');
        Route::get('/locations/{location}/children/create', [LocationController::class, 'createChild'])->name('locations.children.create');
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
        Route::get('/locations/search', [LocationController::class, 'search'])->name('locations.search');
        Route::get('/locations/{location}', [LocationController::class, 'show'])->name('locations.show');

    // Supply routes
    Route::get('/supplies/search', [SupplyController::class, 'ajaxSearch'])->name('supplies.search');
    Route::resource('supplies', SupplyController::class);
    // Dean-friendly supplies index alias (read-only list view)
    Route::get('/dean/supplies', [SupplyController::class, 'index'])->name('dean.supplies.index')->middleware('role:dean');
    // Dean-friendly view-only supplies route (show only)
    Route::get('/dean/supplies/{supply}', [SupplyController::class, 'deanShow'])->name('dean.supplies.show')->middleware('role:dean');
    
    // Student and Adviser dedicated supply detail routes (show-only)
    Route::get('/student/supplies/{supply}', [SupplyController::class, 'studentShow'])->name('student.supplies.show');
    Route::get('/adviser/supplies/{supply}', [SupplyController::class, 'adviserShow'])->name('adviser.supplies.show');
    Route::post('/supplies/{supply}/toggle-status', [SupplyController::class, 'toggleStatus'])->name('supplies.toggle-status');
    Route::get('/supplies/{supply}/variants', [SupplyController::class, 'variants'])->name('supplies.variants');
    Route::post('/supplies/{supply}/variants', [SupplyVariantController::class, 'storeVariant'])->name('supplies.variants.store');
    Route::put('/supplies/{supply}/variants/{variant}', [SupplyController::class, 'updateVariant'])->name('supplies.variants.update');
    Route::delete('/supplies/{supply}/variants/{variant}', [SupplyController::class, 'destroyVariant'])->name('supplies.variants.destroy');
    
    // Supply status management routes
    Route::patch('/supplies/{supply}/mark-active', [SupplyController::class, 'markAsActive'])->name('supplies.mark-active');
    Route::patch('/supplies/{supply}/mark-inactive', [SupplyController::class, 'markAsInactive'])->name('supplies.mark-inactive');
        Route::patch('/supplies/{supply}/mark-damaged', [SupplyController::class, 'markAsDamaged'])->name('supplies.mark-damaged');
        Route::patch('/supplies/{supply}/assign-location', [SupplyController::class, 'assignLocation'])->name('supplies.assign-location');

    Route::patch('/supplies/{supply}/enable-variants', [SupplyController::class, 'enableVariants'])->name('supplies.enable-variants');

    // Supply Variant routes - nested under supplies
    Route::get('/supplies/{supply}/variants/create', [SupplyVariantController::class, 'create'])->name('supply-variants.create');
    Route::post('/supplies/{supply}/variants', [SupplyVariantController::class, 'store'])->name('supply-variants.store');
    Route::get('/supply-variants/{variant}', [SupplyVariantController::class, 'show'])->name('supply-variants.show');
    Route::get('/supply-variants/{variant}/edit', [SupplyVariantController::class, 'edit'])->name('supply-variants.edit');
    Route::put('/supply-variants/{variant}', [SupplyVariantController::class, 'update'])->name('supply-variants.update');
    Route::delete('/supply-variants/{variant}', [SupplyVariantController::class, 'destroy'])->name('supply-variants.destroy');
    Route::patch('/supply-variants/{variant}/disable', [SupplyVariantController::class, 'disable'])->name('supply-variants.disable');
    Route::patch('/supply-variants/{variant}/enable', [SupplyVariantController::class, 'enable'])->name('supply-variants.enable');
    Route::get('/supply-variants', [SupplyVariantController::class, 'index'])->name('supply-variants.index');
    Route::get('/issued-items/supply-variants/{supply}', [SupplyVariantController::class, 'getVariants'])->name('supply-variants.get-variants');
    Route::get('/issued-items/variant-by-sku/{sku}', [SupplyVariantController::class, 'getVariantBySku'])->name('supply-variants.get-by-sku');
    // Supply info for issued items (AJAX)
    Route::get('/issued-items/supply-info/{supply}', [IssuedItemController::class, 'getSupplyInfo'])->name('issued-items.supply-info');
    // AJAX: check for duplicate supply names
    Route::get('/supplies/check-name', [SupplyController::class, 'checkName'])->name('supplies.check-name');
    // AJAX: live search supplies across all pages

    // Category routes
    Route::resource('categories', CategoryController::class);

    // Supplier routes
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
    Route::patch('/suppliers/{supplier}/activate', [SupplierController::class, 'activate'])->name('suppliers.activate');
    Route::patch('/suppliers/{supplier}/deactivate', [SupplierController::class, 'deactivate'])->name('suppliers.deactivate');

    // Department routes
    Route::resource('departments', DepartmentController::class);
    Route::post('/departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');

    // Units routes (derived from supplies' unit values)
    Route::get('/units', [UnitsController::class, 'index'])->name('units.index');
    Route::get('/units/{unit}', [UnitsController::class, 'show'])->name('units.show');

    // Supply Request routes
    Route::resource('supply-requests', SupplyRequestController::class);
    // AJAX: Get supplies by department for existing supply requests
    Route::get('/supply-requests/department-supplies/{department}', [SupplyRequestController::class, 'departmentSupplies'])
        ->name('supply-requests.department-supplies');
    Route::patch('/supply-requests/{supplyRequest}/approve', [SupplyRequestController::class, 'approve'])->name('supply-requests.approve');
    Route::patch('/supply-requests/{supplyRequest}/reject', [SupplyRequestController::class, 'reject'])->name('supply-requests.reject');
    Route::patch('/supply-requests/{supplyRequest}/decline', [SupplyRequestController::class, 'decline'])->name('supply-requests.decline');
    Route::patch('/supply-requests/{supplyRequest}/fulfill', [SupplyRequestController::class, 'fulfill'])->name('supply-requests.fulfill');

    // Department carts (admin-managed order staging per department)
    Route::get('/department-carts/{department}', [DepartmentCartController::class, 'show'])->name('department-carts.show');
    Route::patch('/department-carts/{cart}/items/{item}', [DepartmentCartController::class, 'updateItem'])->name('department-carts.items.update');
    Route::post('/department-carts/{cart}/finalize', [DepartmentCartController::class, 'finalize'])->name('department-carts.finalize');

    // Consolidated Supply Request Batch routes
    Route::get('/supply-request-batches', [SupplyRequestBatchController::class, 'index'])->name('supply-request-batches.index');
    Route::get('/supply-request-batches/{batch}', [SupplyRequestBatchController::class, 'show'])->name('supply-request-batches.show');
    Route::post('/supply-request-batches/{batch}/approve-all', [SupplyRequestBatchController::class, 'approveAll'])->name('supply-request-batches.approve-all');
    Route::post('/supply-request-batches/{batch}/approve-selected', [SupplyRequestBatchController::class, 'approveSelected'])->name('supply-request-batches.approve-selected');

    // Loan Request Batch routes (consolidated approvals for standard loan requests)
    Route::post('/loan-request-batches/{batch}/approve-all', [LoanRequestBatchController::class, 'approveAll'])->name('loan-request-batches.approve-all');
    Route::post('/loan-request-batches/{batch}/approve-selected', [LoanRequestBatchController::class, 'approveSelected'])->name('loan-request-batches.approve-selected');
    Route::post('/loan-request-batches/{batch}/decline-all', [LoanRequestBatchController::class, 'declineAll'])->name('loan-request-batches.decline-all');

    // Borrowed Item routes
    Route::resource('borrowed-items', BorrowedItemController::class);
    Route::patch('/borrowed-items/{borrowedItem}/return', [BorrowedItemController::class, 'returnItem'])->name('borrowed-items.return');
    Route::patch('/borrowed-items/{borrowedItem}/verify-return', [BorrowedItemController::class, 'verifyReturn'])->name('borrowed-items.verify-return');

    // Issued Item routes
    Route::resource('issued-items', IssuedItemController::class);
    Route::patch('/issued-items/{issuedItem}/return', [IssuedItemController::class, 'returnItem'])->name('issued-items.return');
    Route::get('/issued-items/export', [IssuedItemController::class, 'export'])->name('issued-items.export');

    // Restock Request routes
    Route::resource('restock-requests', RestockRequestController::class);
    Route::get('/restock-requests/{restockRequest}/order', [RestockRequestController::class, 'showOrderPage'])->name('restock-requests.order');
    Route::post('/restock-requests/{restockRequest}/order', [RestockRequestController::class, 'order'])->name('restock-requests.order.submit');
    Route::patch('/restock-requests/{restockRequest}/approve', [RestockRequestController::class, 'approve'])->name('restock-requests.approve');
    Route::patch('/restock-requests/{restockRequest}/reject', [RestockRequestController::class, 'reject'])->name('restock-requests.reject');
    Route::patch('/restock-requests/{restockRequest}/fulfill', [RestockRequestController::class, 'fulfill'])->name('restock-requests.fulfill');
    Route::patch('/restock-requests/{restockRequest}/mark-delivered', [RestockRequestController::class, 'markAsDelivered'])->name('restock-requests.mark-delivered');
    Route::patch('/restock-requests/{restockRequest}/delivered', [RestockRequestController::class, 'markAsDelivered'])->name('restock-requests.delivered');

    // New to-order view routes
    Route::get('/to-order', [RestockRequestController::class, 'toOrderIndex'])->name('to-order.index');
    Route::get('/to-order/add', [RestockRequestController::class, 'toOrderAdd'])->name('to-order.add');
    Route::get('/to-order/order-list', [RestockRequestController::class, 'toOrderOrderList'])->name('to-order.order-list');
    Route::get('/to-order/create', [RestockRequestController::class, 'toOrderCreate'])->name('to-order.create');
    Route::post('/to-order/submit', [RestockRequestController::class, 'toOrderSubmit'])->name('to-order.submit');

    // User routes
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('/users/{user}/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // Temporary Privileges routes (Dean/Admin/Super Admin)
    Route::post('/users/{user}/temporary-privileges', [TemporaryPrivilegeController::class, 'assign'])->name('users.temporary-privileges.assign');
    Route::delete('/users/{user}/temporary-privileges', [TemporaryPrivilegeController::class, 'revoke'])->name('users.temporary-privileges.revoke');

    // Dean-specific user profile and access management routes
    // Dedicated endpoint for deans to manage department-scoped dean-level access
    Route::get('/dean/users/{user}/profile', [\App\Http\Controllers\DeanAccessController::class, 'showProfile'])->name('dean.users.profile');
    Route::post('/dean/users/{user}/dean-access', [\App\Http\Controllers\DeanAccessController::class, 'assign'])->name('dean.users.access.assign');
    Route::delete('/dean/users/{user}/dean-access', [\App\Http\Controllers\DeanAccessController::class, 'revoke'])->name('dean.users.access.revoke');

    // Admin Profile Request routes
    Route::get('/admin/profile-requests', [AdminProfileController::class, 'index'])->name('admin.profile-requests.index');
    Route::get('/admin/profile-requests/{profileRequest}', [AdminProfileController::class, 'show'])->name('admin.profile-requests.show');
    Route::patch('/admin/profile-requests/{profileRequest}/approve', [AdminProfileController::class, 'approve'])->name('admin.profile-requests.approve');
    Route::patch('/admin/profile-requests/{profileRequest}/reject', [AdminProfileController::class, 'reject'])->name('admin.profile-requests.reject');

    // Loan Request routes
    Route::resource('loan-requests', LoanRequestController::class);
    // Supply-scoped loan request creation (multi-item, constrained to given supply)
    Route::post('/loan-requests/{supply}', [LoanRequestController::class, 'storeForSupply'])->name('loan-requests.store-for-supply');
    Route::patch('/loan-requests/{loanRequest}/dean-approve', [LoanRequestController::class, 'deanApprove'])->name('loan-requests.dean-approve');
    Route::patch('/loan-requests/{loanRequest}/approve', [LoanRequestController::class, 'approve'])->name('loan-requests.approve');
    Route::patch('/loan-requests/{loanRequest}/issue', [LoanRequestController::class, 'issue'])->name('loan-requests.issue');
    Route::patch('/loan-requests/{loanRequest}/reject', [LoanRequestController::class, 'reject'])->name('loan-requests.reject');
    Route::patch('/loan-requests/{loanRequest}/decline', [LoanRequestController::class, 'decline'])->name('loan-requests.decline');
    Route::patch('/loan-requests/{loanRequest}/fulfill', [LoanRequestController::class, 'fulfill'])->name('loan-requests.fulfill');
    // Standard loan multi-item return form and action
    Route::get('/loan-requests/{loanRequest}/return-form', [LoanRequestController::class, 'returnForm'])->name('loan-requests.return-form');
    Route::patch('/loan-requests/{loanRequest}/return', [LoanRequestController::class, 'initiateReturn'])->name('loan-requests.return');
    // Bulk verify all pending returns for a loan request (admin-only)
    Route::patch('/loan-requests/{loanRequest}/verify-return', [LoanRequestController::class, 'verifyReturnBulk'])->name('loan-requests.verify-return');
    // Verify selected pending returns with per-item statuses (admin-only)
    Route::patch('/loan-requests/{loanRequest}/verify-return-selected', [LoanRequestController::class, 'verifyReturnSelected'])->name('loan-requests.verify-return-selected');

    // Consolidated Inter-Department Loan routes under /loan-requests for backward compatibility and migration
    Route::prefix('loan-requests/inter-department')->name('loan-requests.inter-department.')->group(function () {
        // Index, create, show
        // Route disabled to restore welcome page at '/'
        // Route::get('/', [InterDepartmentLoanController::class, 'index'])->name('index');
        Route::get('/create', [InterDepartmentLoanController::class, 'create'])->name('create');
        Route::post('/', [InterDepartmentLoanController::class, 'store'])->name('store');
        Route::get('/{interDepartmentLoan}', [InterDepartmentLoanController::class, 'show'])->name('show');
        Route::get('/{interDepartmentLoan}/edit', [InterDepartmentLoanController::class, 'edit'])->name('edit');
        Route::patch('/{interDepartmentLoan}', [InterDepartmentLoanController::class, 'update'])->name('update');
        Route::delete('/{interDepartmentLoan}', [InterDepartmentLoanController::class, 'destroy'])->name('destroy');

        // Action endpoints mirrored under consolidated path
        Route::patch('/{interDepartmentLoan}/approve', [InterDepartmentLoanController::class, 'approve'])->name('approve');
        Route::patch('/{interDepartmentLoan}/reject', [InterDepartmentLoanController::class, 'reject'])->name('reject');
        Route::patch('/{interDepartmentLoan}/decline', [InterDepartmentLoanController::class, 'decline'])->name('decline');
        Route::patch('/{interDepartmentLoan}/fulfill', [InterDepartmentLoanController::class, 'fulfill'])->name('fulfill');
        Route::patch('/{interDepartmentLoan}/return', [InterDepartmentLoanController::class, 'initiateReturn'])->name('return');
        Route::get('/{interDepartmentLoan}/return-form', [InterDepartmentLoanController::class, 'returnForm'])->name('return-form');
        Route::patch('/{interDepartmentLoan}/returns/{returnRecord}', [InterDepartmentLoanController::class, 'updateReturn'])->name('return.update');
        Route::patch('/{interDepartmentLoan}/verify-return', [InterDepartmentLoanController::class, 'verifyReturn'])->name('verify-return');
        Route::patch('/{interDepartmentLoan}/approve-lending', [InterDepartmentLoanController::class, 'approveLending'])->name('approve-lending');
        Route::patch('/{interDepartmentLoan}/dean-approve', [InterDepartmentLoanController::class, 'deanApprove'])->name('dean-approve');
        Route::patch('/{interDepartmentLoan}/lending-dean-approve', [InterDepartmentLoanController::class, 'lendingDeanApprove'])->name('lending-dean-approve');
        Route::patch('/{interDepartmentLoan}/admin-approve', [InterDepartmentLoanController::class, 'adminApprove'])->name('admin-approve');
        Route::patch('/{interDepartmentLoan}/lending-approve', [InterDepartmentLoanController::class, 'lendingApprove'])->name('lending-approve');
    });

    // Manual Receipt routes - redirected to supplies tab for better UX
    Route::get('/manual-receipts', function() {
        return redirect()->route('supplies.index', ['tab' => 'receipts']);
    })->name('manual-receipts.index');
    
    Route::get('/manual-receipts/create', function() {
        return redirect()->route('supplies.index', ['tab' => 'receipts']);
    })->name('manual-receipts.create');
    
    Route::get('/manual-receipts/{manualReceipt}', function($id) {
        return redirect()->route('supplies.index', ['tab' => 'receipts']);
    })->name('manual-receipts.show');
    
    Route::get('/manual-receipts/{manualReceipt}/edit', function($id) {
        return redirect()->route('supplies.index', ['tab' => 'receipts']);
    })->name('manual-receipts.edit');
    
    // Keep the actual functionality routes for AJAX operations
    Route::post('/manual-receipts', [ManualReceiptController::class, 'store'])->name('manual-receipts.store');
    Route::put('/manual-receipts/{manualReceipt}', [ManualReceiptController::class, 'update'])->name('manual-receipts.update');
    Route::delete('/manual-receipts/{manualReceipt}', [ManualReceiptController::class, 'destroy'])->name('manual-receipts.destroy');
    Route::patch('/manual-receipts/{manualReceipt}/approve', [ManualReceiptController::class, 'approve'])->name('manual-receipts.approve');
    Route::patch('/manual-receipts/{manualReceipt}/reject', [ManualReceiptController::class, 'reject'])->name('manual-receipts.reject');

    // Inter-department loan routes
    Route::resource('inter-department-loans', InterDepartmentLoanController::class);
    Route::patch('/inter-department-loans/{interDepartmentLoan}/approve', [InterDepartmentLoanController::class, 'approve'])->name('inter-department-loans.approve');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/reject', [InterDepartmentLoanController::class, 'reject'])->name('inter-department-loans.reject');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/decline', [InterDepartmentLoanController::class, 'decline'])->name('inter-department-loans.decline');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/fulfill', [InterDepartmentLoanController::class, 'fulfill'])->name('inter-department-loans.fulfill');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/return', [InterDepartmentLoanController::class, 'initiateReturn'])->name('inter-department-loans.return');
    Route::get('/inter-department-loans/{interDepartmentLoan}/return-form', [InterDepartmentLoanController::class, 'returnForm'])->name('inter-department-loans.return-form');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/returns/{returnRecord}', [InterDepartmentLoanController::class, 'updateReturn'])->name('inter-department-loans.return.update');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/verify-return', [InterDepartmentLoanController::class, 'verifyReturn'])->name('inter-department-loans.verify-return');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/approve-lending', [InterDepartmentLoanController::class, 'approveLending'])->name('inter-department-loans.approve-lending');
    
    Route::patch('/inter-department-loans/{interDepartmentLoan}/dean-approve', [InterDepartmentLoanController::class, 'deanApprove'])->name('inter-department-loans.dean-approve');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/lending-dean-approve', [InterDepartmentLoanController::class, 'lendingDeanApprove'])->name('inter-department-loans.lending-dean-approve');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/admin-approve', [InterDepartmentLoanController::class, 'adminApprove'])->name('inter-department-loans.admin-approve');
    Route::patch('/inter-department-loans/{interDepartmentLoan}/lending-approve', [InterDepartmentLoanController::class, 'lendingApprove'])->name('inter-department-loans.lending-approve');

    // [Removed] QR testing routes and QR supply action endpoints

    // QR Action routes
    Route::middleware('auth')->prefix('qr')->name('qr.')->group(function () {
        Route::get('/actions/{supply}', [QRActionController::class, 'index'])->name('actions');
        Route::get('/quick-issue/{supply}', [QRActionController::class, 'quickIssue'])->name('quick-issue');
        Route::post('/quick-issue/{supply}', [QRActionController::class, 'processQuickIssue'])->name('process-quick-issue');
        Route::get('/borrow-request/{supply}', [QRActionController::class, 'quickBorrowRequest'])->name('borrow-request');
        Route::post('/borrow-request/{supply}', [QRActionController::class, 'processQuickBorrowRequest'])->name('process-borrow-request');
        Route::post('/approve-loan/{loanRequest}', [QRActionController::class, 'approveLoanRequest'])->name('approve-loan');
        Route::get('/return/{supply}', [QRActionController::class, 'quickReturn'])->name('return');
        Route::post('/return/{supply}', [QRActionController::class, 'processQuickReturn'])->name('process-return');
        Route::get('/supply-request/{supply}', [QRActionController::class, 'quickSupplyRequest'])->name('supply-request');
        Route::post('/supply-request/{supply}', [QRActionController::class, 'processQuickSupplyRequest'])->name('process-supply-request');
        Route::get('/status-change/{supply}', [QRActionController::class, 'quickStatusChange'])->name('status-change');
        Route::post('/status-change/{supply}', [QRActionController::class, 'processQuickStatusChange'])->name('process-status-change');
        Route::get('/borrowing-info/{supply}', [QRActionController::class, 'viewBorrowingInfo'])->name('borrowing-info');
    });

    // QR Code image generation (signed URLs)
        Route::get('/qr-code/supply/{supply}/{action}', [QrCodeController::class, 'generate'])
            ->middleware('auth')
            ->name('qr.code.generate');

        // Admin monthly allocations overview
        Route::get('/admin/allocations', [\App\Http\Controllers\DepartmentAllocationController::class, 'index'])
            ->name('admin.allocations.index');
        Route::post('/admin/allocations/{department}/refresh', [\App\Http\Controllers\DepartmentAllocationController::class, 'adminRefresh'])
            ->name('admin.allocations.refresh');
        Route::patch('/admin/allocations/{department}/status', [\App\Http\Controllers\DepartmentAllocationController::class, 'updateStatus'])
            ->name('admin.allocations.update-status');

        // Admin allocation item configuration
        Route::patch('/admin/allocations/items/{item}/max-limit', [DepartmentAllocationController::class, 'updateItemMaxLimit'])
            ->name('admin.allocations.items.update-max');
        Route::patch('/admin/allocations/items/{item}/issue-qty', [DepartmentAllocationController::class, 'updateItemIssueQty'])
            ->name('admin.allocations.items.update-issue-qty');
        // Admin add/remove items
        Route::patch('/admin/allocations/items/{item}/remove', [DepartmentAllocationController::class, 'removeItem'])
            ->name('admin.allocations.items.remove');
        Route::post('/admin/allocations/{department}/items/add', [DepartmentAllocationController::class, 'addItem'])
            ->name('admin.allocations.items.add');
        Route::post('/admin/allocations/{department}/items/add-multiple', [DepartmentAllocationController::class, 'addMultipleItems'])
            ->name('admin.allocations.items.add-multi');

        // Admin replenish-to-max issuance
        Route::post('/admin/allocations/{department}/issue-to-max', [\App\Http\Controllers\DepartmentAllocationController::class, 'issueToMax'])
            ->name('admin.allocations.issue-to-max');

        // Admin staged issuing workflow
        Route::post('/admin/allocations/{department}/stage-issue', [\App\Http\Controllers\DepartmentAllocationController::class, 'stageIssueSelected'])
            ->name('admin.allocations.stage-issue');
        Route::post('/admin/allocations/{department}/issue-selected', [\App\Http\Controllers\DepartmentAllocationController::class, 'issueSelected'])
            ->name('admin.allocations.issue-selected');

        // Dean monthly allocation routes
        Route::get('/dean/allocations/{department}', [\App\Http\Controllers\DepartmentAllocationController::class, 'show'])
            ->name('dean.allocations.show');
        Route::post('/dean/allocations/{department}/refresh-cart', [\App\Http\Controllers\DepartmentAllocationController::class, 'refreshCart'])
            ->name('dean.allocations.refresh-cart');
        Route::patch('/dean/allocations/items/{item}/min-stock', [\App\Http\Controllers\DepartmentAllocationController::class, 'updateItemMinLevel'])
            ->name('dean.allocations.items.update-min');
        Route::post('/dean/allocations/{department}/update-actual', [\App\Http\Controllers\DepartmentAllocationController::class, 'updateActualAvailability'])
            ->name('dean.allocations.update-actual');
        Route::post('/dean/allocations/{department}/update-reminder-day', [\App\Http\Controllers\DepartmentAllocationController::class, 'updateReminderDay'])
            ->name('dean.allocations.update-reminder-day');

        // In-app notifications: mark all as read
        Route::post('/notifications/mark-all-read', function() {
            $user = auth()->user();
            if ($user) {
                $user->unreadNotifications->markAsRead();
            }
            return back();
        })->name('notifications.mark-all-read');

        }); // Close profile.complete middleware group
    }); // Close auth middleware group

require __DIR__.'/auth.php';
use App\Http\Controllers\ReportsController;
// Reports: Missing and Damaged items
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/missing-items', [ReportsController::class, 'missingItems'])->name('reports.missing-items');
    Route::get('/reports/damaged-items', [ReportsController::class, 'damagedItems'])->name('reports.damaged-items');
    // Issued and Ordered activity reports
    Route::get('/reports/issued-activity', [ReportsController::class, 'issuedActivity'])->name('reports.issued-activity');
    Route::get('/reports/ordered-activity', [ReportsController::class, 'orderedActivity'])->name('reports.ordered-activity');
});
