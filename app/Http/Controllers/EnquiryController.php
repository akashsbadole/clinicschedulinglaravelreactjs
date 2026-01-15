<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnquiryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string',
        ]);

        Enquiry::create($request->only(['name', 'email', 'phone', 'message']));

        return response()->json(['message' => 'Enquiry submitted successfully']);
    }

    public function index()
    {
        return Enquiry::with(['followUps.user'])->latest()->get();
    }

    public function show(Enquiry $enquiry)
    {
        return $enquiry->load(['followUps.user']);
    }

    public function updateStatus(Request $request, Enquiry $enquiry)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Enquiry::getStatuses())),
        ]);

        $oldStatus = $enquiry->status;
        $enquiry->update(['status' => $request->status]);

        // Create a follow-up entry for status change
        if ($oldStatus !== $request->status) {
            FollowUp::create([
                'followable_id' => $enquiry->id,
                'followable_type' => Enquiry::class,
                'user_id' => Auth::id(),
                'type' => FollowUp::TYPE_STATUS_CHANGE,
                'content' => "Status changed from " . Enquiry::getStatuses()[$oldStatus] . " to " . Enquiry::getStatuses()[$request->status],
            ]);
        }

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function addFollowUp(Request $request, Enquiry $enquiry)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(FollowUp::getTypes())),
            'content' => 'required|string',
        ]);

        $followUp = FollowUp::create([
            'followable_id' => $enquiry->id,
            'followable_type' => Enquiry::class,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Follow-up added successfully',
            'follow_up' => $followUp->load('user')
        ]);
    }
}
