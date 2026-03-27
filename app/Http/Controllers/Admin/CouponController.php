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
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:coupons,code'],
            'plan' => ['required', 'in:basic,premium'],
            'days' => ['required', 'integer', 'min:1'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            // Features Opcionais
            'feature_no_ads' => ['nullable'],
            'feature_priority_support' => ['nullable'],
            'feature_priority_requests' => ['nullable'],
        ]);

        $features = [];
        if ($request->has('feature_no_ads')) $features[] = 'no_ads';
        if ($request->has('feature_priority_support')) $features[] = 'priority_support';
        if ($request->has('feature_priority_requests')) $features[] = 'priority_requests';

        Coupon::create([
            'code' => strtoupper($request->code),
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
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => ['required', 'string', 'unique:coupons,code,' . $coupon->id],
            'plan' => ['required', 'in:basic,premium'],
            'days' => ['required', 'integer', 'min:1'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ]);

        $features = [];
        if ($request->has('feature_no_ads')) $features[] = 'no_ads';
        if ($request->has('feature_priority_support')) $features[] = 'priority_support';
        if ($request->has('feature_priority_requests')) $features[] = 'priority_requests';

        $coupon->update([
            'code' => strtoupper($request->code),
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
