<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffTaskController;
use Illuminate\Support\Facades\Route;

Route::resource('tasks', TaskController::class);

Route::get('/', function () {
    return view('auth/login');
})->middleware('guest');

Route::post('/login',[LoginController::class,'handleLogin'])->name('login')->middleware('guest');

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
            Route::delete('/{id}/destroy', 'destroy')->name('destroy');

    });

Route::prefix('devices')->as('devices.')->controller(DeviceController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::put('/{id}', 'update')->name('update');      // <--- Add this
    Route::delete('/{id}/destroy', 'destroy')->name('destroy');
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
        })->name('details');  // route name: master-data.assets.details
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}/destroy', 'destroy')->name('destroy');
        });
    });

    // Top-level Schedule Routes
    Route::prefix('schedules')->as('schedules.')->controller(ScheduleController::class)->group(function () {
        Route::get('/', 'index')->name('index');       // List schedules
        Route::post('/', 'store')->name('store');      // Add schedule
        Route::put('/{id}', 'update')->name('update'); // Update schedule
        Route::delete('/{id}', 'destroy')->name('destroy'); // Delete schedule
    });

});
    //staff dashboard routes
    Route::middleware(['auth'])->group(function () {
    Route::get('/staff/dashboard', [StaffController::class, 'dashboard'])
         ->name('staff.dashboard');
});

    // Staff Task Routes
    Route::middleware(['auth'])->group(function () {
    // View assigned tasks
    Route::get('/staff/tasks', [StaffTaskController::class, 'index'])->name('staff.tasks');

    // Accept task
    Route::post('/staff/tasks/{task}/accept', [StaffTaskController::class, 'accept'])->name('staff.tasks.accept');

    // Reject task
    Route::post('/staff/tasks/{task}/reject', [StaffTaskController::class, 'reject'])->name('staff.tasks.reject');

    // Update progress: in_progress or done
    Route::post('/staff/tasks/{task}/progress/{status}', [StaffTaskController::class, 'updateProgress'])->name('staff.tasks.progress');

    // NEW: Update status via dropdown
    Route::post('/staff/tasks/{task}/update-status', [StaffTaskController::class, 'updateStatus'])->name('staff.tasks.updateStatus');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/todos', [TodoController::class, 'index'])->name('todos.index');
    Route::post('/todos', [TodoController::class, 'store'])->name('todos.store');
    Route::post('/todos/{id}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->name('todos.destroy');
    Route::put('/todos/{id}', [TodoController::class, 'update'])->name('todos.update');
});