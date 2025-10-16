<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Events\QrCodeLogin;
use App\Lib\CurlRequest;
use App\Models\CronJob;
use App\Models\CronJobLog;
use App\Models\QrCode;
use Carbon\Carbon;
use Exception;

class CronController extends Controller
{

    public function cron()
    {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }

        $crons = $crons->get();

        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds($cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }

        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }

        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }
    }

    public function resetLoginQrCode()
    {
        try {

            foreach (['user', 'agent', 'merchant'] as $guard) {
                $columnName = "for_" . $guard . "_login";
                $qrCode     = QrCode::where($columnName, Status::YES)->first();

                if (!$qrCode) {
                    getQrCodeUrlForLogin($guard, false);
                } else {
                    $diffInMinute = now()->parse($qrCode->created_at)->diffInMinutes(now());

                    if ($diffInMinute > 5) {
                        $qrCode->delete();
                        $qrCode = getQrCodeUrlForLogin($guard, false);
                        event(new QrCodeLogin("$guard-qr_code_reset", [
                            'qr_code'     => $qrCode,
                        ], "qr_code_reset"));
                    }
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
