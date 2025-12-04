<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromocodeController extends Controller
{
    public function index(Request $r)
    {
        if ($r->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        return view('admin.promocodes.index');
    }

    public function create()
    {
        return view('admin.promocodes.create');
    }

    public function createPost(Request $request)
    {
        $amount = $request->amount;
        if (!$amount) {
            $amount = 1;
        }
        if ($amount > 1) {
            do {
                $name = Str::random(8);
                Promocode::query()->create([
                    'name' => $name,
                    'sum' => $request->sum,
                    'quantity_spin' => $request->quantity_spin,
                    'id_spin' => $request->id_spin,
                    'activation' => $request->activation,
                    'wager' => $request->wager,
                    'type' => $request->type,
                    'min_deposits' => $request->min_deposits,
                    'deposits_days' => $request->deposits_days,
                    'end_time' => $request->end_time,
                    'only_private_club' => $request->only_private_club,
                ]);
                $amount -= 1;
            } while ($amount >= 1);
        } else {
            Promocode::query()->create($request->except('amount'));
        }

        return redirect('/admin/promocodes')->with('success', 'Промокод создан! ');
    }

    public function edit($id)
    {
        $promocode = Promocode::query()->find($id);

        if (!$promocode) {
            return redirect()->back();
        }

        return view('admin.promocodes.edit', compact('promocode'));
    }

    public function editPost($id, Request $r)
    {
        Promocode::query()->find($id)->update($r->all());

        return redirect('/admin/promocodes/edit/' . $id);
    }

    public function delete($id)
    {
        Promocode::query()->find($id)->delete();

        return redirect()->back()->with('success', 'Промокод удален!');
    }
}
