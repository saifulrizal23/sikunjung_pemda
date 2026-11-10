<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\EmployeeWorkspaceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StrukturController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/jadwal', [JadwalController::class, 'jadwal'])->name('jadwal');
Route::get('/struktur', [StrukturController::class, 'struktur'])->name('struktur');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminManagementController::class, 'index'])->name('index');
    Route::post('/pengguna', [AdminManagementController::class, 'storeUser'])->name('users.store');
    Route::patch('/pengguna/{user}', [AdminManagementController::class, 'updateUser'])->name('users.update');
    Route::delete('/pengguna/{user}', [AdminManagementController::class, 'destroyUser'])->name('users.destroy');

    Route::post('/karyawan', [AdminManagementController::class, 'storeEmployee'])->name('employees.store');
    Route::patch('/karyawan/{employee}', [AdminManagementController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/karyawan/{employee}', [AdminManagementController::class, 'destroyEmployee'])->name('employees.destroy');

    Route::post('/pemohon', [AdminManagementController::class, 'storeApplicant'])->name('applicants.store');
    Route::patch('/pemohon/{applicant}', [AdminManagementController::class, 'updateApplicant'])->name('applicants.update');
    Route::delete('/pemohon/{applicant}', [AdminManagementController::class, 'destroyApplicant'])->name('applicants.destroy');

    Route::post('/jadwal', [AdminManagementController::class, 'storeSchedule'])->name('schedules.store');
    Route::patch('/jadwal/{schedule}', [AdminManagementController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/jadwal/{schedule}', [AdminManagementController::class, 'destroySchedule'])->name('schedules.destroy');

    Route::patch('/kunjungan/{visit}', [AdminManagementController::class, 'updateVisit'])->name('visits.update');
    Route::delete('/kunjungan/{visit}', [AdminManagementController::class, 'destroyVisit'])->name('visits.destroy');

    Route::post('/kegiatan', [AdminManagementController::class, 'storeActivity'])->name('activities.store');
    Route::patch('/kegiatan/{activity}', [AdminManagementController::class, 'updateActivity'])->name('activities.update');
    Route::delete('/kegiatan/{activity}', [AdminManagementController::class, 'destroyActivity'])->name('activities.destroy');
});

Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->name('employee.')->group(function (): void {
    Route::get('/ruang-kerja', [EmployeeWorkspaceController::class, 'index'])->name('workspace');
    Route::post('/jadwal', [EmployeeWorkspaceController::class, 'storeSchedule'])->name('schedules.store');
    Route::patch('/jadwal/{schedule}', [EmployeeWorkspaceController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/jadwal/{schedule}', [EmployeeWorkspaceController::class, 'destroySchedule'])->name('schedules.destroy');
    Route::post('/kegiatan', [EmployeeWorkspaceController::class, 'storeActivity'])->name('activities.store');
    Route::patch('/kegiatan/{activity}', [EmployeeWorkspaceController::class, 'updateActivity'])->name('activities.update');
    Route::delete('/kegiatan/{activity}', [EmployeeWorkspaceController::class, 'destroyActivity'])->name('activities.destroy');
    Route::post('/kunjungan/{visit}/keputusan', [VisitController::class, 'decide'])->name('visits.decide');
});

Route::middleware(['auth', 'role:pemohon'])->prefix('pengajuan')->name('visits.')->group(function (): void {
    Route::get('/', [VisitController::class, 'index'])->name('index');
    Route::get('/buat', [VisitController::class, 'create'])->name('create');
    Route::post('/', [VisitController::class, 'store'])->name('store');
    Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy');
});
