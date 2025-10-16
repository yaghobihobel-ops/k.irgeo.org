<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\MobileRecharge;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\User;
use Illuminate\Http\Request;

class MobileRechargeController extends Controller
{
    public function pending()
    {
        $pageTitle          = 'Pending Mobile Recharge';
        $mobileRechargeData = $this->mobileRechargeData("pending");
        $mobileRecharges    = $mobileRechargeData['data'];

        if (request()->export) {
            return exportData($mobileRecharges, request()->export, "MobileRecharge", "A4 landscape");
        }

        $mobileRecharges = $mobileRecharges->paginate(getPaginate());
        $widget          = $mobileRechargeData['widget'];

        return view('admin.mobile_recharge.history', compact('pageTitle', 'mobileRecharges', 'widget'));
    }

    public function approved()
    {
        $pageTitle          = 'Approved Mobile Recharge';
        $mobileRechargeData = $this->mobileRechargeData("approved");
        $mobileRecharges    = $mobileRechargeData['data'];

        if (request()->export) {
            return exportData($mobileRecharges, request()->export, "MobileRecharge", "A4 landscape");
        }

        $mobileRecharges = $mobileRecharges->paginate(getPaginate());
        $widget          = $mobileRechargeData['widget'];

        return view('admin.mobile_recharge.history', compact('pageTitle', 'mobileRecharges', 'widget'));
    }

    public function rejected()
    {
        $pageTitle          = 'Rejected Mobile Recharge';
        $mobileRechargeData = $this->mobileRechargeData("rejected");
        $mobileRecharges    = $mobileRechargeData['data'];

        if (request()->export) {
            return exportData($mobileRecharges, request()->export, "MobileRecharge", "A4 landscape");
        }

        $mobileRecharges = $mobileRecharges->paginate(getPaginate());
        $widget          = $mobileRechargeData['widget'];

        return view('admin.mobile_recharge.history', compact('pageTitle', 'mobileRecharges', 'widget'));
    }

    public function all()
    {
        $pageTitle          = 'All Mobile Recharge';
        $mobileRechargeData = $this->mobileRechargeData();
        $mobileRecharges    = $mobileRechargeData['data'];

        if (request()->export) {
            return exportData($mobileRecharges, request()->export, "MobileRecharge", "A4 landscape");
        }

        $mobileRecharges = $mobileRecharges->paginate(getPaginate());
        $widget          = $mobileRechargeData['widget'];

        return view('admin.mobile_recharge.history', compact('pageTitle', 'mobileRecharges', 'widget'));
    }

    private function mobileRechargeData($scope = 'query')
    {

        $widget = [
            'pending'           => MobileRecharge::pending()->sum('amount'),
            'approved'          => MobileRecharge::approved()->sum('amount'),
            'rejected'          => MobileRecharge::rejected()->sum('amount'),
            'all'               => MobileRecharge::sum('amount'),
            'today_charge'      => MobileRecharge::approved()->whereDate('created_at', now()->today())->sum('charge'),
            'yesterday_charge'  => MobileRecharge::approved()->whereDate('created_at', now()->yesterday())->sum('charge'),
            'this_month_charge' => MobileRecharge::approved()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('charge'),
            'all_charge'        => MobileRecharge::approved()->sum('charge'),
        ];

        $query           = MobileRecharge::$scope();
        $mobileRecharges = $query->searchable(['user:username', 'mobileOperator:name', 'trx'])->dateFilter()->with('mobileOperator', 'user', 'getTrx')->orderBy('id', getOrderBy());

        return [
            'data'   => $mobileRecharges,
            'widget' => $widget,
        ];
    }

    public function approve($id)
    {
        $mobileRecharge         = MobileRecharge::where('status', Status::PENDING)->findOrFail($id);
        $mobileRecharge->status = Status::APPROVED;
        $mobileRecharge->save();

        $mobileOperator = $mobileRecharge->mobileOperator;
        $getTrx         = $mobileRecharge->getTrx;
        $user           = User::findOrFail($mobileRecharge->user_id);

        notify($user, 'MOBILE_RECHARGE_APPROVE', [
            'user'         => $user->fullname,
            'amount'       => showAmount($mobileRecharge->amount, currencyFormat: false),
            'charge'       => showAmount($getTrx->charge, currencyFormat: false),
            'mobile'       => $mobileRecharge->mobile,
            'operator'     => $mobileOperator->name,
            'trx'          => $mobileRecharge->trx,
            'time'         => showDateTime($mobileRecharge->created_at),
            'post_balance' => showAmount($getTrx->post_balance, currencyFormat: false),
        ]);

        $notify[] = ['success', 'Mobile recharge has been approved successfully'];
        return back()->withNotify($notify);
    }
    public function reject(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
        ]);

        $mobileRecharge = MobileRecharge::where('status', Status::PENDING)->findOrFail($id);
        $amount         = $mobileRecharge->amount + $mobileRecharge->charge;

        $mobileOperator = $mobileRecharge->mobileOperator;
        $getTrx         = $mobileRecharge->getTrx;
        $user           = User::findOrFail($mobileRecharge->user_id);

        $mobileRecharge->status         = Status::REJECTED;
        $mobileRecharge->admin_feedback = $request->message;
        $mobileRecharge->save();

        $user->balance += $amount;
        $user->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->remark       = 'reject_mobile_recharge';
        $transaction->details      = 'Rejection of mobile recharge';
        $transaction->trx          = $mobileRecharge->trx;
        $transaction->save();

        notify($user, 'MOBILE_RECHARGE_REJECT', [
            'user'         => $user->fullname,
            'amount'       => showAmount($mobileRecharge->amount, currencyFormat: false),
            'charge'       => showAmount($getTrx->charge, currencyFormat: false),
            'mobile'       => $mobileRecharge->mobile,
            'operator'     => $mobileOperator->name,
            'trx'          => $getTrx->trx,
            'reason'       => $request->message,
            'time'         => showDateTime($mobileRecharge->created_at),
            'post_balance' => showAmount($getTrx->post_balance, currencyFormat: false),
        ]);

        $notify[] = ['success', 'Mobile recharge has been rejected successfully'];
        return back()->withNotify($notify);
    }

    public function chargeSetting()
    {
        $charge    = TransactionCharge::where('slug', "mobile_recharge")->firstOrFail();
        $pageTitle = "Mobile Recharge Charge & Limit Setting ";
        return view('admin.mobile_recharge.charge_setting', compact('pageTitle', 'charge'));
    }

    public function updateCharges(Request $request)
    {
        $request->validate([
            'min_limit'      => 'required|numeric|gte:0',
            'max_limit'      => 'required|numeric|gt:min_limit',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'cap'            => 'required|numeric|gte:-1',
        ]);

        $charge = TransactionCharge::where('slug', 'mobile_recharge')->first();

        if (!$charge) {
            $charge       = new TransactionCharge();
            $charge->slug = "mobile_recharge";
        }

        $charge->percent_charge = $request->percent_charge ?? 0;
        $charge->fixed_charge   = $request->fixed_charge ?? 0;
        $charge->min_limit      = $request->min_limit ?? 0;
        $charge->max_limit      = $request->max_limit ?? 0;
        $charge->cap            = $request->cap ?? 0;
        $charge->save();


        $notify[] = ['success', 'Limit & charge updated successfully'];
        return back()->withNotify($notify);
    }
}
