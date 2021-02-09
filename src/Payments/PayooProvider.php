<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;
use Illuminate\Support\Facades\Http;

class PayooProvider extends AbstractBaseProvider
{
	public function __construct()
	{
		$this->apiUrl    = config('payment.payoo_service_url');
		$this->returnUrl = config('payment.payoo_return_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'payoo';
	}

	public function purchase($model, $bankCode = null, $extraDatas = [])
	{
		if (! $this->serviceUrl) {
			return null;
		}

		$params = [
			'amount'    => $model->total_payment,
			'orderInfo' => 'Thanh toan: ' . $model->total_payment,
			'returnUrl' => $this->returnUrl,
			'txnRef'    => $model->code,
			'customer'  => [
				'name'    => $model->name,
				'phone'   => $model->phone,
				'address' => $model->address,
				'city'    => $model->city->name,
				'email'   => $model->email,
			],
		];
		$params      = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;

		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();

		$response = Http::withHeaders([
			'Accept'        => 'application/json',
			'Authorization' => $token,
		])->post($this->serviceUrl . '/purchase', $params);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('payoo', 'purchase', $params, $body, [$requestedAt, $responsedAt]);

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
		logToFile('payoo', 'queryTransaction', $params, $body, [$requestedAt, $responsedAt]);

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
