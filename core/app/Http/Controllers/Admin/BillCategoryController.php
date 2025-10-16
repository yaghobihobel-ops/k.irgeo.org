<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillCategory;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class BillCategoryController extends Controller
{
    public function all()
    {
        $pageTitle  = 'Bill Category List';
        $baseQuery  = BillCategory::searchable(['name'])->orderBy('id', getOrderBy());

        if (request()->export) {
            return exportData($baseQuery, request()->export, "BillCategory");
        }

        $utilityBills = $baseQuery->paginate(getPaginate());

        return view('admin.company.category', compact('pageTitle', 'utilityBills'));
    }

    public function save(Request $request, $id = 0)
    {
        $imageValidation = $id ? 'nullable' : 'required';

        $request->validate([
            'name'           => 'required|max:255',
            'image'          => [$imageValidation, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if ($id) {
            $utility  = BillCategory::findOrFail($id);
            $notify[] = ['success', 'Utility bill setting updated successfully'];
        } else {
            $utility  = new BillCategory();
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

        $utility->name  = $request->name;
        $utility->save();

        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return BillCategory::changeStatus($id);
    }

}
