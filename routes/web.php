<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Patient Management
    Route::middleware('role:admin,doctor,staff')->group(function () {
        Route::get('patients', function () {
            return Inertia::render('patients/index');
        })->name('patients.index');

        Route::get('patients/create', function () {
            return Inertia::render('patients/create');
        })->name('patients.create');

        Route::get('patients/{id}', function ($id) {
            return Inertia::render('patients/show', ['id' => $id]);
        })->name('patients.show');

        Route::get('patients/{id}/edit', function ($id) {
            return Inertia::render('patients/edit', ['id' => $id]);
        })->name('patients.edit');
    });

    // Doctor Management
    Route::middleware('role:admin,staff')->group(function () {
        Route::get('doctors', function () {
            return Inertia::render('doctors/index');
        })->name('doctors.index');

        Route::get('doctors/create', function () {
            return Inertia::render('doctors/create');
        })->name('doctors.create');

        Route::get('doctors/{id}', function ($id) {
            return Inertia::render('doctors/show', ['id' => $id]);
        })->name('doctors.show');

        Route::get('doctors/{id}/edit', function ($id) {
            return Inertia::render('doctors/edit', ['id' => $id]);
        })->name('doctors.edit');
    });

    // Appointment Management
    Route::middleware('role:admin,doctor,staff,patient')->group(function () {
        Route::get('appointments', function () {
            return Inertia::render('appointments/index');
        })->name('appointments.index');

        Route::get('appointments/create', function () {
            return Inertia::render('appointments/create');
        })->name('appointments.create');

        Route::get('appointments/{id}', function ($id) {
            return Inertia::render('appointments/show', ['id' => $id]);
        })->name('appointments.show');

        Route::get('appointments/{id}/edit', function ($id) {
            return Inertia::render('appointments/edit', ['id' => $id]);
        })->name('appointments.edit');
    });

    // Billing Management
    Route::middleware('role:admin,doctor,staff')->group(function () {
        Route::get('billing', function () {
            return Inertia::render('billing/index');
        })->name('billing.index');

        Route::get('billing/create', function () {
            return Inertia::render('billing/create');
        })->name('billing.create');

        Route::get('billing/{id}', function ($id) {
            return Inertia::render('billing/show', ['id' => $id]);
        })->name('billing.show');

        Route::get('billing/{id}/edit', function ($id) {
            return Inertia::render('billing/edit', ['id' => $id]);
        })->name('billing.edit');
    });

    // Medical Records
    Route::middleware('role:admin,doctor')->group(function () {
        Route::get('medical-records', function () {
            return Inertia::render('medical-records/index');
        })->name('medical-records.index');

        Route::get('medical-records/create', function () {
            return Inertia::render('medical-records/create');
        })->name('medical-records.create');

        Route::get('medical-records/{id}', function ($id) {
            return Inertia::render('medical-records/show', ['id' => $id]);
        })->name('medical-records.show');

        Route::get('medical-records/{id}/edit', function ($id) {
            return Inertia::render('medical-records/edit', ['id' => $id]);
        })->name('medical-records.edit');
    });

    // Reports
    Route::middleware('role:admin,doctor,staff')->group(function () {
        Route::get('reports', function () {
            return Inertia::render('reports/index');
        })->name('reports.index');

        Route::get('reports/financial', function () {
            return Inertia::render('reports/financial');
        })->name('reports.financial');

        Route::get('reports/operational', function () {
            return Inertia::render('reports/operational');
        })->name('reports.operational');
    });
});

require __DIR__.'/settings.php';
