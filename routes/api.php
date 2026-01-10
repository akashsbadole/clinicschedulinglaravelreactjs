<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

// Role-based protected routes examples
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin-only routes will go here
    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Admin dashboard']);
    });
});

Route::middleware(['auth:sanctum', 'role:doctor'])->group(function () {
    // Doctor-only routes will go here
    Route::get('/doctor/dashboard', function () {
        return response()->json(['message' => 'Doctor dashboard']);
    });
});

Route::middleware(['auth:sanctum', 'role:staff'])->group(function () {
    // Staff-only routes will go here
    Route::get('/staff/dashboard', function () {
        return response()->json(['message' => 'Staff dashboard']);
    });
});

Route::middleware(['auth:sanctum', 'role:patient'])->group(function () {
    // Patient-only routes will go here
    Route::get('/patient/dashboard', function () {
        return response()->json(['message' => 'Patient dashboard']);
    });
});

// Multi-role access example
Route::middleware(['auth:sanctum', 'role:admin,doctor'])->group(function () {
    // Routes accessible to both admin and doctor
    Route::get('/medical/dashboard', function () {
        return response()->json(['message' => 'Medical staff dashboard']);
    });
});

// Patient Management Routes
Route::middleware(['auth:sanctum', 'role:admin,doctor,staff'])->group(function () {
    Route::get('/patients', [\App\Http\Controllers\PatientController::class, 'index']);
    Route::post('/patients', [\App\Http\Controllers\PatientController::class, 'store']);
    Route::get('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'show']);
    Route::put('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'update']);
    Route::get('/patients/{id}/appointments', [\App\Http\Controllers\PatientController::class, 'appointments']);
    Route::get('/patients/{id}/bills', [\App\Http\Controllers\PatientController::class, 'bills']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor'])->group(function () {
    Route::get('/patients/{id}/medical-records', [\App\Http\Controllers\PatientController::class, 'medicalRecords']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::delete('/patients/{id}', [\App\Http\Controllers\PatientController::class, 'destroy']);
});

// Doctor Management Routes
Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {
    Route::get('/doctors', [\App\Http\Controllers\DoctorController::class, 'index']);
    Route::get('/doctors/{id}', [\App\Http\Controllers\DoctorController::class, 'show']);
    Route::get('/doctors/{id}/appointments', [\App\Http\Controllers\DoctorController::class, 'appointments']);
    Route::get('/doctors/{id}/schedule', [\App\Http\Controllers\DoctorController::class, 'schedule']);
    Route::get('/doctors/{id}/bills', [\App\Http\Controllers\DoctorController::class, 'bills']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/doctors', [\App\Http\Controllers\DoctorController::class, 'store']);
    Route::delete('/doctors/{id}', [\App\Http\Controllers\DoctorController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor'])->group(function () {
    Route::put('/doctors/{id}', [\App\Http\Controllers\DoctorController::class, 'update']);
    Route::post('/doctors/{id}/schedule', [\App\Http\Controllers\DoctorController::class, 'updateSchedule']);
});

// Appointment Management Routes
Route::middleware(['auth:sanctum', 'role:admin,doctor,staff,patient'])->group(function () {
    Route::get('/appointments', [\App\Http\Controllers\AppointmentController::class, 'index']);
    Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'show']);
    Route::get('/appointments/doctor/{doctorId}/slots', [\App\Http\Controllers\AppointmentController::class, 'getAvailableSlots']);
    Route::get('/patients/{patientId}/appointments', [\App\Http\Controllers\AppointmentController::class, 'getPatientAppointments']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor,staff'])->group(function () {
    Route::put('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'update']);
    Route::post('/appointments/{id}/complete', [\App\Http\Controllers\AppointmentController::class, 'complete']);
    Route::post('/appointments/{id}/no-show', [\App\Http\Controllers\AppointmentController::class, 'markNoShow']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor,staff,patient'])->group(function () {
    Route::delete('/appointments/{id}', [\App\Http\Controllers\AppointmentController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor,staff'])->group(function () {
    Route::get('/doctors/{doctorId}/appointments', [\App\Http\Controllers\AppointmentController::class, 'getDoctorAppointments']);
});

// Billing Management Routes
Route::middleware(['auth:sanctum', 'role:admin,doctor,staff'])->group(function () {
    Route::get('/bills', [\App\Http\Controllers\BillController::class, 'index']);
    Route::post('/bills', [\App\Http\Controllers\BillController::class, 'store']);
    Route::get('/bills/{id}', [\App\Http\Controllers\BillController::class, 'show']);
    Route::put('/bills/{id}', [\App\Http\Controllers\BillController::class, 'update']);
    Route::post('/bills/{id}/pay', [\App\Http\Controllers\BillController::class, 'processPayment']);
    Route::post('/bills/{id}/cancel', [\App\Http\Controllers\BillController::class, 'cancel']);
    Route::get('/bills/{id}/pdf', [\App\Http\Controllers\BillController::class, 'generatePdf']);
    Route::post('/bills/from-appointment/{appointmentId}', [\App\Http\Controllers\BillController::class, 'createFromAppointment']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor,staff'])->group(function () {
    Route::get('/bills/statistics', [\App\Http\Controllers\BillController::class, 'getBillingStatistics']);
    Route::get('/bills/service-catalog', [\App\Http\Controllers\BillController::class, 'getServiceCatalog']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::delete('/bills/{id}', [\App\Http\Controllers\BillController::class, 'destroy']);
});

// Medical Record Management Routes
Route::middleware(['auth:sanctum', 'role:admin,doctor'])->group(function () {
    Route::get('/medical-records', [\App\Http\Controllers\MedicalRecordController::class, 'index']);
    Route::post('/medical-records', [\App\Http\Controllers\MedicalRecordController::class, 'store']);
    Route::get('/medical-records/statistics', [\App\Http\Controllers\MedicalRecordController::class, 'getStatistics']);
    Route::get('/medical-records/{id}', [\App\Http\Controllers\MedicalRecordController::class, 'show']);
    Route::put('/medical-records/{id}', [\App\Http\Controllers\MedicalRecordController::class, 'update']);
    Route::get('/medical-records/patient/{patientId}', [\App\Http\Controllers\MedicalRecordController::class, 'getPatientRecords']);
    Route::get('/medical-records/type/{type}', [\App\Http\Controllers\MedicalRecordController::class, 'getRecordsByType']);
    Route::get('/medical-records/appointment/{appointmentId}', [\App\Http\Controllers\MedicalRecordController::class, 'getAppointmentRecords']);
    Route::post('/medical-records/appointment/{appointmentId}/consultation', [\App\Http\Controllers\MedicalRecordController::class, 'createConsultationNote']);
    Route::post('/medical-records/patient/{patientId}/prescription', [\App\Http\Controllers\MedicalRecordController::class, 'createPrescription']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor'])->group(function () {
    Route::get('/medical-records/{recordId}/attachment/{filename}', [\App\Http\Controllers\MedicalRecordController::class, 'downloadAttachment']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::delete('/medical-records/{id}', [\App\Http\Controllers\MedicalRecordController::class, 'destroy']);
});

// Reports Routes
Route::middleware(['auth:sanctum', 'role:admin,doctor,staff'])->group(function () {
    Route::get('/reports/dashboard', [\App\Http\Controllers\ReportController::class, 'dashboard']);
    Route::get('/reports/financial', [\App\Http\Controllers\ReportController::class, 'financial']);
    Route::get('/reports/operational', [\App\Http\Controllers\ReportController::class, 'operational']);
    Route::get('/reports/export/financial', [\App\Http\Controllers\ReportController::class, 'exportFinancial']);
    Route::get('/reports/export/operational', [\App\Http\Controllers\ReportController::class, 'exportOperational']);
});

Route::middleware(['auth:sanctum', 'role:admin,doctor,staff,patient'])->group(function () {
    Route::get('/patients/{patientId}/bills', [\App\Http\Controllers\BillController::class, 'getPatientBills']);
    Route::get('/doctors/{doctorId}/bills', [\App\Http\Controllers\BillController::class, 'getDoctorBills']);
    Route::get('/appointments/{appointmentId}/bills', [\App\Http\Controllers\BillController::class, 'getAppointmentBills']);
});

// Notification Management Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/stats', [\App\Http\Controllers\NotificationController::class, 'getStats']);
    Route::put('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {
    Route::post('/notifications/send-appointment-reminders', [\App\Http\Controllers\NotificationController::class, 'sendAppointmentReminders']);
    Route::post('/notifications/send-payment-reminders', [\App\Http\Controllers\NotificationController::class, 'sendPaymentReminders']);
    Route::post('/notifications/send-overdue-notifications', [\App\Http\Controllers\NotificationController::class, 'sendOverdueNotifications']);
});