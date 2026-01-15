<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        Contact::create($request->only(['name', 'email', 'phone', 'subject', 'message']));

        return response()->json(['message' => 'Contact submitted successfully']);
    }

    public function index()
    {
        return Contact::with(['followUps.user'])->latest()->get();
    }

    public function show(Contact $contact)
    {
        return $contact->load(['followUps.user']);
    }

    public function updateStatus(Request $request, Contact $contact)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Contact::getStatuses())),
        ]);

        $oldStatus = $contact->status;
        $contact->update(['status' => $request->status]);

        // Create a follow-up entry for status change
        if ($oldStatus !== $request->status) {
            FollowUp::create([
                'followable_id' => $contact->id,
                'followable_type' => Contact::class,
                'user_id' => Auth::id(),
                'type' => FollowUp::TYPE_STATUS_CHANGE,
                'content' => "Status changed from " . Contact::getStatuses()[$oldStatus] . " to " . Contact::getStatuses()[$request->status],
            ]);
        }

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function addFollowUp(Request $request, Contact $contact)
    {
        $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(FollowUp::getTypes())),
            'content' => 'required|string',
        ]);

        $followUp = FollowUp::create([
            'followable_id' => $contact->id,
            'followable_type' => Contact::class,
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
