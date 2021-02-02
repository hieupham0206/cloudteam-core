<?php

namespace Cloudteam\Core\Payments;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class AbstractBaseProvider
{
	public $apiUrl;

	public $serviceUrl;

	public $returnUrl;

	protected $verifySignatureEndpoint = '/verify-signature';

	protected $classChannel;

	abstract public function purchase($model, $bankCode = null);

	abstract public function queryTransaction();

	abstract public function refund();

	public function getToken(): string
	{
		$cacheKey   = $this->classChannel . '_payment_service_token';
		$tokenValue = Cache::get($cacheKey);

		if ( ! $tokenValue) {
			$tokenResponse = Http::withHeaders([
				'Accept' => 'application/json',
			])->post($this->serviceUrl . '/auth/signin', [
				'username' => 'admin',
				'password' => 'Cloudteam@123',
			]);

			logToFile('daily', 'signin', ['url' => $this->serviceUrl . '/auth/sigin'], $tokenResponse->body());

			if ($tokenResponse->ok()) {
				$tokenResponse = json_decode($tokenResponse, true);
				$tokenValue    = $tokenResponse['access_token'];

				Cache::put($cacheKey, $tokenValue);
			}
		}

		return "Bearer $tokenValue";
	}

	public function verifySignature(string $query): bool
	{
		$params = [
			'query' => $query,
		];

		$requestedAt = date('d-m-Y H:i:s');
		$response    = Http::post($this->serviceUrl . $this->verifySignatureEndpoint, $params);
		$body        = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile($this->classChannel, 'verifySignature', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			return true;
		}

		return false;
	}
}
