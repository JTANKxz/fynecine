<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::paginate(10);
        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.subscription-plans.index')->with('error', 'Acesso negado. Apenas administradores podem criar planos.');
        }
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.subscription-plans.index')->with('error', 'Acesso negado.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_type' => 'required|in:basic,premium',
            'plan_category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'first_time_discount' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
        ]);

        $features = collect($request->input('features', []))->map(function($feature) {
            return [
                'name' => $feature['name'] ?? '',
                'included' => isset($feature['included']) && $feature['included'] == '1'
            ];
        })->filter(fn($f) => !empty($f['name']))->values()->toArray();

        SubscriptionPlan::create([
            'name' => $request->name,
            'plan_type' => $request->plan_type,
            'plan_category' => $request->plan_category,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'first_time_discount' => $request->first_time_discount ?: 0,
            'duration_days' => $request->duration_days,
            'features' => !empty($features) ? $features : null,
            'is_active' => $request->has('is_active'),
            'points_cost' => $request->points_cost ?: null,
        ]);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plano criado com sucesso!');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.subscription-plans.index')->with('error', 'Acesso negado. Apenas administradores podem editar planos.');
        }
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.subscription-plans.index')->with('error', 'Acesso negado.');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_type' => 'required|in:basic,premium',
            'plan_category' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'first_time_discount' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
        ]);

        $features = collect($request->input('features', []))->map(function($feature) {
            return [
                'name' => $feature['name'] ?? '',
                'included' => isset($feature['included']) && $feature['included'] == '1'
            ];
        })->filter(fn($f) => !empty($f['name']))->values()->toArray();

        $subscriptionPlan->update([
            'name' => $request->name,
            'plan_type' => $request->plan_type,
            'plan_category' => $request->plan_category,
            'price' => $request->price,
            'original_price' => $request->original_price,
            'first_time_discount' => $request->first_time_discount ?: 0,
            'duration_days' => $request->duration_days,
            'features' => !empty($features) ? $features : null,
            'is_active' => $request->has('is_active'),
            'points_cost' => $request->points_cost ?: null,
        ]);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plano atualizado com sucesso!');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('admin.subscription-plans.index')->with('error', 'Acesso negado.');
        }
        $subscriptionPlan->delete();
        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plano removido com sucesso!');
    }
}
