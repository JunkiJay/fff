<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DepositsController extends Controller
{
    public function index()
    {
        return view('admin.deposits.index');
    }

    public function init(Request $request)
    {
        $user = $request->user();
        $depositSum = Payment::where('status', 1)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::create(2025, 6, 26, 0, 0, 0))
            ->sum('sum');


        return response()->json([
            'success' => true,
            'sum' => $depositSum,
            'vip' => $depositSum >= 10000,
            'tg_link' => $depositSum >= 10000 ? 'https://t.me/stimule_bot/?start=bind_' . $user->id : ''
        ], 200);
    }
}
