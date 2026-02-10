<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaBudget;
use Illuminate\Http\Request;

class MpesaBudgetController extends Controller
{
    public function index()
    {
        $budgets = MpesaBudget::with('creator')->latest()->paginate(20);
        
        // Recalculate spending for all budgets
        foreach ($budgets as $budget) {
            $budget->recalculateSpending();
        }

        return view('admin.mpesa.budgets.index', compact('budgets'));
    }

    public function create()
    {
        return view('admin.mpesa.budgets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period_type' => 'required|in:daily,weekly,monthly,custom',
            'period_start' => 'required_if:period_type,custom|nullable|date',
            'period_end' => 'required_if:period_type,custom|nullable|date|after:period_start',
            'budget_limit' => 'required|numeric|min:0',
            'warning_threshold' => 'required|numeric|min:0|max:100',
            'critical_threshold' => 'required|numeric|min:0|max:100|gte:warning_threshold',
            'transaction_types' => 'nullable|array',
            'transaction_types.*' => 'in:c2b,b2c,b2b,stk_push,reversal',
            'direction' => 'nullable|in:inbound,outbound',
            'alerts_enabled' => 'boolean',
            'alert_emails' => 'nullable|string',
            'alert_phones' => 'nullable|string',
            'alert_on_warning' => 'boolean',
            'alert_on_critical' => 'boolean',
            'alert_on_exceeded' => 'boolean',
            'block_on_exceeded' => 'boolean',
        ]);

        // Process alert emails and phones
        $validated['alert_emails'] = $request->alert_emails 
            ? array_map('trim', explode(',', $request->alert_emails)) 
            : null;
        $validated['alert_phones'] = $request->alert_phones 
            ? array_map('trim', explode(',', $request->alert_phones)) 
            : null;

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;

        MpesaBudget::create($validated);

        return redirect()->route('admin.mpesa.budgets.index')
            ->with('success', 'Budget created successfully');
    }

    public function show(MpesaBudget $budget)
    {
        $budget->recalculateSpending();
        $budget->load('creator');
        
        return view('admin.mpesa.budgets.show', compact('budget'));
    }

    public function edit(MpesaBudget $budget)
    {
        return view('admin.mpesa.budgets.edit', compact('budget'));
    }

    public function update(Request $request, MpesaBudget $budget)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period_type' => 'required|in:daily,weekly,monthly,custom',
            'period_start' => 'required_if:period_type,custom|nullable|date',
            'period_end' => 'required_if:period_type,custom|nullable|date|after:period_start',
            'budget_limit' => 'required|numeric|min:0',
            'warning_threshold' => 'required|numeric|min:0|max:100',
            'critical_threshold' => 'required|numeric|min:0|max:100|gte:warning_threshold',
            'transaction_types' => 'nullable|array',
            'transaction_types.*' => 'in:c2b,b2c,b2b,stk_push,reversal',
            'direction' => 'nullable|in:inbound,outbound',
            'alerts_enabled' => 'boolean',
            'alert_emails' => 'nullable|string',
            'alert_phones' => 'nullable|string',
            'alert_on_warning' => 'boolean',
            'alert_on_critical' => 'boolean',
            'alert_on_exceeded' => 'boolean',
            'block_on_exceeded' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Process alert emails and phones
        $validated['alert_emails'] = $request->alert_emails 
            ? array_map('trim', explode(',', $request->alert_emails)) 
            : null;
        $validated['alert_phones'] = $request->alert_phones 
            ? array_map('trim', explode(',', $request->alert_phones)) 
            : null;

        $budget->update($validated);

        return redirect()->route('admin.mpesa.budgets.index')
            ->with('success', 'Budget updated successfully');
    }

    public function destroy(MpesaBudget $budget)
    {
        $budget->delete();

        return redirect()->route('admin.mpesa.budgets.index')
            ->with('success', 'Budget deleted successfully');
    }

    public function toggle(MpesaBudget $budget)
    {
        $budget->update(['is_active' => !$budget->is_active]);

        return back()->with('success', 'Budget status updated');
    }

    public function reset(MpesaBudget $budget)
    {
        $budget->resetForNewPeriod();

        return back()->with('success', 'Budget reset successfully');
    }

    public function recalculate(MpesaBudget $budget)
    {
        $budget->recalculateSpending();

        return back()->with('success', 'Budget spending recalculated');
    }
}
