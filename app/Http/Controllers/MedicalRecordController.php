<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Http\Requests\MedicalRecordStoreRequest;
use App\Http\Requests\MedicalRecordUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MedicalRecordController extends Controller
{
    /**
     * Get all medical records for the current clinic (admin/doctor access only)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Only admin and doctors can access medical records
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        // Admin can see all records, doctors can see records from their clinic
        if ($user->isAdmin()) {
            $medicalRecords = MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $medicalRecords = MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])
                ->where('clinic_id', $user->clinic_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $medicalRecords
        ]);
    }

    /**
     * Create a new medical record
     */
    public function store(MedicalRecordStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to create medical records
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors and admins can create medical records'
            ], 403);
        }
        
        // Check if the patient, doctor, and clinic are accessible to the user
        $patient = Patient::find($request->patient_id);
        $doctor = Doctor::find($request->doctor_id);
        $clinic = Clinic::find($request->clinic_id);
        
        if (!$patient || !$doctor || !$clinic) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid patient, doctor, or clinic'
            ], 404);
        }
        
        // Check clinic access
        if (!$user->isAdmin() && $clinic->id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create records for this clinic'
            ], 403);
        }
        
        try {
            // Handle file attachments
            $attachmentPaths = [];
            
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('medical_records/attachments', $filename, 'public');
                    $attachmentPaths[] = $path;
                }
            }
            
            // Create the medical record
            $medicalRecord = MedicalRecord::create([
                'clinic_id' => $request->clinic_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'appointment_id' => $request->appointment_id,
                'record_type' => $request->record_type,
                'content' => $request->content,
                'severity' => $request->severity ?? 'medium',
                'is_confidential' => $request->is_confidential ?? false,
                'attachments' => $attachmentPaths,
            ]);
            
            // Update patient's last visit date if this is a consultation record
            if ($request->record_type === 'consultation' && $request->appointment_id) {
                $appointment = Appointment::find($request->appointment_id);
                if ($appointment) {
                    $patient->update(['last_visit_date' => now()]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Medical record created successfully',
                'data' => MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])->find($medicalRecord->id)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create medical record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific medical record
     */
    public function show($id)
    {
        $medicalRecord = MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])->find($id);
        
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Medical record not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this medical record
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        if (!$user->isAdmin() && $medicalRecord->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this medical record'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $medicalRecord
        ]);
    }

    /**
     * Update a medical record
     */
    public function update(MedicalRecordUpdateRequest $request, $id)
    {
        $medicalRecord = MedicalRecord::find($id);
        
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Medical record not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this medical record
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors and admins can update medical records'
            ], 403);
        }
        
        if (!$user->isAdmin() && $medicalRecord->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this medical record'
            ], 403);
        }
        
        try {
            // Handle new file attachments
            $existingAttachments = $medicalRecord->attachments ?? [];
            
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('medical_records/attachments', $filename, 'public');
                    $existingAttachments[] = $path;
                }
            }
            
            $validatedData = $request->validated();
            
            // Update the medical record
            $medicalRecord->update([
                'record_type' => $validatedData['record_type'] ?? $medicalRecord->record_type,
                'content' => $validatedData['content'] ?? $medicalRecord->content,
                'severity' => $validatedData['severity'] ?? $medicalRecord->severity,
                'is_confidential' => $validatedData['is_confidential'] ?? $medicalRecord->is_confidential,
                'attachments' => $existingAttachments,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Medical record updated successfully',
                'data' => MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])->find($medicalRecord->id)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update medical record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a medical record (soft delete)
     */
    public function destroy($id)
    {
        $medicalRecord = MedicalRecord::find($id);
        
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Medical record not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to delete this medical record
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete medical records'
            ], 403);
        }
        
        if ($medicalRecord->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this medical record'
            ], 403);
        }
        
        try {
            // Soft delete the medical record
            $medicalRecord->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Medical record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete medical record',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get medical records for a specific patient
     */
    public function getPatientRecords($patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this patient's medical records
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        $medicalRecords = MedicalRecord::with(['doctor.user', 'clinic', 'appointment'])
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $medicalRecords
        ]);
    }

    /**
     * Get medical records by type
     */
    public function getRecordsByType($type)
    {
        $user = Auth::user();
        
        // Validate record type
        $validTypes = ['diagnosis', 'prescription', 'lab_result', 'imaging', 'notes', 'consultation'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid record type'
            ], 400);
        }
        
        // Check if user has access to medical records
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        // Admin can see all records, doctors can see records from their clinic
        if ($user->isAdmin()) {
            $medicalRecords = MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])
                ->where('record_type', $type)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $medicalRecords = MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])
                ->where('record_type', $type)
                ->where('clinic_id', $user->clinic_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $medicalRecords
        ]);
    }

    /**
     * Get medical records for a specific appointment
     */
    public function getAppointmentRecords($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this appointment's medical records
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this appointment'
            ], 403);
        }
        
        $medicalRecords = MedicalRecord::with(['patient.user', 'doctor.user', 'clinic'])
            ->where('appointment_id', $appointmentId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $medicalRecords
        ]);
    }

    /**
     * Create a consultation note for an appointment
     */
    public function createConsultationNote(Request $request, $appointmentId)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'clinic'])->find($appointmentId);
        
        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to create consultation notes
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors and admins can create consultation notes'
            ], 403);
        }
        
        if (!$user->isAdmin() && $appointment->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create notes for this appointment'
            ], 403);
        }
        
        $request->validate([
            'content' => 'required|string',
            'severity' => 'nullable|string|in:low,medium,high,critical',
            'is_confidential' => 'nullable|boolean',
        ]);
        
        try {
            // Create consultation note
            $medicalRecord = MedicalRecord::create([
                'clinic_id' => $appointment->clinic_id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_id' => $appointment->id,
                'record_type' => 'consultation',
                'content' => $request->content,
                'severity' => $request->severity ?? 'medium',
                'is_confidential' => $request->is_confidential ?? false,
            ]);
            
            // Update patient's last visit date
            $appointment->patient->update(['last_visit_date' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => 'Consultation note created successfully',
                'data' => MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])->find($medicalRecord->id)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create consultation note',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a prescription for a patient
     */
    public function createPrescription(Request $request, $patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to create prescriptions
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Only doctors and admins can create prescriptions'
            ], 403);
        }
        
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create prescriptions for this patient'
            ], 403);
        }
        
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'medications' => 'required|array',
            'medications.*.name' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.duration' => 'required|string',
            'instructions' => 'nullable|string',
            'severity' => 'nullable|string|in:low,medium,high,critical',
            'is_confidential' => 'nullable|boolean',
        ]);
        
        try {
            // Get doctor for prescription content
            $doctor = Doctor::find($request->doctor_id);
            
            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }
            
            // Format prescription content
            $medicationList = '';
            foreach ($request->medications as $medication) {
                $medicationList .= "• {$medication['name']} - {$medication['dosage']} ({$medication['frequency']}) for {$medication['duration']}\n";
            }
            
            $content = "PRESCRIPTION:\n\n" . $medicationList . "\n\n";
            
            if ($request->instructions) {
                $content .= "INSTRUCTIONS:\n{$request->instructions}\n\n";
            }
            
            $content .= "Prescribed by: Dr. " . $doctor->user->name . "\n";
            $content .= "Date: " . now()->format('Y-m-d H:i:s');
            
            // Create prescription record
            $medicalRecord = MedicalRecord::create([
                'clinic_id' => $patient->clinic_id,
                'patient_id' => $patient->id,
                'doctor_id' => $request->doctor_id,
                'appointment_id' => $request->appointment_id,
                'record_type' => 'prescription',
                'content' => $content,
                'severity' => $request->severity ?? 'medium',
                'is_confidential' => $request->is_confidential ?? false,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Prescription created successfully',
                'data' => MedicalRecord::with(['patient.user', 'doctor.user', 'clinic', 'appointment'])->find($medicalRecord->id)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create prescription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a medical record attachment
     */
    public function downloadAttachment($recordId, $filename)
    {
        $medicalRecord = MedicalRecord::find($recordId);
        
        if (!$medicalRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Medical record not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this medical record
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        if (!$user->isAdmin() && $medicalRecord->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this medical record'
            ], 403);
        }
        
        // Check if the attachment exists in the record
        if (!in_array($filename, $medicalRecord->attachments ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment not found'
            ], 404);
        }
        
        // Check if file exists in storage
        if (!Storage::disk('public')->exists($filename)) {
            return response()->json([
                'success' => false,
                'message' => 'Attachment file not found'
            ], 404);
        }
        
        return Storage::disk('public')->download($filename);
    }

    /**
     * Get medical record statistics
     */
    public function getStatistics()
    {
        $user = Auth::user();
        
        // Check if user has access to medical records
        if (!$user->isAdmin() && !$user->isDoctor()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access medical records'
            ], 403);
        }
        
        // Admin can see all records, doctors can see records from their clinic
        $query = MedicalRecord::query();
        
        if (!$user->isAdmin()) {
            $query->where('clinic_id', $user->clinic_id);
        }
        
        $statistics = [
            'total_records' => $query->count(),
            'by_type' => $query->selectRaw('record_type, count(*) as count')
                ->groupBy('record_type')
                ->pluck('count', 'record_type'),
            'by_severity' => $query->selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity'),
            'confidential_records' => $query->where('is_confidential', true)->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }
}