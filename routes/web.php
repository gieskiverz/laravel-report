<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\CoaController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('kategori', [KategoriController::class, 'index'])->name('kategori.index');
Route::get('kategori/edit/{id}', [KategoriController::class, 'edit']);
Route::post('kategori/store', [KategoriController::class, 'store'])->name('kategori.store');
Route::post('kategori/update', [KategoriController::class, 'update'])->name('kategori.update');
Route::get('kategori/destroy/{id}/', [KategoriController::class, 'destroy']);

Route::get('coa', [CoaController::class, 'index'])->name('coa.index');
Route::get('coa/edit/{id}', [CoaController::class, 'edit']);
Route::post('coa/store', [CoaController::class, 'store'])->name('coa.store');
Route::post('coa/update', [CoaController::class, 'update'])->name('coa.update');
Route::get('coa/destroy/{id}/', [CoaController::class, 'destroy']);

Route::get('transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
Route::get('transaksi/edit/{id}', [TransaksiController::class, 'edit']);
Route::get('transaksi/modol/{id}', [TransaksiController::class, 'modol']);
Route::post('transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');
Route::post('transaksi/update', [TransaksiController::class, 'update'])->name('transaksi.update');
Route::get('transaksi/destroy/{id}/', [TransaksiController::class, 'destroy']);

Route::get('export', [ExportController::class, 'index']);
Route::get('export/report/{id}', [ExportController::class, 'report']);