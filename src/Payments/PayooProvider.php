<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;

class PayooProvider extends AbstractBasePaymentProvider
{
	public function __construct()
	{
		$this->apiUrl    = config('payment.payoo_service_url');
		$this->returnUrl = config('payment.payoo_return_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'payoo';
	}

	public function purchase($params, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$token   = $this->getToken();
		$finalParams  = [
			'amount'    => $params['amount'],
			'orderInfo' => 'Thanh toan: ' . $params['amount'],
			'returnUrl' => $this->returnUrl,
			'txnRef'    => $params['code'],
			'customer'  => [
				'name'    => $params['customer_name'] ?? '',
				'phone'   => $params['customer_phone'] ?? '',
				'address' => $params['customer_address'] ?? '',
				'city'    => $params['customer_city'] ?? '',
				'email'   => $params['customer_email'] ?? '',
			],
		];
		$headers = [
			'Accept'        => 'application/json',
			'Authorization' => $token,
		];
		$headers = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;
		$finalParams  = is_array($extraDatas) ? array_merge($finalParams, $extraDatas) : $finalParams;

		$requestedAt = date('d-m-Y H:i:s');

		$response = $this->sendPostRequest($this->serviceUrl . '/purchase', $finalParams, $headers);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('payoo', 'purchase', $finalParams, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['redirect_url'];
		}

		return null;
	}

	public function queryTransaction($params = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();

		$headers  = [
			'Accept'        => 'application/json',
			'Authorization' => $token,
		];
		$headers  = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;
		$response = $this->sendGetRequest($this->serviceUrl . '/query-transaction', $params, $headers);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('payoo', 'queryTransaction', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['providerDatas'];
		}

		return null;
	}

	public function checkConnection($params = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();

		$headers  = [
			'Accept'        => 'application/json',
			'Authorization' => $token,
		];
		$headers  = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;
		$response = $this->sendGetRequest($this->serviceUrl . '/check-transaction', $params, $headers);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('payoo', 'checkConnection', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['message'];
		}

		return null;
	}

	public function refund()
	{
		// TODO: Implement refund() method.
	}
}
