<?php
/**
 * User: ADMIN
 * Date: 02/03/2021 9:49 SA
 */

namespace Cloudteam\Core\Traits;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

trait Apiable
{
    public function sendPostRequest($link, $params, $headers): Response
    {
        $verifySsl = config('payment.verify_ssl', true);

        if ($verifySsl) {
            return Http::withHeaders($headers)->post($link, $params);
        }

        return Http::withoutVerifying()->withHeaders($headers)->post($link, $params);
    }

    public function sendGetRequest($link, $params, $headers): Response
    {
        $verifySsl = config('payment.verify_ssl', true);

        if ($verifySsl) {
            return Http::withHeaders($headers)->get($link, $params);
        }

        return Http::withoutVerifying()->withHeaders($headers)->get($link, $params);
    }

    public function getToken(): string
    {
        $cacheKey   = $this->classChannel.$this->tokenKeyName;
        $tokenValue = Cache::get($cacheKey);

        if (! $tokenValue) {
            $verifySsl = config('payment.verify_ssl', true);
            if ($verifySsl) {
                $tokenResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                ])->post($this->serviceUrl.'/auth/signin', [
                    'username' => 'admin',
                    'password' => 'Cloudteam@123',
                ]);
            } else {
                $tokenResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                ])->withoutVerifying()->post($this->serviceUrl.'/auth/signin', [
                    'username' => 'admin',
                    'password' => 'Cloudteam@123',
                ]);
            }

            logToFile('daily', 'signin', ['url' => $this->serviceUrl.'/auth/sigin'], $tokenResponse->body());

            if ($tokenResponse->ok()) {
                $tokenResponse = json_decode($tokenResponse, true);
                $tokenValue    = $tokenResponse['access_token'];

                Cache::put($cacheKey, $tokenValue);
            }
        }

        return "Bearer $tokenValue";
    }
}
