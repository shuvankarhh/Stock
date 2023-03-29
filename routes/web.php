<?php

use App\Http\Controllers\Admin\CheckController;
use App\Http\Controllers\Admin\StepOneController;
use App\Http\Controllers\Admin\StepTwoController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\ApiAccessController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
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


Route::middleware(['auth','RestrictedUrl'])->group(function(){

    //Route::get('/{home?}', [HomeController::class, 'index'])->name('home');
    Route::get('/fileupload', [InvoiceController::class, 'index'])->name("upload");
    Route::post('/fileupload', [InvoiceController::class, 'saveFile'])->name("saveFile");
    Route::get('/reupload', [InvoiceController::class, 'reupload'])->name("reupload");

});


Route::middleware(['auth'])->group(function(){

    Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');

    Route::get('/step-one', [StepOneController::class, 'create'])->name('step-one.create');
    Route::post('/step-one', [StepOneController::class, 'store'])->name('step-one.store');

    Route::get('/step-two', [StepTwoController::class, 'create'])->name('step-two.create');
    Route::post('/step-two', [StepTwoController::class, 'store'])->name('step-two.store');

    Route::get('/check', [CheckController::class, 'index'])->name('check.index');
    Route::post('/check', [CheckController::class, 'store'])->name('check.store');

    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/user/create', [UserController::class, 'create'])->name('userCreate');
    Route::post('/users/create', [UserController::class, 'store'])->name('storeUser');
    Route::get('/user/edit/{id}', [UserController::class, 'edit'])->name('userEdit');
    Route::post('/user/edit/{id}', [UserController::class, 'update'])->name('userUpdate');
    Route::get('/user/delete/{id}', [UserController::class, 'delete'])->name('userDelete');

});

Route::get('/', function () {
    return redirect('/setup');
});

Route::get('clear_cache', function () {
    Artisan::call('optimize:clear');
    dd("clear cache");
});

Route::get('migrate', function () {
    Artisan::call('migrate');
    dd("run migrations");
});

Route::get('wipe', function () {
    Artisan::call('db:wipe');
    Artisan::call('migrate');
    dd("run migrations");
});

Route::get('seeder', function () {
    Artisan::call('db:seed', array('--class' => 'CreateAdminUserSeeder'));
    Artisan::call('db:seed', array('--class' => 'CreateDummyDataSeeder'));
    dd("run seeder");
});

Route::get('/reset', function(Request $request) {
    $request->session()->forget('client_id');
    $request->session()->forget('project_id');
    return redirect()->route('step-one.create');
})->name('reset');

Auth::routes();
