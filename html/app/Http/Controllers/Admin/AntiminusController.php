<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profit;
use App\Models\Setting;
use Illuminate\Http\Request;

class AntiminusController extends Controller
{
    public function index(Request $r)
    {
        if($r->user()->admin_role == 'moder'){
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        return view('admin.antiminus.index');
    }

    public function save(Request $request)
    {
        Profit::find(1)->update($request->all());
        Setting::find(1)->update([
            'antiminus' => $request->antiminus
        ]);

        return redirect()->back()->withSuccess('Сохранено');
    }
}