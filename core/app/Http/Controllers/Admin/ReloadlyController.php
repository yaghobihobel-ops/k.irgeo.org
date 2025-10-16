<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApiConfiguration;

class ReloadlyController extends Controller
{

    public function form()
    {
        $pageTitle = 'Reloadly API Configuration';
        $apiConfig = ApiConfiguration::where('provider', 'reloadly')->firstOrFail();
        return view('admin.api_config.reloadly', compact('pageTitle', 'apiConfig'));
    }

    public function saveCredentials(Request $request)
    {
        $request->validate([
            'credentials.client_id'     => 'required|string',
            'credentials.client_secret' => 'required|string',
            'test_mode'                 => 'nullable|in:on'
        ], [
            'credentials.client_id.required'     => 'The client id field is required',
            'credentials.client_secret.required' => 'The client secret field is required'
        ]);

        $apiConfig = ApiConfiguration::where('provider', 'reloadly')->firstOrFail();

        $apiConfig->credentials  = $request->credentials;
        $apiConfig->access_token = null;
        $apiConfig->test_mode    = $request->test_mode ? Status::ENABLE : Status::DISABLE;
        $apiConfig->save();

        $notify[] = ['success', 'API credentials updated successfully'];
        return back()->withNotify($notify);
    }
}
