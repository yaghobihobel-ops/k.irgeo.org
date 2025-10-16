<?php

namespace App\Lib\AuthorizedTransactions;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\UtilityBill;

class AuthorizeUtilityBill
{
    public function store($userAction)
    {
        $user    = auth()->user();
        $details = $userAction->details;
        if (@$details->total_amount > $user->balance) {
            $notify[] = 'Sorry! Insufficient balance';
            return apiResponse("validation_error", "error", $notify);
        }

        $company = Company::where('id', $details->company_id)->first();

        if (!$company) {
            $notify[] = 'Sorry! Utility not found';
            return apiResponse("validation_error", "error", $notify);
        }

        $userAction->is_used = Status::YES;
        $userAction->save();

        $user->balance -= $details->total_amount;
        $user->save();

        $utilityBill                   = new UtilityBill();
        $utilityBill->user_id          = $user->id;
        $utilityBill->company_id       = $company->id;
        $utilityBill->amount           = $details->amount;
        $utilityBill->charge           = $details->total_charge;
        $utilityBill->total            = $details->total_amount;
        $utilityBill->trx              = generateUniqueTrxNumber();
        $utilityBill->status           = 0;
        $utilityBill->unique_id        = $details->unique_id;
        $utilityBill->user_data        = $details->user_data;
        $utilityBill->save();

        $transaction                = new Transaction();
        $transaction->user_id       = $user->id;
        $transaction->amount        = $details->amount;
        $transaction->post_balance  = $user->balance;
        $transaction->charge        = $details->total_charge;
        $transaction->trx_type      = '-';
        $transaction->remark        = 'utility_bill';
        $transaction->details       = 'Utility bill';
        $transaction->trx           = $utilityBill->trx;
        $transaction->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New utility bill request from ' . $user->username;
        $adminNotification->click_url = urlPath('admin.utility.bill.all');
        $adminNotification->save();

        $notify[] = 'Successfully complete the utility bill process';
        return apiResponse("utility_bill_done", "success", $notify, [
            'redirect_type' => "new_url",
            'redirect_url'  => route('user.utility.bill.details', $utilityBill->id),
            'bill'          => $utilityBill
        ]);
    }
}
