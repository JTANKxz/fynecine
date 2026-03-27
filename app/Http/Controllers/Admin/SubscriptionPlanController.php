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
        return view('admin.subscription-plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_type' => 'required|in:basic,premium',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $features = [];
        if ($request->has('feature_no_ads')) $features[] = 'no_ads';
        if ($request->has('feature_priority_support')) $features[] = 'priority_support';
        if ($request->has('feature_priority_requests')) $features[] = 'priority_requests';
        if ($request->has('feature_premium_channels')) $features[] = 'premium_channels';

        SubscriptionPlan::create([
            'name' => $request->name,
            'plan_type' => $request->plan_type,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'features' => !empty($features) ? $features : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plano criado com sucesso!');
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('admin.subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_type' => 'required|in:basic,premium',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $features = [];
        if ($request->has('feature_no_ads')) $features[] = 'no_ads';
        if ($request->has('feature_priority_support')) $features[] = 'priority_support';
        if ($request->has('feature_priority_requests')) $features[] = 'priority_requests';
        if ($request->has('feature_premium_channels')) $features[] = 'premium_channels';

        $subscriptionPlan->update([
            'name' => $request->name,
            'plan_type' => $request->plan_type,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'features' => !empty($features) ? $features : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plano atualizado com sucesso!');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $subscriptionPlan->delete();
        return redirect()->route('admin.subscription-plans.index')->with('success', 'Plano removido com sucesso!');
    }
}
