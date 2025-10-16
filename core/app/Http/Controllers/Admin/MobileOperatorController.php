<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileOperator;
use App\Models\TransactionCharge;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class MobileOperatorController extends Controller
{
    public function all()
    {
        $pageTitle = 'All Mobile Operators';
        $baseQuery = MobileOperator::searchable(['name'])->orderBy('id', getOrderBy());

        if (request()->export) {
            return exportData($baseQuery, request()->export, "MobileOperator");
        }

        $operators = $baseQuery->paginate(getPaginate());
        $charge    = TransactionCharge::where('slug', "mobile_recharge")->first();
        return view('admin.mobile_operator.all', compact('pageTitle', 'operators','charge'));
    }

    public function save(Request $request, $id = 0)
    {
        $imageValidation = $id ? 'nullable' : 'required';

        $request->validate([
            'name'           => 'required|max:255',
            'fixed_charge'   => 'nullable|numeric|min:0',
            'percent_charge' => 'nullable|numeric|min:0',
            'image'          => [$imageValidation, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if ($id) {
            $operator  = MobileOperator::findOrFail($id);
            $notify[] = ['success', 'Mobile operator updated successfully'];
        } else {
            $operator  = new MobileOperator();
            $notify[] = ['success', 'Mobile operator added successfully'];
        }

        if ($request->hasFile('image')) {
            try {
                $old = $operator->image;
                $operator->image = fileUploader($request->image, getFilePath('mobile_operator'), getFileSize('mobile_operator'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }
        $operator->name           = $request->name;
        $operator->fixed_charge   = $request->fixed_charge;
        $operator->percent_charge = $request->percent_charge;
        $operator->save();

        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return MobileOperator::changeStatus($id);
    }
}
