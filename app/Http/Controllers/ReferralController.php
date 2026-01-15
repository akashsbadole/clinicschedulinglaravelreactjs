<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReferralStoreRequest;
use App\Http\Requests\ReferralUpdateRequest;
use App\Models\Referral;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function index(): View
    {
        $referrals = Referral::with(['patient', 'referringDoctor', 'receivingDoctor', 'encounter'])->paginate(10);
        return view('referrals.index', compact('referrals'));
    }

    public function create(): View
    {
        return view('referrals.create');
    }

    public function store(ReferralStoreRequest $request): RedirectResponse
    {
        $referral = Referral::create($request->validated());
        return redirect()->route('referrals.index')->with('success', 'Referral created successfully.');
    }

    public function show(Referral $referral): View
    {
        $referral->load(['patient', 'referringDoctor', 'receivingDoctor', 'encounter']);
        return view('referrals.show', compact('referral'));
    }

    public function edit(Referral $referral): View
    {
        return view('referrals.edit', compact('referral'));
    }

    public function update(ReferralUpdateRequest $request, Referral $referral): RedirectResponse
    {
        $referral->update($request->validated());
        return redirect()->route('referrals.index')->with('success', 'Referral updated successfully.');
    }

    public function destroy(Referral $referral): RedirectResponse
    {
        $referral->delete();
        return redirect()->route('referrals.index')->with('success', 'Referral deleted successfully.');
    }

    public function apiIndex(): JsonResponse
    {
        $referrals = Referral::with(['patient', 'referringDoctor', 'receivingDoctor', 'encounter'])->get();
        return response()->json($referrals);
    }

    public function apiStore(ReferralStoreRequest $request): JsonResponse
    {
        $referral = Referral::create($request->validated());
        return response()->json($referral, 201);
    }

    public function apiShow(Referral $referral): JsonResponse
    {
        $referral->load(['patient', 'referringDoctor', 'receivingDoctor', 'encounter']);
        return response()->json($referral);
    }

    public function apiUpdate(ReferralUpdateRequest $request, Referral $referral): JsonResponse
    {
        $referral->update($request->validated());
        return response()->json($referral);
    }

    public function apiDestroy(Referral $referral): JsonResponse
    {
        $referral->delete();
        return response()->json(null, 204);
    }
}