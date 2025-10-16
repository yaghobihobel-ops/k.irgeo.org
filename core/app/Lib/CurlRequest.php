<?php

namespace App\Lib;

use Illuminate\Support\Facades\Log;

class CurlRequest
{
    /**
     * GET request using curl
     *
     * @return mixed
     */
    public static function curlContent($url, $header = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        self::applySslVerification($ch);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            Log::error(sprintf('Curl GET request to [%s] failed: %s', $url, $error));
        }

        curl_close($ch);

        return $result;
    }

    /**
     * POST request using curl
     *
     * @return mixed
     */
    public static function curlPostContent($url, $postData = null, $header = null)
    {
        $params = is_array($postData) ? http_build_query($postData) : $postData;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        self::applySslVerification($ch);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            Log::error(sprintf('Curl POST request to [%s] failed: %s', $url, $error));
        }

        curl_close($ch);

        return $result;
    }

    protected static function applySslVerification($curlHandle): void
    {
        $verify = (bool) config('security.verify_ssl', true);

        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, $verify ? 2 : 0);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, $verify);
    }
}
