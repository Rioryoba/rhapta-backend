
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::apiResource('departments', App\Http\Controllers\API\DepartmentController::class)->middleware(['auth:api', 'role:admin,hr']);
Route::apiResource('customers', App\Http\Controllers\API\CustomerController::class);
Route::apiResource('contract-types', App\Http\Controllers\API\ContractTypeController::class);
Route::apiResource('customer-contracts', App\Http\Controllers\API\CustomerContractController::class);
Route::apiResource('contracts', App\Http\Controllers\API\ContractController::class);
Route::apiResource('audit-logs', App\Http\Controllers\API\AuditLogController::class)->middleware(['auth:api', 'role:admin,hr']);
Route::apiResource('assets', App\Http\Controllers\API\AssetController::class);
Route::post('attendences/check-in', [App\Http\Controllers\API\AttendenceController::class, 'checkIn'])->middleware(['auth:api']);
Route::post('attendences/check-out', [App\Http\Controllers\API\AttendenceController::class, 'checkOut'])->middleware(['auth:api']);
Route::apiResource('attendences', App\Http\Controllers\API\AttendenceController::class)->middleware(['auth:api']);
// Employees route - allow accountants to view employees for payroll purposes
Route::get('employees', [App\Http\Controllers\API\EmployeeController::class, 'index'])->middleware(['auth:api', 'role:admin,hr,accountant']);
Route::get('employees/{employee}', [App\Http\Controllers\API\EmployeeController::class, 'show'])->middleware(['auth:api', 'role:admin,hr,accountant']);
Route::apiResource('employees', App\Http\Controllers\API\EmployeeController::class)->middleware(['auth:api', 'role:admin,hr'])->except(['index', 'show']);
Route::apiResource('employee-contracts', App\Http\Controllers\API\EmployeeContractController::class);
Route::apiResource('documents', App\Http\Controllers\API\DocumentController::class);
// Suppliers, Sites, Purchase Orders routes
Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('suppliers', App\Http\Controllers\API\SupplierController::class);
    Route::apiResource('sites', App\Http\Controllers\API\SiteController::class);
    Route::apiResource('purchase-orders', App\Http\Controllers\API\PurchaseOrderController::class);
});
// Expenses routes: all require authentication; create/update require admin or accountant
Route::middleware(['auth:api'])->group(function () {
    Route::get('expenses', [App\Http\Controllers\API\ExpenseController::class, 'index']);
    Route::get('expenses/{expense}', [App\Http\Controllers\API\ExpenseController::class, 'show']);
    Route::post('expenses', [App\Http\Controllers\API\ExpenseController::class, 'store'])->middleware('role:admin,accountant');
    Route::put('expenses/{expense}', [App\Http\Controllers\API\ExpenseController::class, 'update'])->middleware('role:admin,accountant');
    Route::patch('expenses/{expense}', [App\Http\Controllers\API\ExpenseController::class, 'update'])->middleware('role:admin,accountant');
    Route::delete('expenses/{expense}', [App\Http\Controllers\API\ExpenseController::class, 'destroy'])->middleware('role:admin');
});
// Budgets routes: all require authentication; create/update/delete require admin or accountant
Route::middleware(['auth:api'])->group(function () {
    Route::get('budgets', [App\Http\Controllers\API\BudgetController::class, 'index']);
    Route::get('budgets/forecast', [App\Http\Controllers\API\BudgetController::class, 'forecast']);
    Route::get('budgets/{budget}', [App\Http\Controllers\API\BudgetController::class, 'show']);
    Route::post('budgets', [App\Http\Controllers\API\BudgetController::class, 'store'])->middleware('role:admin,accountant');
    Route::put('budgets/{budget}', [App\Http\Controllers\API\BudgetController::class, 'update'])->middleware('role:admin,accountant');
    Route::patch('budgets/{budget}', [App\Http\Controllers\API\BudgetController::class, 'update'])->middleware('role:admin,accountant');
    Route::delete('budgets/{budget}', [App\Http\Controllers\API\BudgetController::class, 'destroy'])->middleware('role:admin,accountant');
});
// Only accountants can create invoices
Route::middleware(['auth:api', 'role:accountant'])->group(function () {
    Route::post('invoices', [App\Http\Controllers\API\InvoiceController::class, 'store']);
    Route::apiResource('invoices', App\Http\Controllers\API\InvoiceController::class)->except(['store']);
    Route::post('invoices/{invoice}/payments', [App\Http\Controllers\API\InvoiceController::class, 'payInvoice']);
});
// Other invoice actions remain open (customize as needed)
Route::apiResource('leaves', App\Http\Controllers\API\LeaveController::class);
Route::apiResource('performances', App\Http\Controllers\API\PerformanceController::class)->middleware(['auth:api']);
Route::apiResource('trainings', App\Http\Controllers\API\TrainingController::class)->middleware(['auth:api']);
Route::apiResource('maintenance-records', App\Http\Controllers\API\MaintenanceRecordController::class);
// Notifications routes - require authentication and staff role
Route::middleware(['auth:api', 'role:staff,admin,hr'])->group(function () {
    Route::get('notifications', [App\Http\Controllers\API\NotificationController::class, 'index']);
    Route::post('notifications', [App\Http\Controllers\API\NotificationController::class, 'store']);
    Route::get('notifications/{notification}', [App\Http\Controllers\API\NotificationController::class, 'show']);
    Route::put('notifications/{notification}', [App\Http\Controllers\API\NotificationController::class, 'update']);
    Route::patch('notifications/{notification}', [App\Http\Controllers\API\NotificationController::class, 'update']);
    Route::delete('notifications/{notification}', [App\Http\Controllers\API\NotificationController::class, 'destroy']);
    Route::put('notifications/{notification}/read', [App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
    Route::put('notifications/mark-all-read', [App\Http\Controllers\API\NotificationController::class, 'markAllAsRead']);
    Route::put('notifications/{notification}/archive', [App\Http\Controllers\API\NotificationController::class, 'archive']);
});
Route::apiResource('payments', App\Http\Controllers\API\PaymentController::class);
Route::apiResource('payrolls', App\Http\Controllers\API\PayrollController::class)->middleware(['auth:api']);
Route::get('payrolls/attendance-summary', [App\Http\Controllers\API\PayrollController::class, 'getAttendanceSummary'])->middleware(['auth:api']);
Route::post('payrolls/calculate', [App\Http\Controllers\API\PayrollController::class, 'calculatePayroll'])->middleware(['auth:api']);
Route::apiResource('positions', App\Http\Controllers\API\PositionController::class)->middleware(['auth:api', 'role:admin,hr']);
Route::apiResource('products', App\Http\Controllers\API\ProductController::class);
Route::apiResource('projects', App\Http\Controllers\API\ProjectController::class);
// Roles: allow HR and admin to view roles (for assigning to employees), but only admin can create/update/delete
Route::middleware(['auth:api'])->group(function () {
    Route::get('roles', [App\Http\Controllers\API\RoleController::class, 'index'])->middleware('role:admin,hr');
    Route::get('roles/{role}', [App\Http\Controllers\API\RoleController::class, 'show'])->middleware('role:admin,hr');
});
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('roles', [App\Http\Controllers\API\RoleController::class, 'store']);
    Route::put('roles/{role}', [App\Http\Controllers\API\RoleController::class, 'update']);
    Route::patch('roles/{role}', [App\Http\Controllers\API\RoleController::class, 'update']);
    Route::delete('roles/{role}', [App\Http\Controllers\API\RoleController::class, 'destroy']);
});
// Sales routes: require authentication; create/update/delete requires admin or accountant
Route::middleware(['auth:api'])->group(function () {
    Route::get('sales', [App\Http\Controllers\API\SaleController::class, 'index']);
    Route::get('sales/{sale}', [App\Http\Controllers\API\SaleController::class, 'show']);
    Route::post('sales', [App\Http\Controllers\API\SaleController::class, 'store'])->middleware('role:admin,accountant');
    Route::put('sales/{sale}', [App\Http\Controllers\API\SaleController::class, 'update'])->middleware('role:admin,accountant');
    Route::patch('sales/{sale}', [App\Http\Controllers\API\SaleController::class, 'update'])->middleware('role:admin,accountant');
    Route::delete('sales/{sale}', [App\Http\Controllers\API\SaleController::class, 'destroy'])->middleware('role:admin,accountant');
});
Route::apiResource('sales-orders', App\Http\Controllers\API\SalesOrderController::class);
Route::apiResource('tasks', App\Http\Controllers\API\TasksController::class);
// Accounts routes: create/update protected by role middleware; deletion disabled
Route::get('accounts', [App\Http\Controllers\API\AccountController::class, 'index'])->middleware(['auth:api','role:accountant,ceo']);
Route::get('accounts/{account}', [App\Http\Controllers\API\AccountController::class, 'show'])->middleware(['auth:api','role:accountant,ceo']);
Route::post('accounts', [App\Http\Controllers\API\AccountController::class, 'store'])->middleware(['auth:api','role:accountant,ceo']);
Route::put('accounts/{account}', [App\Http\Controllers\API\AccountController::class, 'update'])->middleware(['auth:api','role:accountant']);
Route::patch('accounts/{account}', [App\Http\Controllers\API\AccountController::class, 'update'])->middleware(['auth:api','role:accountant']);
// Note: no DELETE route for accounts (deletion forbidden)

// Account transactions: manual deposits/withdrawals
Route::middleware(['auth:api'])->group(function () {
    Route::get('account-transactions', [App\Http\Controllers\API\AccountTransactionController::class, 'index']);
    Route::post('account-transactions', [App\Http\Controllers\API\AccountTransactionController::class, 'store'])->middleware('role:accountant,admin');
});

// Journal entries: double-entry bookkeeping
Route::middleware(['auth:api', 'role:accountant,ceo'])->group(function () {
    Route::get('journal-entries', [App\Http\Controllers\API\JournalEntryController::class, 'index']);
    Route::get('journal-entries/{journalEntry}', [App\Http\Controllers\API\JournalEntryController::class, 'show']);
    Route::post('journal-entries', [App\Http\Controllers\API\JournalEntryController::class, 'store']);
    // Note: deletion disabled for journal entries (create reversing entry instead)
});

// Auth routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [App\Http\Controllers\API\auth\AuthentificationController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\API\auth\AuthentificationController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [App\Http\Controllers\API\auth\AuthentificationController::class, 'refresh'])->middleware('auth:api');
});

// Auth routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [App\Http\Controllers\API\auth\AuthentificationController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\API\auth\AuthentificationController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [App\Http\Controllers\API\auth\AuthentificationController::class, 'refresh'])->middleware('auth:api');
    Route::middleware(['auth:api', 'role:admin'])->post('register', [App\Http\Controllers\API\AuthController::class, 'register']);
});

// Example of protecting routes by role
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::apiResource('users', App\Http\Controllers\API\UserController::class);
    // Add other admin-only routes here
});

//check protected routes to velify role
Route::middleware('auth:api')->get('/user', function (Request $request) {
    $user = $request->user();
    $user->load('role');
    return response()->json([
        'id' => $user->id,
        'name' => $user->user_name,
        'email' => $user->email,
        'role' => $user->role ? $user->role->name : null,
        'employee_id' => $user->employee_id,
    ]);
});

Route::middleware(['auth:api', 'role:user'])->group(function () {
    // Add user-only routes here
});


// User password setup route (first login)
Route::post('users/set-password', [App\Http\Controllers\API\UserController::class, 'setPassword'])->middleware('auth:api');

// User password change route (requires current password)
Route::post('users/change-password', [App\Http\Controllers\API\UserController::class, 'changePassword'])->middleware('auth:api');

// User profile update route (name and email) - placed before apiResource to avoid route conflict
Route::put('user/profile', [App\Http\Controllers\API\UserController::class, 'updateProfile'])->middleware('auth:api');



Route::middleware(['auth:api', 'role:hr'])->group(function () {
    Route::post('employees', [App\Http\Controllers\API\EmployeeController::class, 'store']);
    Route::put('employees/{employee}', [App\Http\Controllers\API\EmployeeController::class, 'update']);
    Route::delete('employees/{employee}', [App\Http\Controllers\API\EmployeeController::class, 'destroy']);
});
// Optionally, restrict or remove Route::apiResource('employees', ...) if you want only HRs to manage employees.


// Settings routes
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('settings', [App\Http\Controllers\API\SettingController::class, 'store']);
    Route::put('settings/{setting}', [App\Http\Controllers\API\SettingController::class, 'update']);
});
Route::apiResource('settings', App\Http\Controllers\API\SettingController::class)->only(['index', 'show']);


//request routes
Route::apiResource('requests', App\Http\Controllers\API\RequestController::class)->middleware(['auth:api']);
Route::post('requests/{request}/manager-approve', [App\Http\Controllers\API\RequestController::class, 'managerApprove'])->middleware(['auth:api']);
Route::post('requests/{request}/ceo-approve', [App\Http\Controllers\API\RequestController::class, 'ceoApprove'])->middleware(['auth:api', 'role:ceo']);
Route::post('requests/{request}/accountant-process', [App\Http\Controllers\API\RequestController::class, 'accountantProcess'])->middleware(['auth:api', 'role:accountant']);
Route::post('requests/{request}/reject', [App\Http\Controllers\API\RequestController::class, 'reject'])->middleware(['auth:api']);

// Only accountants can create invoice items
Route::middleware(['auth:api', 'role:accountant'])->group(function () {
    Route::post('invoice-items', [App\Http\Controllers\API\InvoiceItemController::class, 'store']);
    Route::apiResource('invoice-items', App\Http\Controllers\API\InvoiceItemController::class)->except(['store'])->middleware(['auth:api']);

});
// Other invoice item actions remain open (customize as needed)
Route::apiResource('invoice-items', App\Http\Controllers\API\InvoiceItemController::class)->except(['store'])->middleware(['auth:api']);

// Invoice PDF download
Route::get('invoices/{id}/download-pdf', [App\Http\Controllers\API\InvoicePdfController::class, 'download']);

// Invoice HTML preview (for browser viewing/printing)
Route::get('invoices/{id}/preview', [App\Http\Controllers\API\InvoicePrintController::class, 'show']);

// Reports routes: require accountant or admin role
Route::middleware(['auth:api', 'role:accountant,admin'])->group(function () {
    Route::get('reports/expenses', [App\Http\Controllers\API\ReportController::class, 'expenseReport']);
    Route::get('reports/expenses/download', [App\Http\Controllers\API\ReportController::class, 'downloadExpenseReport']);
});

// Dashboard routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('dashboard/hr-statistics', [App\Http\Controllers\API\DashboardController::class, 'hrStatistics'])->middleware('role:hr,admin');
    Route::get('dashboard/accountant-statistics', [App\Http\Controllers\API\DashboardController::class, 'accountantStatistics'])->middleware('role:accountant,admin,ceo');
});

// Income & Sales Summary route
Route::middleware(['auth:api', 'role:accountant,admin,ceo'])->group(function () {
    Route::get('income-sales/summary', [App\Http\Controllers\API\IncomeSalesSummaryController::class, 'index']);
});