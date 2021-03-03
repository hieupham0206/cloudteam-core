<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;

class OnepayProvider extends AbstractBasePaymentProvider
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

	public function purchase($params, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$token       = $this->getToken();
		$finalParams = [
			'amount'    => $params['amount'],
			'returnUrl' => $this->returnUrl,
			'againLink' => $this->returnUrl,
			'orderInfo' => 'Thanh toan: ' . $params['amount'],
			'txnRef'    => $params['code'],
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
		logToFile('onepay', 'purchase', $finalParams, $body, [$requestedAt, $responsedAt]);

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
