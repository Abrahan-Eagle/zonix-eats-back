<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

// Frontend Controllers
use App\Http\Controllers\Web\Front\IndexController;

// Dashboard Controllers
use App\Http\Controllers\Web\Dashboard\HomeController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\RolePermission\RoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rutas de autenticación (Laravel UI)
Auth::routes();

// Ruta dinámica para robots.txt
Route::get('/robots.txt', function () {
    $isTestEnvironment = str_contains(request()->getHost(), 'test.zonixeats.com');
    
    if ($isTestEnvironment) {
        return response("User-agent: *\nDisallow: /", 200)
            ->header('Content-Type', 'text/plain');
    }
    
    $file = public_path('robots.txt');
    if (file_exists($file)) {
        return response(file_get_contents($file), 200)
            ->header('Content-Type', 'text/plain');
    }
    
    return response("User-agent: *\nAllow: /", 200)
        ->header('Content-Type', 'text/plain');
})->name('robots.txt');

// Ruta para assetlinks.json (Android App Links)
Route::get('/.well-known/assetlinks.json', function () {
    $file = public_path('.well-known/assetlinks.json');
    
    if (!file_exists($file)) {
        return response('File not found', 404);
    }
    
    return response(file_get_contents($file), 200)
        ->header('Content-Type', 'application/json');
})->name('assetlinks');

// Ruta para limpiar caché (solo desarrollo)
Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache is cleared";
})->name('clear.cache');

// ============================================
// RUTAS PÚBLICAS DEL FRONTEND
// ============================================

Route::get('/', [IndexController::class, 'index'])->name('front.home');

// Páginas legales (pendientes de reimplementación)
// Route::get('/politica-privacidad', ...)->name('pages.privacy');
// Route::get('/terminos-condiciones', ...)->name('pages.terms');
// Route::get('/eliminar-cuenta', ...)->name('pages.delete-account');

// ============================================
// RUTAS PROTEGIDAS (requieren autenticación web)
// ============================================

Route::middleware('auth')->group(function () {
    
    // Dashboard principal
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/light', [HomeController::class, 'update'])->name('update.light');

    // Users - Dashboard (Role & Permission)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/show/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
        Route::post('/update/{user}', [UserController::class, 'update'])->name('update');
        Route::post('/delete/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Roles - Dashboard (Role & Permission)
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/store', [RoleController::class, 'store'])->name('store');
        Route::get('/show/{role}', [RoleController::class, 'show'])->name('show');
        Route::get('/edit/{role}', [RoleController::class, 'edit'])->name('edit');
        Route::post('/update/{role}', [RoleController::class, 'update'])->name('update');
        Route::post('/delete/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
});
