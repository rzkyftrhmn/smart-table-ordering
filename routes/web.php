<?php

use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CashierPaymentController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerReportExportController;
use App\Http\Controllers\Admin\RestaurantLocationController;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\KitchenQueue;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Shift;
use App\Models\User;
use App\Services\Reports\FinancialReportService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
| Route di sini hanya bisa diakses user yang belum login.
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->name('login.process');

    require __DIR__.'/members/gilang.php';
    require __DIR__.'/members/fatur.php';
});

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
| Route di sini hanya bisa diakses user yang sudah login.
| Logout dan fitur umum user login taruh di sini.
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::prefix('notifications')
        ->name('notifications.')
        ->group(function () {
            Route::get('/', [NotificationController::class, 'index'])
                ->name('index');

            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])
                ->name('unread-count');

            Route::post('/read-all', [NotificationController::class, 'readAll'])
                ->name('read-all');

            Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])
                ->name('read');
        });

    /*
    |--------------------------------------------------------------------------
    | Admin routes
    |--------------------------------------------------------------------------
    | Khusus user login dengan role admin.
    */

    Route::middleware('role:admin')->group(function () {
        $adminDashboardData = function (): array {
            $todayFilters = [
                'start_at' => now()->startOfDay(),
                'end_at' => now()->endOfDay(),
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'max_date' => now()->toDateString(),
                'shift_id' => null,
                'payment_method' => null,
            ];

            return [
                'todayFilters' => $todayFilters,
                'todayReport' => app(FinancialReportService::class)->report($todayFilters),
                'pendingPaymentOrders' => Order::where('payment_status', 'unpaid')
                    ->whereBetween('created_at', [$todayFilters['start_at'], $todayFilters['end_at']])
                    ->count(),
                'activeKitchenQueues' => KitchenQueue::whereIn('status', ['queued', 'preparing'])
                    ->whereBetween('queued_at', [$todayFilters['start_at'], $todayFilters['end_at']])
                    ->count(),
                'completedKitchenQueues' => KitchenQueue::where('status', 'done')
                    ->whereBetween('done_at', [$todayFilters['start_at'], $todayFilters['end_at']])
                    ->count(),
            ];
        };

        Route::get('/dashboard', function () use ($adminDashboardData) {
            $data = $adminDashboardData();

            return view('dashboardAdmin', [
                'totalUsers' => User::count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'totalShifts' => Shift::count(),
                'totalTables' => DiningTable::count(),
                'totalCategories' => Category::count(),
                'totalMenus' => MenuItem::count(),
                'activeMenus' => MenuItem::where('is_active', true)->count(),
                'todayReport' => $data['todayReport'],
                'pendingPaymentOrders' => $data['pendingPaymentOrders'],
                'activeKitchenQueues' => $data['activeKitchenQueues'],
                'completedKitchenQueues' => $data['completedKitchenQueues'],
            ]);
        })->name('dashboard');

        Route::get('/dashboard/realtime', function () use ($adminDashboardData) {
            $data = $adminDashboardData();

            return response()->json([
                'ops_html' => view('Admin.dashboard.partials.ops', $data)->render(),
                'payments_html' => view('Admin.dashboard.partials.payments', $data)->render(),
            ]);
        })->name('dashboard.realtime');

        Route::resource('menu', MenuController::class)
            ->parameters(['menu' => 'menuItem']);

        Route::prefix('menu')->name('menu.')->group(function () {
            Route::get('{menuItem}/quick-edit', [MenuController::class, 'editQuick'])
                ->name('quick-edit');

            Route::put('{menuItem}/quick-update', [MenuController::class, 'updateQuick'])
                ->name('quick-update');

            Route::post('import/preview', [MenuController::class, 'importPreview'])
                ->name('import.preview');

            Route::post('import/store', [MenuController::class, 'importStore'])
                ->name('import.store');

            Route::post('{menuItem}/discount', [MenuController::class, 'storeDiscount'])
                ->name('discount.store');

            Route::delete('{menuItem}/discount/{discount}', [MenuController::class, 'destroyDiscount'])
                ->name('discount.destroy');
        });

        Route::resource('categories', CategoryController::class);
        Route::resource('tables', DiningTableController::class);
        Route::resource('users', UserController::class);
        Route::resource('shifts', ShiftController::class);

        Route::get('/admin/orders', [AdminOrderController::class, 'index'])
            ->name('admin.orders.index');

        Route::get('/admin/orders/export/{type}', [AdminOrderController::class, 'export'])
            ->name('admin.orders.export');

        Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])
            ->name('admin.orders.show');

        Route::get('/admin/reports/financial', FinancialReportController::class)
            ->name('admin.reports.financial');

        Route::get('/admin/reports/financial/realtime', [FinancialReportController::class, 'realtime'])
            ->name('admin.reports.financial.realtime');

        // Route untuk setting lokasi resto
        Route::get('/restaurant-location', [RestaurantLocationController::class, 'edit'])
            ->name('admin.restaurant-location.edit');
        Route::put('/restaurant-location', [RestaurantLocationController::class, 'update'])
            ->name('admin.restaurant-location.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Owner routes
    |--------------------------------------------------------------------------
    | Khusus user login dengan role owner.
    */

    Route::middleware('role:owner')
        ->prefix('owner')
        ->name('owner.')
        ->group(function () {
            Route::get('/dashboard', OwnerDashboardController::class)
                ->name('dashboard');

            Route::get('/reports/export/excel', [OwnerReportExportController::class, 'excel'])
                ->name('reports.export.excel');

            Route::get('/reports/export/pdf', [OwnerReportExportController::class, 'pdf'])
                ->name('reports.export.pdf');
        });

    /*
    |--------------------------------------------------------------------------
    | Kasir routes
    |--------------------------------------------------------------------------
    | Khusus user login dengan role kasir.
    */

    Route::middleware(['role:kasir', 'shift.active'])
        ->prefix('kasir')
        ->name('kasir.')
        ->group(function () {
            Route::get('/dashboard', [CashierPaymentController::class, 'index'])
                ->name('dashboard');

            Route::get('/dashboard/realtime', [CashierPaymentController::class, 'realtime'])
                ->name('dashboard.realtime');

            Route::get('/orders/{order}/receipt', [CashierPaymentController::class, 'receipt'])
                ->name('orders.receipt');

            Route::post('/orders/{order}/reject-items', [CashierPaymentController::class, 'rejectItems'])
                ->name('orders.reject-items');

            Route::post('/cashier/orders/{order}/pay-cash', [CashierPaymentController::class, 'payCash'])
                ->name('orders.pay-cash');
        });

    /*
    |--------------------------------------------------------------------------
    | Dapur routes
    |--------------------------------------------------------------------------
    | Khusus user login dengan role dapur.
    */

    Route::middleware(['role:dapur', 'shift.active'])
        ->prefix('dapur')
        ->name('dapur.')
        ->group(function () {
            Route::get('/dashboard', [KitchenController::class, 'index'])
                ->name('dashboard');

            Route::get('/dashboard/realtime', [KitchenController::class, 'realtime'])
                ->name('dashboard.realtime');

            Route::get('/orders/{order}', [KitchenController::class, 'show'])
                ->name('orders.show');

            Route::post('/orders/{order}/prepare', [KitchenController::class, 'prepare'])
                ->name('orders.prepare');

            Route::post('/orders/{order}/done', [KitchenController::class, 'done'])
                ->name('orders.done');

        });
});
