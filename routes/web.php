<?php

use App\Http\Controllers\GenericController;
use App\Http\Controllers\Web\SAFTImportController;
use App\Http\Controllers\SAFTController;
use App\Http\Controllers\DocLinhaAccessController;
use App\Http\Controllers\DocAccessController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('index');
})->name('index');

Route::post('upload', [GenericController::class, 'uploadSaft'])->name('upload');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/saft/import', [\App\Http\Controllers\Web\SAFTImportController::class, 'index'])->name('saft.import');
Route::post('/saft/import', [\App\Http\Controllers\Web\SAFTImportController::class, 'store'])->name('saft.import.post');

// Rotas para o DocLinhaAccessController
Route::get('/doc-linha-access', [DocLinhaAccessController::class, 'index'])->name('doc-linha-access.index');
Route::post('/doc-linha-access/search', [DocLinhaAccessController::class, 'search'])->name('doc-linha-access.search');
Route::get('/doc-linha-access/{invoiceId}', [DocLinhaAccessController::class, 'show'])->name('doc-linha-access.show');

// Rotas para o DocAccessController
Route::get('/doc-access', [DocAccessController::class, 'index'])->name('doc-access.index');
Route::get('/doc-access/results', [DocAccessController::class, 'results'])->name('doc-access.results');
Route::post('/doc-access/batch-sql', [DocAccessController::class, 'generateBatchSql'])->name('doc-access.batch-sql');
Route::get('/doc-access/document/{invoiceId}', [DocAccessController::class, 'showDocument'])->name('doc-access.show-document');
