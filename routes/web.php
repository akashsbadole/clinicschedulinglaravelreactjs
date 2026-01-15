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
        // Fetch dashboard data server-side and pass as props
        $user = auth()->user();
        $clinicId = $user->clinic_id;

        $stats = [
            'total_patients' => \App\Models\Patient::where('clinic_id', $clinicId)->count(),
            'total_doctors' => \App\Models\Doctor::where('clinic_id', $clinicId)->count(),
            'total_appointments_this_month' => \App\Models\Appointment::where('clinic_id', $clinicId)
                ->whereMonth('appointment_date', now()->month)
                ->whereYear('appointment_date', now()->year)
                ->count(),
            'total_revenue_this_month' => \App\Models\Bill::where('clinic_id', $clinicId)
                ->where('status', 'paid')
                ->whereMonth('bill_date', now()->month)
                ->whereYear('bill_date', now()->year)
                ->sum('total_amount'),
            'pending_bills' => \App\Models\Bill::where('clinic_id', $clinicId)
                ->where('status', 'pending')
                ->count(),
            'today_appointments' => \App\Models\Appointment::where('clinic_id', $clinicId)
                ->whereDate('appointment_date', today())
                ->count(),
        ];

        // Fetch today's appointments
        $today = today();
        $recentAppointments = \App\Models\Appointment::with(['patient.user', 'doctor.user'])
            ->where('clinic_id', $clinicId)
            ->whereDate('appointment_date', $today)
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        // Fetch recent notifications
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'type' => $notification->data['type'] ?? 'info',
                    'created_at' => $notification->created_at,
                ];
            });

        return Inertia::render('dashboard', [
            'stats' => $stats,
            'recentAppointments' => $recentAppointments,
            'notifications' => $notifications,
        ]);
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

        Route::get('reports/patients', function () {
            return Inertia::render('reports/patients');
        })->name('reports.patients');

        Route::get('reports/appointments', function () {
            return Inertia::render('reports/appointments');
        })->name('reports.appointments');

        Route::get('reports/billing', function () {
            return Inertia::render('reports/billing');
        })->name('reports.billing');

        Route::get('reports/medical', function () {
            return Inertia::render('reports/medical');
        })->name('reports.medical');
    });

    // Admin Dashboard for enquiries and contacts
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/dashboard', function () {
            return Inertia::render('admin/dashboard', [
                'enquiries' => \App\Models\Enquiry::with(['followUps.user'])->latest()->get(),
                'contacts' => \App\Models\Contact::with(['followUps.user'])->latest()->get(),
            ]);
        })->name('admin.dashboard');

        // Enquiry management
        Route::get('admin/enquiries', [App\Http\Controllers\EnquiryController::class, 'index'])->name('admin.enquiries.index');
        Route::get('admin/enquiries/{enquiry}', [App\Http\Controllers\EnquiryController::class, 'show'])->name('admin.enquiries.show');
        Route::patch('admin/enquiries/{enquiry}/status', [App\Http\Controllers\EnquiryController::class, 'updateStatus'])->name('admin.enquiries.update-status');
        Route::post('admin/enquiries/{enquiry}/follow-ups', [App\Http\Controllers\EnquiryController::class, 'addFollowUp'])->name('admin.enquiries.add-follow-up');

        // Contact management
        Route::get('admin/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('admin.contacts.index');
        Route::get('admin/contacts/{contact}', [App\Http\Controllers\ContactController::class, 'show'])->name('admin.contacts.show');
        Route::patch('admin/contacts/{contact}/status', [App\Http\Controllers\ContactController::class, 'updateStatus'])->name('admin.contacts.update-status');
        Route::post('admin/contacts/{contact}/follow-ups', [App\Http\Controllers\ContactController::class, 'addFollowUp'])->name('admin.contacts.add-follow-up');
    });
});

// Public routes for enquiries and contacts
Route::post('enquiries', [App\Http\Controllers\EnquiryController::class, 'store']);
Route::post('contacts', [App\Http\Controllers\ContactController::class, 'store']);

require __DIR__.'/settings.php';
