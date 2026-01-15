<?php

namespace App\Http\Controllers;

use App\Http\Requests\InteroperabilityStoreRequest;
use App\Http\Requests\InteroperabilityUpdateRequest;
use App\Models\Interoperability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class InteroperabilityController extends Controller
{
    /**
     * Display a listing of the interoperability records.
     */
    public function index(): JsonResource
    {
        return JsonResource::collection(Interoperability::with(['patient', 'referringDoctor', 'receivingDoctor', 'encounter'])->paginate());
    }

    /**
     * Store a newly created interoperability record in storage.
     */
    public function store(InteroperabilityStoreRequest $request): JsonResource
    {
        $interoperability = Interoperability::create($request->validated());
        return new JsonResource($interoperability->load(['patient', 'referringDoctor', 'receivingDoctor', 'encounter']));
    }

    /**
     * Display the specified interoperability record.
     */
    public function show(Interoperability $interoperability): JsonResource
    {
        return new JsonResource($interoperability->load(['patient', 'referringDoctor', 'receivingDoctor', 'encounter']));
    }

    /**
     * Update the specified interoperability record in storage.
     */
    public function update(InteroperabilityUpdateRequest $request, Interoperability $interoperability): JsonResource
    {
        $interoperability->update($request->validated());
        return new JsonResource($interoperability->load(['patient', 'referringDoctor', 'receivingDoctor', 'encounter']));
    }

    /**
     * Remove the specified interoperability record from storage.
     */
    public function destroy(Interoperability $interoperability): JsonResponse
    {
        $interoperability->delete();
        return response()->json(null, 204);
    }

    /**
     * Get interoperability records for a specific patient.
     */
    public function patientInteroperability(int $patientId): JsonResource
    {
        $interoperability = Interoperability::where('patient_id', $patientId)
            ->with(['patient', 'referringDoctor', 'receivingDoctor', 'encounter'])
            ->get();
        return JsonResource::collection($interoperability);
    }

    /**
     * Update the status of an interoperability record.
     */
    public function updateStatus(InteroperabilityUpdateRequest $request, Interoperability $interoperability): JsonResource
    {
        $interoperability->update(['status' => $request->input('status')]);
        return new JsonResource($interoperability->load(['patient', 'referringDoctor', 'receivingDoctor', 'encounter']));
    }

    /**
     * Get pending interoperability requests.
     */
    public function pendingRequests(): JsonResource
    {
        $pending = Interoperability::where('status', 'pending')
            ->with(['patient', 'referringDoctor', 'receivingDoctor', 'encounter'])
            ->get();
        return JsonResource::collection($pending);
    }

    /**
     * Get completed interoperability records.
     */
    public function completedRequests(): JsonResource
    {
        $completed = Interoperability::where('status', 'completed')
            ->with(['patient', 'referringDoctor', 'receivingDoctor', 'encounter'])
            ->get();
        return JsonResource::collection($completed);
    }
}