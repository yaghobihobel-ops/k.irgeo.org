<?php

namespace App\Traits;

use App\Models\MobileOperator;
use App\Models\MobileRecharge;
use App\Models\TransactionCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait MobileRechargeOperation
{
    public function create()
    {
        $pageTitle            = 'Mobile Recharge';
        $user                 = auth()->user();
        $view                 = 'Template::user.mobile_recharge.create';
        $mobileRechargeCharge = TransactionCharge::where('slug', 'mobile_recharge')->first();
        $latestMobileRecharge = MobileRecharge::latest()->where('user_id', $user->id)->groupBy('mobile')->take(3)->with('mobileOperator')->get();
        $operators            = MobileOperator::active()->get();
        return responseManager("mobile_recharge", $pageTitle, 'success', compact('view', 'pageTitle', 'mobileRechargeCharge', 'latestMobileRecharge', 'operators'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'        => 'required|integer|gt:0',
            'operator'   => 'required|integer',
            'mobile_number' => 'required',
            ...getOtpValidationRules()
        ]);

        if ($validator->fails()) {
            return apiResponse("validation_error", "error", $validator->errors()->all());
        }

        $user     = auth()->user();
        $operator = MobileOperator::active()->find($request->operator);

        if (!$operator) {
            $notify[] = "Sorry,The operator not found";
            return apiResponse("validation_error", "error", $notify);
        }

        $mobileRechargeCharge = TransactionCharge::where('slug', 'mobile_recharge')->first();

        if (!$mobileRechargeCharge) {
            $notify[] = "Sorry, Transaction charge not found";
            return apiResponse("validation_error", "error", $notify);
        }

        if ($request->amount < $mobileRechargeCharge->min_limit || $request->amount > $mobileRechargeCharge->max_limit) {
            $notify[] = "Please follow the mobile recharge limit";
            return apiResponse("validation_error", "error", $notify);
        }

        if ($operator->fixed_charge > 0 || $operator->percent_charge > 0) {
            $fixedCharge   = $operator->fixed_charge;
            $percentCharge = $request->amount * $operator->percent_charge / 100;
            $totalCharge   = $fixedCharge + $percentCharge;
        } else {
            $fixedCharge   = $mobileRechargeCharge->fixed_charge;
            $percentCharge = $request->amount * $mobileRechargeCharge->percent_charge / 100;
            $totalCharge   = $fixedCharge + $percentCharge;
        }

        $cap = $mobileRechargeCharge->cap;

        if ($cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $totalAmount   = getAmount($request->amount + $totalCharge);

        if ($totalAmount > $user->balance) {
            $notify[] = "Sorry! Insufficient balance";
            return apiResponse("validation_error", "error", $notify);
        }

        $details = [
            'amount'       => $request->amount,
            'total_amount' => $totalAmount,
            'total_charge' => $totalCharge,
            'operator_id'  => $operator->id,
            'mobile'       => $request->mobile_number
        ];

        return storeAuthorizedTransactionData("mobile_recharge", $details);
    }


    public function history()
    {
        $pageTitle = 'Mobile Recharge History';
        $view      = 'Template::user.mobile_recharge.index';
        $recharges = MobileRecharge::where('user_id', auth()->id())
            ->with(['mobileOperator'])
            ->latest('id')
            ->searchable(['trx', 'mobile'])
            ->paginate(getPaginate());

        return responseManager("mobile_recharge_history", $pageTitle, 'success', compact('view', 'pageTitle', 'recharges'));
    }
}
