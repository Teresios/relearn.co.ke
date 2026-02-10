<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaForwardingRule;
use Illuminate\Http\Request;

class MpesaForwardingController extends Controller
{
    public function index()
    {
        $rules = MpesaForwardingRule::with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.mpesa.forwarding.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.mpesa.forwarding.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_phone' => 'nullable|string|max:20',
            'source_shortcode' => 'nullable|string|max:20',
            'destination_shortcode' => 'required|string|max:20',
            'destination_account' => 'nullable|string|max:50',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
            'forward_percentage' => 'required|numeric|min:0|max:100',
            'flat_fee' => 'nullable|numeric|min:0',
            'auto_forward' => 'boolean',
            'delay_seconds' => 'nullable|integer|min:0',
            'active_hours_start' => 'nullable|date_format:H:i',
            'active_hours_end' => 'nullable|date_format:H:i',
            'active_days' => 'nullable|array',
            'active_days.*' => 'integer|min:0|max:6',
        ]);

        // Process active hours
        $validated['active_hours'] = null;
        if ($request->active_hours_start && $request->active_hours_end) {
            $validated['active_hours'] = [
                'start' => $request->active_hours_start,
                'end' => $request->active_hours_end,
            ];
        }
        unset($validated['active_hours_start'], $validated['active_hours_end']);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;

        MpesaForwardingRule::create($validated);

        return redirect()->route('admin.mpesa.forwarding.index')
            ->with('success', 'Forwarding rule created successfully');
    }

    public function show(MpesaForwardingRule $forwarding)
    {
        $forwarding->load('creator');
        
        return view('admin.mpesa.forwarding.show', compact('forwarding'));
    }

    public function edit(MpesaForwardingRule $forwarding)
    {
        return view('admin.mpesa.forwarding.edit', compact('forwarding'));
    }

    public function update(Request $request, MpesaForwardingRule $forwarding)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'source_phone' => 'nullable|string|max:20',
            'source_shortcode' => 'nullable|string|max:20',
            'destination_shortcode' => 'required|string|max:20',
            'destination_account' => 'nullable|string|max:50',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
            'forward_percentage' => 'required|numeric|min:0|max:100',
            'flat_fee' => 'nullable|numeric|min:0',
            'auto_forward' => 'boolean',
            'delay_seconds' => 'nullable|integer|min:0',
            'active_hours_start' => 'nullable|date_format:H:i',
            'active_hours_end' => 'nullable|date_format:H:i',
            'active_days' => 'nullable|array',
            'active_days.*' => 'integer|min:0|max:6',
            'is_active' => 'boolean',
        ]);

        // Process active hours
        $validated['active_hours'] = null;
        if ($request->active_hours_start && $request->active_hours_end) {
            $validated['active_hours'] = [
                'start' => $request->active_hours_start,
                'end' => $request->active_hours_end,
            ];
        }
        unset($validated['active_hours_start'], $validated['active_hours_end']);

        $forwarding->update($validated);

        return redirect()->route('admin.mpesa.forwarding.index')
            ->with('success', 'Forwarding rule updated successfully');
    }

    public function destroy(MpesaForwardingRule $forwarding)
    {
        $forwarding->delete();

        return redirect()->route('admin.mpesa.forwarding.index')
            ->with('success', 'Forwarding rule deleted successfully');
    }

    public function toggle(MpesaForwardingRule $forwarding)
    {
        $forwarding->update(['is_active' => !$forwarding->is_active]);

        return back()->with('success', 'Forwarding rule status updated');
    }

    public function testForward(Request $request, MpesaForwardingRule $forwarding)
    {
        // Simulate forward test (dry run)
        $testAmount = $request->input('amount', 100);
        $forwardAmount = $forwarding->calculateForwardAmount($testAmount);

        return response()->json([
            'rule' => $forwarding->name,
            'source_amount' => $testAmount,
            'forward_amount' => $forwardAmount,
            'fee_deducted' => $forwarding->flat_fee,
            'percentage_applied' => $forwarding->forward_percentage,
            'within_active_hours' => $forwarding->isWithinActiveHours(),
            'on_active_day' => $forwarding->isOnActiveDay(),
            'can_forward' => $forwarding->is_active && $forwarding->isWithinActiveHours() && $forwarding->isOnActiveDay(),
        ]);
    }
}
