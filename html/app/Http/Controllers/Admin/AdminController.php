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
                try {
                    $query = Withdraw::query()
                        ->where('withdraws.status', $request->status ?? 0)
                        ->join('users', 'users.id', '=', 'withdraws.user_id')
                        ->select('users.id as user_id', 'users.username as username', 'withdraws.*')
                        ->where('users.is_youtuber', '<', 1);

                    if ($request->status == 2 && $request->input('reason') == true) {
                        $query->whereNotNull('withdraws.reason');
                    }

                    return datatables()->eloquent($query)
                        ->addColumn('usdt', function(\App\Models\Withdraw $w) {
                            return $w->usdt ?? 0;
                        })
                        ->addColumn('image', function(\App\Models\Withdraw $w) {
                            return $w->image ?? '';
                        })
                        ->addColumn('variant', function(\App\Models\Withdraw $w) {
                            return $w->variant ?? null;
                        })
                        ->addColumn('method', function(\App\Models\Withdraw $w) {
                            // Возвращаем method как строку (значение enum)
                            return $w->method ? $w->method->value : null;
                        })
                        ->filterColumn('username', function($query, $keyword) {
                            $query->whereRaw('users.username LIKE ?', ["%{$keyword}%"]);
                        })
                        ->filterColumn('wallet', function($query, $keyword) {
                            $query->whereRaw('withdraws.wallet LIKE ?', ["%{$keyword}%"]);
                        })
                        ->filterColumn('system', function($query, $keyword) {
                            $query->whereRaw('withdraws.system LIKE ?', ["%{$keyword}%"]);
                        })
                        ->filterColumn('id', function($query, $keyword) {
                            if (ctype_digit($keyword)) {
                                $query->where('withdraws.id', (int) $keyword);
                            } else {
                                $query->whereRaw('CAST(withdraws.id AS CHAR) LIKE ?', ["%{$keyword}%"]);
                            }
                        })
                        ->toJson();
                } catch (\Exception $e) {
                    Log::error('DataTables withdraws error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return response()->json([
                        'draw' => $request->input('draw', 0),
                        'recordsTotal' => 0,
                        'recordsFiltered' => 0,
                        'data' => [],
                        'error' => 'Ошибка загрузки данных: ' . $e->getMessage()
                    ], 500);
                }
                break;
        }
    }
}
