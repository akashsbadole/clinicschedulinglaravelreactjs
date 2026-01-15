<?php

namespace App\Http\Controllers;

use App\Models\Icd10Code;
use App\Http\Requests\Icd10CodeStoreRequest;
use App\Http\Requests\Icd10CodeUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Icd10CodeController extends Controller
{
    /**
     * Get all ICD-10 codes
     */
    public function index()
    {
        $icd10Codes = Icd10Code::all();
        
        return response()->json([
            'success' => true,
            'data' => $icd10Codes
        ]);
    }

    /**
     * Create a new ICD-10 code
     */
    public function store(Icd10CodeStoreRequest $request)
    {
        $validatedData = $request->validated();
        
        $user = Auth::user();
        
        // Only admins can create ICD-10 codes
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can create ICD-10 codes'
            ], 403);
        }
        
        try {
            // Create ICD-10 code
            $icd10Code = Icd10Code::create([
                'code' => $request->code,
                'description' => $request->description,
                'category' => $request->category,
                'chapter' => $request->chapter,
                'is_active' => $request->is_active ?? true,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'ICD-10 code created successfully',
                'data' => $icd10Code
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ICD-10 code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific ICD-10 code
     */
    public function show($id)
    {
        $icd10Code = Icd10Code::find($id);
        
        if (!$icd10Code) {
            return response()->json([
                'success' => false,
                'message' => 'ICD-10 code not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $icd10Code
        ]);
    }

    /**
     * Update an ICD-10 code
     */
    public function update(Icd10CodeUpdateRequest $request, $id)
    {
        $icd10Code = Icd10Code::find($id);
        
        if (!$icd10Code) {
            return response()->json([
                'success' => false,
                'message' => 'ICD-10 code not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Only admins can update ICD-10 codes
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can update ICD-10 codes'
            ], 403);
        }
        
        $validatedData = $request->validated();
        
        try {
            $icd10Code->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'ICD-10 code updated successfully',
                'data' => $icd10Code
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ICD-10 code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an ICD-10 code
     */
    public function destroy($id)
    {
        $icd10Code = Icd10Code::find($id);
        
        if (!$icd10Code) {
            return response()->json([
                'success' => false,
                'message' => 'ICD-10 code not found'
            ], 404);
        }
        
        $user = Auth::user();
        
        // Only admins can delete ICD-10 codes
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can delete ICD-10 codes'
            ], 403);
        }
        
        try {
            $icd10Code->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'ICD-10 code deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ICD-10 code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search ICD-10 codes
     */
    public function search(Request $request)
    {
        $query = Icd10Code::query();
        
        if ($request->code) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        
        if ($request->description) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        
        if ($request->category) {
            $query->where('category', 'like', '%' . $request->category . '%');
        }
        
        if ($request->chapter) {
            $query->where('chapter', 'like', '%' . $request->chapter . '%');
        }
        
        if ($request->is_active) {
            $query->where('is_active', $request->is_active);
        }
        
        $icd10Codes = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $icd10Codes
        ]);
    }

    /**
     * Get ICD-10 codes by category
     */
    public function getByCategory($category)
    {
        $icd10Codes = Icd10Code::where('category', $category)->get();
        
        return response()->json([
            'success' => true,
            'data' => $icd10Codes
        ]);
    }

    /**
     * Get ICD-10 codes by chapter
     */
    public function getByChapter($chapter)
    {
        $icd10Codes = Icd10Code::where('chapter', $chapter)->get();
        
        return response()->json([
            'success' => true,
            'data' => $icd10Codes
        ]);
    }

    /**
     * Get active ICD-10 codes
     */
    public function getActiveCodes()
    {
        $icd10Codes = Icd10Code::where('is_active', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $icd10Codes
        ]);
    }

    /**
     * Import ICD-10 codes from CSV
     */
    public function importFromCsv(Request $request)
    {
        $user = Auth::user();
        
        // Only admins can import ICD-10 codes
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can import ICD-10 codes'
            ], 403);
        }
        
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);
        
        try {
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();
            
            $data = array_map('str_getcsv', file($filePath));
            
            $importedCount = 0;
            $skippedCount = 0;
            
            foreach ($data as $row) {
                if (count($row) >= 2) {
                    $code = trim($row[0]);
                    $description = trim($row[1]);
                    $category = isset($row[2]) ? trim($row[2]) : null;
                    $chapter = isset($row[3]) ? trim($row[3]) : null;
                    
                    // Check if code already exists
                    $existingCode = Icd10Code::where('code', $code)->first();
                    
                    if (!$existingCode) {
                        Icd10Code::create([
                            'code' => $code,
                            'description' => $description,
                            'category' => $category,
                            'chapter' => $chapter,
                            'is_active' => true,
                        ]);
                        $importedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'ICD-10 codes imported successfully',
                'imported' => $importedCount,
                'skipped' => $skippedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import ICD-10 codes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}