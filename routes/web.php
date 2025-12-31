<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffTaskController;
use App\Http\Controllers\StaffScheduleController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminLeaveController;
use App\Http\Controllers\StaffLeaveController;
use App\View\Components\Admin\Aside;
use App\View\Components\Staff\StaffAside;
use App\Http\Controllers\AdminMainDashboardController;
use App\Http\Controllers\SupervisorMainDashboardController;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Controllers\WhatsAppNotificationController;
use Illuminate\Support\Facades\Route;

Route::resource('tasks', TaskController::class);

Route::get('/', function () {
    return view('auth/login');
})->middleware('guest');

Route::get('/guest/complaint', [ComplaintController::class, 'guestForm'])->name('complaint.guest');
Route::post('/guest/complaint', [ComplaintController::class, 'guestSubmit'])->name('complaint.guest.submit');

Route::post('/login',[LoginController::class,'handleLogin'])->name('login')->middleware('guest');

// -------------------------------
// Admin Main Menu (No Sidebar)
// -------------------------------
Route::get('/admin/mainmenu', function () {
    $aside = new Aside();
    return view('mainmenu', ['routes' => $aside->routes]);
})->name('admin.mainmenu');

// -------------------------------
// Staff Main Menu (No Sidebar)
// -------------------------------
Route::get('/staff/mainmenu', function () {
    $aside = new StaffAside();
    return view('staffmainmenu', ['routes' => $aside->routes]);
})->name('staff.mainmenu');

Route::middleware('auth')->group(function(){
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout',[LoginController::class,'logout'])->name('logout');

    // Floors
    Route::prefix('floors')->as('floors.')->controller(FloorController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}/destroy', 'destroy')->name('destroy');
    });

    Route::prefix('users')->as('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/details', 'details')->name('details');
        Route::post('/{user}/reset-password', 'resetPassword')->name('reset-password');
        Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        });


    Route::prefix('devices')->as('devices.')->controller(DeviceController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{device}', 'update')->name('update');
        Route::delete('/{device}/destroy', 'destroy')->name('destroy');
    });

    Route::prefix('sensors')->as('sensors.')->controller(SensorController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}/destroy', 'destroy')->name('destroy');
    });

    // Floor Pictures
    Route::get('floor_pictures/{filename}', function ($filename) {
        $path = storage_path('app/private/public/floor_pictures/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    });

    // Master Data Modules
    Route::prefix('leave-data')->as('leave-data.')->group(function () {
       // Attendance
       Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance');

       // Leave requests page
       Route::get('/leave', [AdminLeaveController::class, 'index'])->name('admin.leave.index');

       // Leave quota index page (with modal for add/edit)
       Route::get('/leave/quota', [AdminLeaveController::class, 'indexQuota'])->name('admin.leave.quota.index');

       // Store leave quota (handles both create and edit)
       Route::post('/leave/quota/store', [AdminLeaveController::class, 'storeQuota'])->name('admin.leave.quota.store');

       // Update leave quota
       Route::put('/leave/quota/{quota}/update', [AdminLeaveController::class, 'updateQuota'])->name('admin.leave.quota.update');

       // Delete leave quota
       Route::delete('/leave/quota/{quota}/destroy', [AdminLeaveController::class, 'destroyQuota'])->name('admin.leave.quota.destroy');

       // Approve / Reject leave
       Route::post('/leave/{leave}/status', [AdminLeaveController::class, 'updateStatus'])->name('admin.leave.status');

       // Optional: Show leave detail
       Route::get('/leave/{leave}', [AdminLeaveController::class, 'show'])->name('admin.leave.show');
    });

    // Master Data Modules
    Route::prefix('master-data')->as('master-data.')->group(function () {
        // Kategori
        Route::prefix('kategori')->as('kategori.')->controller(KategoriController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        }); 

        // Product
        Route::prefix('product')->as('product.')->controller(ProductController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        });

        // Assets
        Route::prefix('assets')->as('assets.')->controller(AssetController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{asset}/details', function ($asset) {
                return view('asset.details', ['asset_id' => $asset]);
            })->name('details');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        });

        Route::prefix('complaints')->as('complaint.')->controller(ComplaintController::class)->group(function () {
            Route::get('/', 'index')->name('index');           // show all complaints
            Route::post('/', 'store')->name('store');          // add complaint
            Route::put('/{id}', 'update')->name('update');     // update complaint
            Route::delete('/{id}', 'destroy')->name('destroy'); // delete complaint
        });
    });

Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assignStaff'])
    ->name('complaints.assignStaff')
    ->middleware('auth');

    // Top-level Schedule Routes
    Route::prefix('schedules')->as('schedules.')->controller(ScheduleController::class)->group(function () {
        Route::get('/', 'index')->name('index');       
        Route::post('/', 'store')->name('store');      
        Route::put('/{id}', 'update')->name('update'); 
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});

// Staff dashboard routes
Route::middleware(['auth'])->group(function () {
    Route::get('/staff/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');
});

Route::middleware(['auth'])->get('/todos/staff', [TodoController::class, 'staffIndex'])
    ->name('todos.staffindex');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () { return view('profile/index'); })->name('profile.index');
    Route::get('/profile/staff', function () { return view('profile/staffindex'); })->name('profile.staffindex');
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload.photo');
    
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.editPassword');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

// Staff Task Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/staff/tasks', [StaffTaskController::class, 'index'])->name('staff.tasks');
    Route::post('/staff/tasks/{task}/accept', [StaffTaskController::class, 'accept'])->name('staff.tasks.accept');
    Route::post('/staff/tasks/{task}/reject', [StaffTaskController::class, 'reject'])->name('staff.tasks.reject');
    Route::post('/staff/tasks/{task}/progress/{status}', [StaffTaskController::class, 'updateProgress'])->name('staff.tasks.progress');
    Route::post('/staff/tasks/{task}/update-status', [StaffTaskController::class, 'updateStatus'])->name('staff.tasks.updateStatus');
    Route::post('/staff/tasks', [StaffTaskController::class, 'store'])->name('staff.tasks.store');

});

// Todo routes
Route::middleware(['auth'])->group(function () {
    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::post('/todos/{id}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->name('todos.destroy');
    Route::put('/todos/{id}', [TodoController::class, 'update'])->name('todos.update');
});

// Staff schedule
Route::middleware('auth')->group(function () {
    Route::get('/staff/schedule', [StaffScheduleController::class, 'index'])->name('staff.schedule');
});

// Attendance
Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance');

// Leave requests page
Route::get('/leave', [AdminLeaveController::class, 'index'])->name('admin.leave.index');

// Leave quota index page (with modal for add/edit)
Route::get('/leave/quota', [AdminLeaveController::class, 'indexQuota'])->name('admin.leave.quota.index');

// Store leave quota (handles both create and edit)
Route::post('/leave/quota/store', [AdminLeaveController::class, 'storeQuota'])->name('admin.leave.quota.store');

// Update leave quota
Route::put('/leave/quota/{quota}/update', [AdminLeaveController::class, 'updateQuota'])->name('admin.leave.quota.update');

// Delete leave quota
Route::delete('/leave/quota/{quota}/destroy', [AdminLeaveController::class, 'destroyQuota'])->name('admin.leave.quota.destroy');

// Approve / Reject leave
Route::post('/leave/{leave}/status', [AdminLeaveController::class, 'updateStatus'])->name('admin.leave.status');

// Optional: Show leave detail
Route::get('/leave/{leave}', [AdminLeaveController::class, 'show'])->name('admin.leave.show');

// Staff leave routes
Route::get('/staff/leaves', [StaffLeaveController::class, 'index'])->name('staff.leave.index');
Route::post('/staff/leaves', [StaffLeaveController::class, 'store'])->name('staff.leave.store');

Route::resource('complaints', ComplaintController::class);

// ----------------------
// Admin Apply Leave Routes
// ----------------------
Route::get('/admin/leave/apply', [AdminLeaveController::class, 'apply'])->name('admin.leave.apply');
Route::post('/admin/leave/apply', [AdminLeaveController::class, 'storeApply'])->name('admin.leave.apply.store');

Route::get('/admin/leave/apply', [AdminLeaveController::class, 'apply'])->name('admin.leave.apply');


Route::get('/admin/main-dashboard', [AdminMainDashboardController::class, 'index'])
    ->name('admin.main.dashboard');

// // Supervisor Main Menu
// Route::get('/supervisor/mainmenu', function() {
//     return view('supervisormainmenu');
// })->name('supervisor.mainmenu')->middleware('auth');

// // Supervisor Main Dashboard
// Route::get('/supervisor/dashboard', [SupervisorMainDashboardController::class, 'index'])
//     ->name('supervisor.dashboard')->middleware('auth');

Route::get(
    '/admin/dashboard/bin/{id}/popup',
    [AdminMainDashboardController::class, 'binPopup']
)->name('admin.dashboard.bin.popup');    

Route::get(
    '/supervisor/dashboard/bin/{id}/popup',
    [SupervisorMainDashboardController::class, 'binPopup']
)->name('supervisor.dashboard.bin.popup');

// Admin Main Dashboard
Route::get('/admin/dashboard', [AdminMainDashboardController::class, 'index'])
    ->name('admin.main.dashboard') // matches your dropdown
    ->middleware('auth');

Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])
    ->name('supervisor.dashboard');

Route::get('/supervisor/main-dashboard', [SupervisorMainDashboardController::class, 'index'])
    ->name('supervisor.main_dashboard');    

// WhatsApp Notification CRUD routes
Route::resource('whatsapp', WhatsAppNotificationController::class);

// Optional: Manual send route for a single notification
Route::post('whatsapp/{notification}/send', [WhatsAppNotificationController::class, 'sendNow'])
    ->name('whatsapp.send');