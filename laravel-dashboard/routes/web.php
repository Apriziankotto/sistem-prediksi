<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterBahanController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\DetailBahanPermintaanController;
use App\Http\Controllers\DetailBahanAktualController;
use App\Http\Controllers\HasilPrediksiBahanController;
use App\Http\Controllers\StokBahanController;
use App\Http\Controllers\ItemSpkController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\PrediksiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ManagementModelController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:Super Admin,Kepala Gudang,Pembelian,Anggota Gudang')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin')->group(function () {

        Route::resource('/master-bahan', MasterBahanController::class);

        Route::resource('spk', SpkController::class);

        Route::resource('hasil-prediksi', HasilPrediksiBahanController::class);

        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN MODEL
        |--------------------------------------------------------------------------
        | Hanya Super Admin yang boleh mengakses halaman ini.
        */
        Route::get('/management-model', [ManagementModelController::class, 'index'])
            ->name('management-model.index');
        Route::post('/management-model/retrain', [ManagementModelController::class, 'retrain'])
            ->name('management-model.retrain');

        /*
        |--------------------------------------------------------------------------
        | MANAJEMEN USER
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN + KEPALA GUDANG
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin,Kepala Gudang')->group(function () {

        Route::resource('stok-bahan', StokBahanController::class);

        Route::get('/laporan/bahan-keluar', [DetailBahanAktualController::class, 'index'])
            ->name('bahan-keluar.index');

        Route::post('/laporan/bahan-keluar', [DetailBahanAktualController::class, 'store'])
            ->name('bahan-keluar.store');

        Route::put('/laporan/bahan-keluar/{id}', [DetailBahanAktualController::class, 'update'])
            ->name('bahan-keluar.update');

        Route::delete('/laporan/bahan-keluar/{id}', [DetailBahanAktualController::class, 'destroy'])
            ->name('bahan-keluar.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN + KEPALA GUDANG + PEMBELIAN
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin,Kepala Gudang,Pembelian')->group(function () {

        Route::get('/prediksi', [PrediksiController::class, 'index'])
            ->name('prediksi.index');

        Route::post('/prediksi/process', [PrediksiController::class, 'predict'])
            ->name('prediksi.process');

        Route::get('/laporan/bahan-masuk', [BarangMasukController::class, 'index'])
            ->name('bahan-masuk.index');

        Route::post('/laporan/bahan-masuk', [BarangMasukController::class, 'store'])
            ->name('bahan-masuk.store');

        Route::put('/laporan/bahan-masuk/{id}', [BarangMasukController::class, 'update'])
            ->name('bahan-masuk.update');

        Route::delete('/laporan/bahan-masuk/{id}', [BarangMasukController::class, 'destroy'])
            ->name('bahan-masuk.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN + KEPALA GUDANG + ANGGOTA GUDANG
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin,Kepala Gudang,Anggota Gudang')->group(function () {

        Route::prefix('permintaan-bahan')->name('permintaan-bahan.')->group(function () {

            Route::get('/', [DetailBahanPermintaanController::class, 'index'])
                ->name('index');

            Route::post('/add-bahan', [DetailBahanPermintaanController::class, 'addBahan'])
                ->name('add-bahan');

            Route::post('/store', [DetailBahanPermintaanController::class, 'store'])
                ->name('store');

            Route::put('/update-jumlah/{id}', [DetailBahanPermintaanController::class, 'updateJumlah'])
                ->whereNumber('id')
                ->name('update-jumlah');

            Route::delete('/hapus-banyak', [DetailBahanPermintaanController::class, 'hapusBanyak'])
                ->name('hapus-banyak');

            Route::delete('/hapus-bahan/{spk_id}/{bahan_id}', [DetailBahanPermintaanController::class, 'hapusBahan'])
                ->name('hapus-bahan');

            Route::get('/{spk_id}', [DetailBahanPermintaanController::class, 'show'])
                ->whereNumber('spk_id')
                ->name('show');
        });

        Route::post('/item-spk', [ItemSpkController::class, 'store'])
            ->name('item-spk.store');

        Route::put('/item-spk/{id}', [ItemSpkController::class, 'update'])
            ->whereNumber('id')
            ->name('item-spk.update');

        Route::delete('/item-spk/{id}', [ItemSpkController::class, 'destroy'])
            ->whereNumber('id')
            ->name('item-spk.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | LIHAT BAHAN
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Super Admin,Kepala Gudang,Pembelian,Anggota Gudang')->group(function () {

        Route::get('/lihat-bahan', [MasterBahanController::class, 'index'])
            ->name('lihat-bahan.index');
    });

});

require __DIR__ . '/auth.php';