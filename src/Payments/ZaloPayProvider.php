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
	public function purchase($model, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$token   = $this->getToken();
		$params  = [
			'amount'    => $model->total_payment,
			'returnUrl' => $this->returnUrl,
			'orderInfo' => 'Thanh toan: ' . $model->total_payment,
			'txnRef'    => $model->code,
			'bankCode'  => $bankCode,
		];
		$headers = [
			'Accept'        => 'application/json',
			'Authorization' => $token,
		];
		$headers = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;
		$params  = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;

		$requestedAt = date('d-m-Y H:i:s');

		$response    = $this->sendPostRequest($this->serviceUrl . '/purchase', $params, $headers);
		$body        = $response->body();
		$responsedAt = date('d-m-Y H:i:s');
		logToFile('zalopay', 'purchase', $params, $body, [$requestedAt, $responsedAt]);

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

	public function refund()
	{
		// TODO: Implement refund() method.
	}
}
