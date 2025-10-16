<?php

namespace App\Traits;

use App\Models\TransactionCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Lib\FormProcessor;
use App\Models\BillCategory;
use App\Models\Company;
use App\Models\Form;
use App\Models\UserCompany;
use App\Models\UtilityBill;
use Barryvdh\DomPDF\Facade\Pdf;

trait UtilityOperation
{
    public function create()
    {
        $pageTitle    = 'Utility Bill';
        $view         = 'Template::user.utility_bill.create';
        $billCategory = BillCategory::active()->with("company", function ($q) {
            $q->active()->with('form');
        })->get();
        $userCompanies = UserCompany::where('user_id', auth()->user()->id)->with('company', function ($q) {
            $q->active()->with('form');
        })->get();
        $utilityCharge = TransactionCharge::where('slug', 'utility_charge')->first();
        $companies     = Company::active()->with('form')->get();

        return responseManager("utility_bill", $pageTitle, 'success', compact('view', 'pageTitle', 'billCategory', 'utilityCharge', 'companies', 'userCompanies'), ['companies']);
    }

    public function form($id)
    {
        $form = Form::where('id', $id)->first();

        if (!$form || !$form->form_data) {
            $notify[] = 'The platform admin could not configure company properly';
            return apiResponse('error', 'error', $notify);
        }

        $hideFile  = request()->hide_file ?? 'no';
        $content   = view('Template::user.utility_bill.form', compact('form', 'hideFile'))->render();
        $message[] = 'Utility Bill Form';

        return apiResponse('success', 'success', $message, ['content' => $content]);
    }

    public function storeUserCompany(Request $request)
    {
        $user    = auth()->user();
        $company = Company::where('id', $request->company_id)->first();

        if (!$company) {
            return responseManager('error', 'Company not found');
        }

        $userCompanyExists = UserCompany::where('company_id', $request->company_id)->where('unique_id', $request->unique_id)->where('user_id', $user->id)->exists();

        if ($userCompanyExists) {
            return responseManager('error', 'The save account is already exists');
        }

        if ($company->form) {
            $formData = $company->form->form_data;
            $formProcessor  = new FormProcessor();
            $validationRule = $formProcessor->valueValidation($formData, true);
            $userData       = $formProcessor->processFormData($request, $formData);
        } else {
            $userData       = [];
            $validationRule = [];
        }

        $request->validate(array_merge([
            'company_id'       => 'required',
            'unique_id'        => 'required|max:255'
        ], $validationRule));

        $userCompany                   = new UserCompany();
        $userCompany->user_id          = $user->id;
        $userCompany->company_id       = $company->id;
        $userCompany->unique_id        = $request->unique_id;
        $userCompany->user_data        = $userData;
        $userCompany->save();

        return responseManager("success", "The user company added successfully", 'success');
    }

    public function userCompanyDetails($id)
    {
        $user = auth()->user();
        $company = UserCompany::with(['company' => function ($q) {
            $q->active()->with('form');
        }])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$company) {
            return responseManager('error', 'User company not found');
        }

        $content = view('Template::user.utility_bill.account_details', compact('company'))->render();

        $message[] = 'User Company Details';
        return apiResponse('success', 'success', $message, ['content' => $content]);
    }

    public function deleteUserCompany($id)
    {
        $user        = auth()->user();
        $userCompany = UserCompany::where('id', $id)->where('user_id', $user->id)->first();

        if (!$userCompany) {
            return responseManager('error', 'The user company not found');
        }

        $userCompany->delete();

        return responseManager("success", "The utility bill account deleted successfully", 'success');
    }

    public function store(Request $request)
    {


        $request->validate([
            'user_company_id' => 'nullable',
            'amount'          => 'required',
            'company_id'      => 'required',
        ], [
            'company_id.required' => "Please select a company"
        ]);


        $user     = auth()->user();
        $company  = Company::active()->where('id', $request->company_id)->firstOrFailWithApi('Company');
        $uniqueId = null;


        if ($request->user_company_id) {
            $userCompany = UserCompany::with(['company' => function ($q) {
                $q->active()->with('form');
            }])->where('id', $request->user_company_id)
                ->where('user_id', $user->id)
                ->where('company_id', $company->id)
                ->first();


            if (!$userCompany) {
                $notify = 'Sorry, The save account is not found';
                return responseManager('error', $notify);
            }

            $uniqueId = $userCompany->unique_id;
        }

        $form = $company->form;


        if (!$form || !$form->form_data) {
            $notify = 'The platform admin could not configure company properly ';
            return responseManager('error', $notify);
        }

        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $userData       = $formProcessor->processFormData($request, $formData);


        $request->validate($validationRule, $validationRule);


        $utilityCharge = TransactionCharge::where('slug', 'utility_charge')->first();


        if (!$utilityCharge) {
            $notify[] = "Sorry, Transaction charge not found";
            return apiResponse("validation_error", "error", $notify);
        }

        if ($request->amount < $utilityCharge->min_limit || $request->amount > $utilityCharge->max_limit) {
            $notify[] = "Please Follow the utility payment limit";
            return apiResponse("validation_error", "error", $notify);
        }

        $dailyTransaction = UtilityBill::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        if ($utilityCharge->daily_limit != -1 && ($dailyTransaction + $request->amount) > $utilityCharge->daily_limit) {
            $notify[] = 'Your daily utility bill limit exceeded';
            return apiResponse("validation_error", "error", $notify);
        }

        $monthlyTransaction = UtilityBill::where('user_id', $user->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        if ($utilityCharge->monthly_limit != -1 && ($monthlyTransaction + $request->amount) > $utilityCharge->monthly_limit) {
            $notify[] = 'Your monthly utility bill limit exceeded';
            return apiResponse("validation_error", "error", $notify);
        }

        if ($company->fixed_charge > 0 || $company->percent_charge > 0) {
            $fixedCharge   = $company->fixed_charge;
            $percentCharge = $request->amount * $company->percent_charge / 100;
            $totalCharge   = $fixedCharge + $percentCharge;
        } else {
            $fixedCharge   = $utilityCharge->fixed_charge;
            $percentCharge = $request->amount * $utilityCharge->percent_charge / 100;
            $totalCharge   = $fixedCharge + $percentCharge;
        }

        $cap = $utilityCharge->cap;

        if ($cap != -1 && $totalCharge > $cap) {
            $totalCharge = $cap;
        }

        $totalAmount   = getAmount($request->amount + $totalCharge);

        if ($totalAmount > $user->balance) {
            $notify[] = 'Sorry! Insufficient balance';
            return apiResponse("validation_error", "error", $notify);
        }
        $details = [
            'amount'           => $request->amount,
            'total_amount'     => $totalAmount,
            'total_charge'     => $totalCharge,
            'company_id'       => $company->id,
            'unique_id'        => $uniqueId,
            'user_data'        => $userData ?? [],
        ];

        return storeAuthorizedTransactionData("utility_bill", $details);
    }

    public function history()
    {
        $pageTitle = 'Utility Bill History';
        $user = auth()->user();
        $view = 'Template::user.utility_bill.index';

        $utilityBills = UtilityBill::where('user_id', $user->id)
            ->with(['company'])
            ->latest()
            ->searchable(['trx', 'company:name'])
            ->paginate(getPaginate());

        return responseManager("utility_bill_history", $pageTitle, 'success', compact('view', 'pageTitle', 'user', 'utilityBills'));
    }

    public function details($id)
    {
        $pageTitle   = 'Utility Bill Details';
        $user        = auth()->user();
        $view        = 'Template::user.utility_bill.details';
        $utilityBill = UtilityBill::where('id', $id)->where('user_id', $user->id)->first();

        if (!$utilityBill) {
            $notify = "The utility bill transaction is not found";
            return responseManager('not_fund', $notify);
        }

        return responseManager("utility_bill_details", $pageTitle, 'success', compact('view', 'pageTitle', 'utilityBill'));
    }

    public function pdf($id)
    {
        $pageTitle   = "Utility Bill Receipt";
        $user        = auth()->user();
        $utilityBill = UtilityBill::where('id', $id)->where('user_id', $user->id)->first();
        if (!$utilityBill) {
            $notify = "The utility bill transaction is not found";
            return responseManager('not_fund', $notify);
        }

        $activeTemplateTrue = activeTemplate(true);
        $activeTemplate     = activeTemplate();

        $pdf      = Pdf::loadView($activeTemplate . '.user.utility_bill.pdf', compact('pageTitle', 'utilityBill', 'user', 'activeTemplateTrue', 'activeTemplate'));
        $fileName = "Utility Bill Receipt - " . $utilityBill->trx . ".pdf";
        return $pdf->download($fileName);
    }
}
