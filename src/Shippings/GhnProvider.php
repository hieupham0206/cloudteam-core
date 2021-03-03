<?php

namespace Cloudteam\Core\Shippings;

use Cloudteam\Core\Utils\ConsulClient;

class GhnProvider extends AbstractBaseShippingProvider
{
	public function __construct()
	{
		$this->apiUrl    = config('shipping.ghn_service_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'ghn';
	}

	public function calculateFee($params, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->serviceUrl) {
			return null;
		}

		$token   = $this->getToken();
		$headers = [
			'Accept'        => 'application/json',
			'Authorization' => $token,
		];
		$params  = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;
		$headers = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;

		$requestedAt = date('d-m-Y H:i:s');
		$response    = $this->sendPostRequest($this->serviceUrl . '/get-fee', $params, $headers);
		$body        = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('ghn', 'calculateFee', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['total'];
		}

		return null;
	}
}
