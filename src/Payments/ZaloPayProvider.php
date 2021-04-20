<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;

class ZaloPayProvider extends AbstractBasePaymentProvider
{
	public function __construct()
	{
		$this->apiUrl    = config('payment.zalopay_service_url');
		$this->returnUrl = config('payment.zalopay_return_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'zalopay';
	}

	/**
	 * @param $model
	 * @param null $bankCode : zalopayapp, CC, null
	 * @param array $extraDatas
	 * @param array $extraHeaders
	 *
	 * @return mixed|null
	 */
	public function purchase($params, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$token       = $this->getToken();
		$finalParams = [
			'amount'    => $params['amount'],
			'returnUrl' => $this->returnUrl,
			'orderInfo' => $params['note'],
			'txnRef'    => $params['code'],
			'bankCode'  => $bankCode,
		];
		$headers     = [
			'Accept'        => 'application/json',
			'Authorization' => $token,
		];
		$headers     = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;
		$finalParams = is_array($extraDatas) ? array_merge($finalParams, $extraDatas) : $finalParams;

		$requestedAt = date('d-m-Y H:i:s');

		$response    = $this->sendPostRequest($this->serviceUrl . '/purchase', $finalParams, $headers);
		$body        = $response->body();
		$responsedAt = date('d-m-Y H:i:s');
		logToFile('zalopay', 'purchase', $finalParams, $body, [$requestedAt, $responsedAt]);

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
		logToFile('zalopay', 'queryTransaction', $params, $body, [$requestedAt, $responsedAt]);

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
		$response = $this->sendGetRequest($this->serviceUrl . '/check-connection', $params, $headers);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('zalopay', 'checkConnection', $params, $body, [$requestedAt, $responsedAt]);

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
