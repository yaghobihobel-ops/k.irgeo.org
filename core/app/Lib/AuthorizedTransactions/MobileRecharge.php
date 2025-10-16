<?php

namespace App\Lib\AuthorizedTransactions;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\MobileOperator;
use App\Models\MobileRecharge as ModelsMobileRecharge;
use App\Models\Transaction;

class MobileRecharge
{
    public function store($userAction)
    {

        $details = $userAction->details;
        $user    = auth()->user();

        if (@$details->total_amount > $user->balance) {
            $notify[] = "Sorry! Insufficient balance";
            return apiResponse("validation_error", "error", $notify);
        }

        $operator = MobileOperator::find($details->operator_id);

        $mobileRecharge                     = new ModelsMobileRecharge();
        $mobileRecharge->user_id            = $user->id;
        $mobileRecharge->mobile_operator_id = $operator->id;
        $mobileRecharge->mobile             = $details->mobile;
        $mobileRecharge->amount             = $details->amount;
        $mobileRecharge->charge             = $details->total_charge;
        $mobileRecharge->total              = $details->total_amount;
        $mobileRecharge->trx                = generateUniqueTrxNumber();
        $mobileRecharge->status             = Status::PENDING;
        $mobileRecharge->save();

        $user->balance -= $details->total_amount;
        $user->save();

        $transaction                = new Transaction();
        $transaction->user_id       = $user->id;
        $transaction->amount        = $details->amount;
        $transaction->post_balance  = $user->balance;
        $transaction->charge        = $details->total_charge;
        $transaction->trx_type      = '-';
        $transaction->remark        = 'mobile_recharge';
        $transaction->details       = 'Mobile recharge';
        $transaction->trx           = $mobileRecharge->trx;
        $transaction->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New mobile recharge request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.mobile.recharge.all');
        $adminNotification->save();

        $notify[] = "Mobile Recharge Successful";

        return apiResponse("mobile_recharge_done", "success", $notify, [
            'redirect_type' => "same_url",
            'recharge'      => $mobileRecharge
        ]);
    }
}
