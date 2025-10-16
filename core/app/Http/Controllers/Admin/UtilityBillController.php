<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use App\Models\User;
use App\Models\UtilityBill;
use Illuminate\Http\Request;

class UtilityBillController extends Controller
{
    public function pending()
    {
        $pageTitle = 'Pending Utility Bills';
        $billData  = $this->utilityBillData("pending");
        $bills     = $billData['data'];

        if (request()->export) {
            return exportData($bills, request()->export, "UtilityBill", "A4 landscape");
        }

        $bills  = $bills->paginate(getPaginate());
        $widget = $billData['widget'];

        return view('admin.utility_bills.history', compact('pageTitle', 'bills', 'widget'));
    }

    public function approved()
    {
        $pageTitle    = 'Approved Utility Bills';
        $billData = $this->utilityBillData("approved");
        $bills    = $billData['data'];

        if (request()->export) {
            return exportData($bills, request()->export, "UtilityBill", "A4 landscape");
        }

        $bills   = $bills->paginate(getPaginate());
        $widget = $billData['widget'];

        return view('admin.utility_bills.history', compact('pageTitle', 'bills', 'widget'));
    }

    public function rejected()
    {
        $pageTitle = 'Rejected Utility Bills';
        $billData   = $this->utilityBillData("rejected");
        $bills      = $billData['data'];

        if (request()->export) {
            return exportData($bills, request()->export, "UtilityBill", "A4 landscape");
        }

        $bills = $bills->paginate(getPaginate());
        $widget    = $billData['widget'];

        return view('admin.utility_bills.history', compact('pageTitle', 'bills', 'widget'));
    }

    public function all()
    {
        $pageTitle = 'All Utility Bills';
        $billData   = $this->utilityBillData();
        $bills      = $billData['data'];

        if (request()->export) {
            return exportData($bills, request()->export, "UtilityBill", "A4 landscape");
        }

        $bills = $bills->paginate(getPaginate());
        $widget    = $billData['widget'];

        return view('admin.utility_bills.history', compact('pageTitle', 'bills', 'widget'));
    }

    private function utilityBillData($scope = 'query')
    {

        $widget = [
            'pending'           => UtilityBill::pending()->sum('amount'),
            'approved'          => UtilityBill::approved()->sum('amount'),
            'rejected'          => UtilityBill::rejected()->sum('amount'),
            'all'               => UtilityBill::sum('amount'),
            'today_charge'      => UtilityBill::approved()->whereDate('created_at', now()->today())->sum('charge'),
            'yesterday_charge'  => UtilityBill::approved()->whereDate('created_at', now()->yesterday())->sum('charge'),
            'this_month_charge' => UtilityBill::approved()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('charge'),
            'all_charge'        => UtilityBill::approved()->sum('charge'),
        ];

        $query = UtilityBill::$scope();
        $bills = $query->searchable(['user:username', 'company:name', 'trx'])->dateFilter()->with('company', 'user', 'getTrx')->orderBy('id', getOrderBy());

        return [
            'data'    => $bills,
            'widget'  => $widget,
        ];
    }

    public function approve($id)
    {

        $utilityBill      = UtilityBill::where('status', Status::PENDING)->findOrFail($id);
        $setupUtilityBill = $utilityBill->company;
        $user             = User::findOrFail($utilityBill->user_id);

        $utilityBill->status = Status::APPROVED;
        $utilityBill->save();

        notify($user, 'UTILITY_BILL_APPROVE', [
            'user'         => $user->fullname,
            'amount'       => showAmount($utilityBill->amount, currencyFormat: false),
            'charge'       => showAmount($utilityBill->charge, currencyFormat: false),
            'utility'      => $setupUtilityBill->name,
            'trx'          => $utilityBill->trx,
            'time'         => showDateTime($utilityBill->created_at),
            'post_balance' => showAmount($utilityBill->getTrx->post_balance, currencyFormat: false),
        ]);

        $notify[] = ['success', 'Utility bill has been approved successfully'];
        return back()->withNotify($notify);
    }
    public function reject(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
        ]);

        $utilityBill      = UtilityBill::where('status', Status::PENDING)->findOrFail($id);
        $setupUtilityBill = $utilityBill->company;
        $user             = User::findOrFail($utilityBill->user_id);


        $utilityBill->status         = Status::REJECTED;
        $utilityBill->admin_feedback = $request->message;
        $utilityBill->save();

        $user->balance += $utilityBill->total;
        $user->save();

        $transaction                = new Transaction();
        $transaction->user_id       = $user->id;
        $transaction->amount        = $utilityBill->total;
        $transaction->post_balance  = $user->balance;
        $transaction->charge        = 0;
        $transaction->trx_type      = '+';
        $transaction->remark        = 'reject_utility_bill';
        $transaction->details       = 'Rejection of utility bill';
        $transaction->trx           = $utilityBill->trx;
        $transaction->save();

        notify($user, 'UTILITY_BILL_REJECT', [
            'user'         => $user->fullname,
            'amount'       => showAmount($utilityBill->amount, currencyFormat: false),
            'charge'       => showAmount($utilityBill->charge, currencyFormat: false),
            'utility'      => $setupUtilityBill->name,
            'trx'          => $utilityBill->trx,
            'reason'       => $request->message,
            'time'         => showDateTime($utilityBill->created_at),
            'post_balance' => showAmount($user->post_balance, currencyFormat: false),
        ]);

        $notify[] = ['success', 'Utility bill has been rejected successfully'];
        return back()->withNotify($notify);
    }

    public function chargeSetting()
    {
        $charge    = TransactionCharge::where('slug', "utility_charge")->firstOrFail();
        $pageTitle = "Utility Bills Charge & Limit Setting ";
        return view('admin.utility_bills.charge_setting', compact('pageTitle', 'charge'));
    }

    public function updateCharges(Request $request)
    {
        $request->validate([
            'min_limit'      => 'required|numeric|gte:0',
            'max_limit'      => 'required|numeric|gt:min_limit',
            'fixed_charge'   => 'required|numeric|gte:0',
            'percent_charge' => 'required|numeric|between:0,100',
            'cap'            => 'required|numeric|gte:-1',
            'daily_limit'    => 'required|numeric|gte:-1',
            'monthly_limit'  => 'required|numeric|gte:-1',
        ]);

        if ($request->monthly_limit != -1 && $request->monthly_limit < $request->daily_limit) {
            $notify[] = ['error', 'The daily limit must not exceed the monthly limit.'];
            return back()->withNotify($notify);
        }

        $charge = TransactionCharge::where('slug', 'utility_charge')->first();

        if (!$charge) {
            $charge       = new TransactionCharge();
            $charge->slug = "utility_charge";
        }

        $charge->percent_charge = $request->percent_charge ?? 0;
        $charge->fixed_charge   = $request->fixed_charge ?? 0;
        $charge->min_limit      = $request->min_limit ?? 0;
        $charge->max_limit      = $request->max_limit ?? 0;
        $charge->cap            = $request->cap ?? 0;
        $charge->monthly_limit  = $request->monthly_limit ?? 0;
        $charge->daily_limit    = $request->daily_limit ?? 0;
        $charge->save();


        $notify[] = ['success', 'Limit & charge updated successfully'];
        return back()->withNotify($notify);
    }
}
