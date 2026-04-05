<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();
        return view('admin.coupons.create', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:coupons,code'],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'plan' => ['nullable', 'required_without:subscription_plan_id', 'in:basic,premium'],
            'days' => ['nullable', 'required_without:subscription_plan_id', 'integer', 'min:1'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            // Features (Configuração Individual de Benefícios)
            'feature_no_ads' => ['nullable'],
            'feature_live_events' => ['nullable'],
            'feature_priority_support' => ['nullable'],
            'feature_priority_requests' => ['nullable'],
            'feature_premium_channels' => ['nullable'],
        ]);

        $features = [];
        if ($request->has('feature_no_ads')) $features[] = 'no_ads';
        if ($request->has('feature_live_events')) $features[] = 'live_events';
        if ($request->has('feature_priority_support')) $features[] = 'priority_support';
        if ($request->has('feature_priority_requests')) $features[] = 'priority_requests';
        if ($request->has('feature_premium_channels')) $features[] = 'premium_channels';

        Coupon::create([
            'code' => strtoupper($request->code),
            'subscription_plan_id' => $request->subscription_plan_id,
            'plan' => $request->plan,
            'days' => $request->days,
            'max_uses' => $request->max_uses,
            'features' => !empty($features) ? $features : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Cupom gerado com sucesso!');
    }

    public function edit(Coupon $coupon)
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();
        return view('admin.coupons.edit', compact('coupon', 'plans'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:coupons,code,' . $coupon->id],
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
            'plan' => ['nullable', 'required_without:subscription_plan_id', 'in:basic,premium'],
            'days' => ['nullable', 'required_without:subscription_plan_id', 'integer', 'min:1'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            // Features (Configuração Individual de Benefícios)
            'feature_no_ads' => ['nullable'],
            'feature_live_events' => ['nullable'],
            'feature_priority_support' => ['nullable'],
            'feature_priority_requests' => ['nullable'],
            'feature_premium_channels' => ['nullable'],
        ]);

        $features = [];
        if ($request->has('feature_no_ads')) $features[] = 'no_ads';
        if ($request->has('feature_live_events')) $features[] = 'live_events';
        if ($request->has('feature_priority_support')) $features[] = 'priority_support';
        if ($request->has('feature_priority_requests')) $features[] = 'priority_requests';
        if ($request->has('feature_premium_channels')) $features[] = 'premium_channels';

        $coupon->update([
            'code' => strtoupper($request->code),
            'subscription_plan_id' => $request->subscription_plan_id,
            'plan' => $request->plan,
            'days' => $request->days,
            'max_uses' => $request->max_uses,
            'features' => !empty($features) ? $features : null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Cupom atualizado.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Cupom deletado.');
    }
}
