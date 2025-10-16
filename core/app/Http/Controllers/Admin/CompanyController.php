<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\BillCategory;
use App\Models\Form;
use App\Models\Company;
use App\Models\TransactionCharge;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function all()
    {
        $pageTitle  = 'Company List';
        $baseQuery  = Company::searchable(['name'])->with('form')->orderBy('id', getOrderBy());
        $categories = BillCategory::active()->get();
        if (request()->export) {
            return exportData($baseQuery, request()->export, "Company");
        }

        $companies = $baseQuery->paginate(getPaginate());
        $charge = TransactionCharge::where('slug', "utility_charge")->first();

        return view('admin.company.all', compact('pageTitle', 'companies', 'categories', 'charge'));
    }

    public function save(Request $request, $id = 0)
    {
        $imageValidation = $id ? 'nullable' : 'required';

        $request->validate([
            'name'           => 'required|max:255|unique:companies,name,'.$id,
            'fixed_charge'   => 'nullable|numeric|min:0',
            'category_id'    => 'required|numeric|exists:bill_categories,id',
            'percent_charge' => 'nullable|numeric|min:0',
            'image'          => [$imageValidation, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if ($id) {
            $utility  = Company::findOrFail($id);
            $notify[] = ['success', 'Utility bill setting updated successfully'];
        } else {
            $utility  = new Company();
            $notify[] = ['success', 'Utility bill setting added successfully'];
        }

        if ($request->hasFile('image')) {
            try {
                $old         = $utility->image;
                $utility->image = fileUploader($request->image, getFilePath('utility'), getFileSize('utility'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $utility->name           = $request->name;
        $utility->fixed_charge   = $request->fixed_charge;
        $utility->percent_charge = $request->percent_charge;
        $utility->category_id    = $request->category_id;
        $utility->save();

        return back()->withNotify($notify);
    }

    public function configure($id)
    {
        $utility   = Company::findOrFail($id);
        $pageTitle = 'Configure Utility Bill - ' . $utility->name;
        $form      = Form::where('act', 'utility_' . $utility->id)->first();
        return view('admin.company.form', compact('pageTitle', 'utility', 'form'));
    }

    public function saveConfigure(Request $request, $id)
    {
        $setup               = Company::findOrFail($id);
        $formProcessor       = new FormProcessor();
        $generatorValidation = $formProcessor->generatorValidation();
        $request->validate($generatorValidation['rules'], $generatorValidation['messages']);
        $exist = Form::where('act', 'utility_' . $id)->first();
        $generatedForm = $formProcessor->generate('utility_' . $id, $exist, 'act');

        $setup->form_id = @$generatedForm->id ?? 0;
        $setup->save();

        $notify[] = ['success', 'Utility bill setting has been successfully configured'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Company::changeStatus($id);
    }
}