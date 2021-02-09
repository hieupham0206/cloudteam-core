<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;
use Illuminate\Support\Facades\Http;

class OnepayProvider extends AbstractBaseProvider
{
	private $type;

	public function __construct($type = 'domestic')
	{
		$this->apiUrl    = config('payment.onepay_service_url');
		$this->returnUrl = config('payment.onepay_return_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'onepay';

		$this->type                    = $type;
		$this->verifySignatureEndpoint .= "/$type";
	}

	public function purchase($model, $bankCode = null, $extraDatas = [])
	{
		if (! $this->serviceUrl) {
			return null;
		}

		$params      = [
			'amount'    => $model->total_payment,
			'returnUrl' => $this->returnUrl,
			'againLink' => $this->returnUrl,
			'orderInfo' => 'Thanh toan: ' . $model->total_payment,
			'txnRef'    => $model->code,
		];
		$params      = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;

		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();

		$response = Http::withHeaders([
			'Accept'        => 'application/json',
			'Authorization' => $token,
		])->post($this->serviceUrl . "/purchase/{$this->type}", $params);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('onepay', 'purchase', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['redirect_url'];
		}

		return null;
	}

	public function queryTransaction($params = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();

		$response = Http::withHeaders([
			'Accept'        => 'application/json',
			'Authorization' => $token,
		])->get($this->serviceUrl . '/query-transaction', $params);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('onepay', 'queryTransaction', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['providerDatas'];
		}

		return null;
	}

	public function refund()
	{
		// TODO: Implement refund() method.
	}
}
