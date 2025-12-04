<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\B2BSlotsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MobuleSlotController;
use App\Models\Action;
use App\Models\B2bSlot;
use App\Models\BonusLevel;
use App\Models\MobuleSlot;
use App\Models\Payment;
use App\Models\Promocode;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class AdminController extends Controller
{
    public function versionUpdate()
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        Setting::where('id', 1)->update([
            'file_version' => time()
        ]);
        return response()->json(['success' => true, 'msg' => 'Версия обновлена!']);
    }

    public function slotsUpdate()
    {
        try {
            
            B2bSlot::truncate();
            MobuleSlot::truncate();

           
            $b2bSlotsController = new B2BSlotsController();
            $mobuleSlotController = new MobuleSlotController();

            $mobuleResult = $mobuleSlotController->fetchAndStore();
            $b2bResult = $b2bSlotsController->fetchAndStore();
            

            
            if ($b2bResult && $mobuleResult) {
                return response()->json(['success' => true, 'msg' => 'Слоты обновлены!']);
            }

            \Log::error('Ошибка обновления слотов', [
                'b2bResult' => $b2bResult,
                'mobuleResult' => $mobuleResult
            ]);

            return response()->json(['success' => false, 'msg' => 'Ошибка при обновлении слотов']);
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении слотов: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'msg' => 'Ошибка на стороне сервера']);
        }
    }


    public function load(Request $request)
    {
        switch ($request->type) {

            case 'users':
                if ($request->user()->admin_role == 'moder') {
                    return [
                        'error' => true,
                        'message' => 'Ваша роль не позволяет совершить это действие',
                        'reload' => true
                    ];
                }
                return datatables(User::query())->toJson();
                break;

            case 'user_actions':
                $userId = $request->input('user_id');
                $query = Action::where('user_id', $userId);
    
                // Фильтрация по дате
                if ($request->filled('start_date')) {
                    $query->where('created_at', '>=', $request->input('start_date') . ' 00:00:00');
                }
                if ($request->filled('end_date')) {
                    $query->where('created_at', '<=', $request->input('end_date') . ' 23:59:59');
                }
    
                return datatables($query->orderByDesc('id'))->toJson();
                break;

            case 'deposits':
                $query = Payment::select([
                    'payments.*',
                    'users.username as username'
                ])
                    ->join('users', 'users.id', '=', 'payments.user_id');

                if ($request->user()->admin_role == 'moder') {
                    $query->where('payments.created_at', '>=', Carbon::now()->subDays(30));
                } else {
                    $query->where('payments.status', 1);
                }

                return datatables()->eloquent($query)->toJson();

            case 'bots':
                if ($request->user()->admin_role == 'moder') {
                    return [
                        'error' => true,
                        'message' => 'Ваша роль не позволяет совершить это действие',
                        'reload' => true
                    ];
                }
                return datatables(User::query()->where('is_bot', '=', 1))->toJson();
                break;

            case 'promocodes':
                if ($request->user()->admin_role == 'moder') {
                    return [
                        'error' => true,
                        'message' => 'Ваша роль не позволяет совершить это действие',
                        'reload' => true
                    ];
                }
                $promocodes = Promocode::where('name', '!=', '')->get();
                // ->leftJoin('promocode_activations', function ($join) {
                //     $join->on('promocodes.id', '=', 'promocode_activations.promo_id');
                // })
                // ->select('promocodes.id', 'promocodes.name', 'promocodes.sum', 'promocodes.activation', 'promocodes.wager', 'promocodes.type', 'promocodes.end_time', DB::raw('count(promocode_activations.id) as activated'))
                // ->groupBy('promocodes.id')->get();

                return Datatables::of($promocodes)->make(true);
                break;

            case 'bonus':
                if ($request->user()->admin_role == 'moder') {
                    return [
                        'error' => true,
                        'message' => 'Ваша роль не позволяет совершить это действие',
                        'reload' => true
                    ];
                }
                return datatables(BonusLevel::all())->toJson();
                break;

            case 'withdraws':
                $search = $request->input('search.value');
                $request->request->remove('search');

                $query = Withdraw::where('status', $request->status);

                if ($search !== null && $search !== '') {
                    $query->where(function ($q) use ($search) {
                        if (ctype_digit($search)) {
                            $q->orWhere('withdraws.id', (int) $search);
                        }
                        $q->orWhereRaw('CAST(withdraws.id AS CHAR) LIKE ?', ["%{$search}%"]);
                        $q->orWhereRaw('username LIKE ?', ["%{$search}%"]);
                        $q->orWhereRaw('wallet LIKE ?', ["%{$search}%"]);
                        $q->orWhereRaw('`system` LIKE ?', ["%{$search}%"]);
                        $q->orWhereRaw('CAST(withdraws.created_at AS CHAR) LIKE ?', ["%{$search}%"]);
                    });
                }


                if ($request->status == 2 && $request->reason == true) {
                    $withdraws = $query
                        ->whereNotNull('reason')
                        ->join('users', 'users.id', '=', 'withdraws.user_id')
                        ->select('users.id as user_id', 'users.username as username', 'withdraws.*')
                        ->where('users.is_youtuber', '<', 1)
                        ->get();
                } else {
                    $withdraws = $query
                        ->join('users', 'users.id', '=', 'withdraws.user_id')
                        ->select('users.id as user_id', 'users.username as username', 'withdraws.*')
                        ->where('users.is_youtuber', '<', 1)
                        ->get();
                }

                return Datatables::of($withdraws)
                    ->addColumn('usdt', fn(\App\Models\Withdraw $w) => $w->usdt)
                    ->addColumn('image', fn(\App\Models\Withdraw $w) => $w->image)
                    ->make(true);
                break;
        }
    }
}
