<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Patient;
use App\Models\Encounter;
use App\Http\Requests\DocumentStoreRequest;
use App\Http\Requests\DocumentUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Get all documents for the current clinic
     */
    public function index()
    {
        $user = Auth::user();
        
        // Admin can see all documents, doctors/staff can see documents from their clinic
        if ($user->isAdmin()) {
            $documents = Document::with(['patient', 'encounter', 'uploadedBy'])->get();
        } else {
            $documents = Document::with(['patient', 'encounter', 'uploadedBy'])
                ->whereHas('patient', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Generate a unique document number
     */
    private function generateDocumentNumber($clinicId)
    {
        $prefix = 'DOC-' . date('Y') . '-';
        
        // Find the highest existing document number for this clinic this year
        $lastDocument = Document::whereHas('patient', function($query) use ($clinicId) {
                $query->where('clinic_id', $clinicId);
            })
            ->where('document_number', 'LIKE', $prefix . '%')
            ->orderBy('document_number', 'desc')
            ->first();
        
        if ($lastDocument) {
            $lastNumber = intval(substr($lastDocument->document_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Upload a new document
     */
    public function store(DocumentStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Check if user has permission to upload documents for this patient
        $patient = Patient::find($request->patient_id);
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to upload documents for this patient'
            ], 403);
        }
        
        try {
            // Handle file upload
            $file = $request->file('file');
            $filePath = $file->store('documents/' . $patient->id, 'public');
            
            // Create document record
            $document = Document::create([
                'patient_id' => $request->patient_id,
                'encounter_id' => $request->encounter_id,
                'uploaded_by' => $user->id,
                'document_number' => $this->generateDocumentNumber($patient->clinic_id),
                'document_type' => $request->document_type,
                'title' => $request->title,
                'description' => $request->description,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'is_confidential' => $request->is_confidential ?? false,
                'tags' => $request->tags,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => Document::with(['patient', 'encounter', 'uploadedBy'])->find($document->id)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific document
     */
    public function show($id)
    {
        $document = Document::with(['patient', 'encounter', 'uploadedBy'])->find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this document
        if (!$user->isAdmin() && $document->patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this document'
            ], 403);
        }
        
        // Check if document is confidential and user is not the uploader or admin
        if ($document->is_confidential && !$user->isAdmin() && $document->uploaded_by != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this confidential document'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Update a document
     */
    public function update(DocumentUpdateRequest $request, $id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to update this document
        if (!$user->isAdmin() && $document->patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this document'
            ], 403);
        }
        
        // Only uploader or admin can update the document
        if ($document->uploaded_by != $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the uploader or admin can update this document'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $document->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => Document::with(['patient', 'encounter', 'uploadedBy'])->find($document->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a document
     */
    public function destroy($id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has permission to delete this document
        if (!$user->isAdmin() && $document->patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this document'
            ], 403);
        }
        
        // Only uploader or admin can delete the document
        if ($document->uploaded_by != $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only the uploader or admin can delete this document'
            ], 403);
        }
        
        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            // Delete the document record
            $document->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a document file
     */
    public function download($id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this document
        if (!$user->isAdmin() && $document->patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this document'
            ], 403);
        }
        
        // Check if document is confidential and user is not the uploader or admin
        if ($document->is_confidential && !$user->isAdmin() && $document->uploaded_by != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this confidential document'
            ], 403);
        }
        
        try {
            if (Storage::disk('public')->exists($document->file_path)) {
                return response()->download(storage_path('app/public/' . $document->file_path), $document->file_name);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Document file not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents for a specific patient
     */
    public function getPatientDocuments($patientId)
    {
        $patient = Patient::find($patientId);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this patient
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this patient'
            ], 403);
        }
        
        $documents = $patient->documents()->with(['encounter', 'uploadedBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Get documents for a specific encounter
     */
    public function getEncounterDocuments($encounterId)
    {
        $encounter = Encounter::find($encounterId);
        
        if (!$encounter) {
            return response()->json([
                'success' => false,
                'message' => 'Encounter not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this encounter
        if (!$user->isAdmin() && $encounter->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this encounter'
            ], 403);
        }
        
        $documents = $encounter->documents()->with(['patient', 'uploadedBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Get documents by type
     */
    public function getDocumentsByType($type)
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            $documents = Document::with(['patient', 'encounter', 'uploadedBy'])
                ->where('document_type', $type)
                ->get();
        } else {
            $documents = Document::with(['patient', 'encounter', 'uploadedBy'])
                ->whereHas('patient', function($query) use ($user) {
                    $query->where('clinic_id', $user->clinic_id);
                })
                ->where('document_type', $type)
                ->get();
        }
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Search documents
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        
        $query = Document::query();
        
        if (!$user->isAdmin()) {
            $query->whereHas('patient', function($query) use ($user) {
                $query->where('clinic_id', $user->clinic_id);
            });
        }
        
        if ($request->patient_id) {
            $query->where('patient_id', $request->patient_id);
        }
        
        if ($request->encounter_id) {
            $query->where('encounter_id', $request->encounter_id);
        }
        
        if ($request->document_type) {
            $query->where('document_type', $request->document_type);
        }
        
        if ($request->search_text) {
            $searchText = $request->search_text;
            $query->where(function($q) use ($searchText) {
                $q->where('title', 'like', '%' . $searchText . '%')
                  ->orWhere('description', 'like', '%' . $searchText . '%')
                  ->orWhere('file_name', 'like', '%' . $searchText . '%')
                  ->orWhere('tags', 'like', '%' . $searchText . '%');
            });
        }
        
        $documents = $query->with(['patient', 'encounter', 'uploadedBy'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Get document statistics for the current clinic
     */
    public function getDocumentStatistics()
    {
        $user = Auth::user();
        
        $query = Document::query();
        
        if (!$user->isAdmin()) {
            $query->whereHas('patient', function($query) use ($user) {
                $query->where('clinic_id', $user->clinic_id);
            });
        }
        
        $totalDocuments = $query->count();
        $confidentialDocuments = $query->where('is_confidential', true)->count();
        
        $documentsByType = $query->selectRaw('document_type, COUNT(*) as count')
            ->groupBy('document_type')
            ->get()
            ->keyBy('document_type')
            ->map(function($item) {
                return $item->count;
            });
        
        $documentsByMonth = $query->whereYear('created_at', date('Y'))
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function($item) {
                return $item->count;
            });
        
        $storageUsage = $query->sum('file_size');
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_documents' => $totalDocuments,
                'confidential_documents' => $confidentialDocuments,
                'documents_by_type' => $documentsByType,
                'documents_by_month' => $documentsByMonth,
                'storage_usage_bytes' => $storageUsage,
                'storage_usage_human' => $this->formatBytes($storageUsage),
            ]
        ]);
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get document categories
     */
    public function getDocumentCategories()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'Medical Records',
                'Lab Results',
                'Imaging Reports',
                'Consultation Notes',
                'Discharge Summaries',
                'Consent Forms',
                'Insurance Documents',
                'Legal Documents',
                'Other',
            ]
        ]);
    }

    /**
     * Get recent documents for the current clinic
     */
    public function getRecentDocuments()
    {
        $user = Auth::user();
        
        $query = Document::with(['patient', 'encounter', 'uploadedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(20);
        
        if (!$user->isAdmin()) {
            $query->whereHas('patient', function($query) use ($user) {
                $query->where('clinic_id', $user->clinic_id);
            });
        }
        
        $documents = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Upload multiple documents
     */
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'encounter_id' => 'nullable|exists:encounters,id',
            'documents' => 'required|array',
            'documents.*.file' => 'required|file|max:10240', // 10MB max
            'documents.*.document_type' => 'required|string|max:100',
            'documents.*.title' => 'nullable|string|max:255',
            'documents.*.description' => 'nullable|string|max:1000',
            'documents.*.is_confidential' => 'nullable|boolean',
            'documents.*.tags' => 'nullable|string|max:255',
        ]);
        
        $user = Auth::user();
        
        // Check if user has permission to upload documents for this patient
        $patient = Patient::find($request->patient_id);
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        if (!$user->isAdmin() && $patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to upload documents for this patient'
            ], 403);
        }
        
        try {
            $uploadedDocuments = [];
            
            foreach ($request->documents as $docData) {
                $file = $docData['file'];
                $filePath = $file->store('documents/' . $patient->id, 'public');
                
                $document = Document::create([
                    'patient_id' => $request->patient_id,
                    'encounter_id' => $request->encounter_id,
                    'uploaded_by' => $user->id,
                    'document_number' => $this->generateDocumentNumber($patient->clinic_id),
                    'document_type' => $docData['document_type'],
                    'title' => $docData['title'] ?? $file->getClientOriginalName(),
                    'description' => $docData['description'] ?? null,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'is_confidential' => $docData['is_confidential'] ?? false,
                    'tags' => $docData['tags'] ?? null,
                ]);
                
                $uploadedDocuments[] = $document;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Documents uploaded successfully',
                'data' => Document::with(['patient', 'encounter', 'uploadedBy'])->findMany(
                    collect($uploadedDocuments)->pluck('id')
                )
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload documents',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document preview (for supported file types)
     */
    public function preview($id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this document
        if (!$user->isAdmin() && $document->patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this document'
            ], 403);
        }
        
        // Check if document is confidential and user is not the uploader or admin
        if ($document->is_confidential && !$user->isAdmin() && $document->uploaded_by != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this confidential document'
            ], 403);
        }
        
        try {
            if (!Storage::disk('public')->exists($document->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document file not found'
                ], 404);
            }
            
            // For now, just return the file URL for preview
            // In a real application, you might generate a preview based on file type
            $fileUrl = Storage::disk('public')->url($document->file_path);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'preview_url' => $fileUrl,
                    'file_type' => $document->file_type,
                    'can_preview' => in_array(strtolower($document->file_type), ['pdf', 'jpg', 'jpeg', 'png', 'gif']),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate document preview',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document metadata
     */
    public function getMetadata($id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Check if user has access to this document
        if (!$user->isAdmin() && $document->patient->clinic_id != $user->clinic_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this document'
            ], 403);
        }
        
        try {
            $metadata = [
                'id' => $document->id,
                'document_number' => $document->document_number,
                'title' => $document->title,
                'description' => $document->description,
                'document_type' => $document->document_type,
                'file_name' => $document->file_name,
                'file_type' => $document->file_type,
                'file_size' => $document->file_size,
                'file_size_human' => $this->formatBytes($document->file_size),
                'is_confidential' => $document->is_confidential,
                'tags' => $document->tags,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
                'patient_id' => $document->patient_id,
                'encounter_id' => $document->encounter_id,
                'uploaded_by' => $document->uploaded_by,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $metadata
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get document metadata',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}