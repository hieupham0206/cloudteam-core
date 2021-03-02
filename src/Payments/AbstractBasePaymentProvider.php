<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Traits\Apiable;
use Illuminate\Support\Facades\Http;

abstract class AbstractBasePaymentProvider
{
	use Apiable;

	public $apiUrl;
	public $serviceUrl;
	public $returnUrl;
	public $tokenKeyName = '_payment_service_token';
	protected $verifySignatureEndpoint = '/verify-signature';
	protected $classChannel;

	abstract public function purchase($model, $bankCode = null, $extraDatas = [], $extraHeaders = []);

	abstract public function queryTransaction($params = [], $extraHeaders = []);

	abstract public function refund();

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
