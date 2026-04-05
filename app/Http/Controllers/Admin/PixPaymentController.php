<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PixPayment;
use Illuminate\Http\Request;

class PixPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = PixPayment::with(['user', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('admin.pix_payments.index', compact('payments'));
    }
}
