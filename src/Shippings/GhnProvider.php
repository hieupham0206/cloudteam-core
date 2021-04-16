<?php

namespace Cloudteam\Core\Shippings;

use Cloudteam\Core\Utils\ConsulClient;

class GhnProvider extends AbstractBaseShippingProvider
{
	public function __construct()
	{
		$this->apiUrl = config('shipping.ghn_service_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'ghn';
	}

	public function calculateFee($params, $extraDatas = [], $extraHeaders = []): ?array
	{
		if ( ! $this->serviceUrl) {
			return null;
		}
		$requestedAt = date('d-m-Y H:i:s');

		try {
			$token   = $this->getToken();
			$headers = [
				'Accept'        => 'application/json',
				'Authorization' => $token,
			];
			$params  = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;
			$headers = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;

			$response = $this->sendGetRequest($this->serviceUrl . '/get-fee', $params, $headers);
			$body     = $response->body();

			$responsedAt = date('d-m-Y H:i:s');
			logToFile('ghn', 'calculateFee', $params, $body, [$requestedAt, $responsedAt]);

			$datas = json_decode($body, true);
			if ($response->ok()) {
				return [
					'message' => 'OK',
					'data'    => $datas['data'],
				];
			}

			return [
				'message' => 'Failed',
				'data'    => $datas,
			];
		} catch (\Exception $exception) {
			$responsedAt = date('d-m-Y H:i:s');
			$message     = "{$exception->getMessage()} - {$exception->getFile()} - {$exception->getLine()}";
			logToFile('ghn', 'calculateFee-error', $params, $message, [$requestedAt, $responsedAt]);

			return [
				'message' => 'Failed',
				'data'    => $message,
			];
		}
	}

	public function createOrder($params, $extraDatas = [], $extraHeaders = []): ?array
	{
		if ( ! $this->serviceUrl) {
			return null;
		}
		$requestedAt = date('d-m-Y H:i:s');

		try {
			$token   = $this->getToken();
			$headers = [
				'Accept'        => 'application/json',
				'Authorization' => $token,
			];
			$params  = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;
			$headers = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;

			$response = $this->sendPostRequest($this->serviceUrl . '/create-order', $params, $headers);
			$body     = $response->body();

			$responsedAt = date('d-m-Y H:i:s');
			logToFile('ghn', 'createOrder', $params, $body, [$requestedAt, $responsedAt]);

			$datas = json_decode($body, true);
			if ($response->ok()) {
				return [
					'message' => 'OK',
					'data'    => $datas['data'],
				];
			}

			return [
				'message' => 'Failed',
				'data'    => $datas,
			];
		} catch (\Exception $exception) {
			$responsedAt = date('d-m-Y H:i:s');
			$message     = "{$exception->getMessage()} - {$exception->getFile()} - {$exception->getLine()}";
			logToFile('ghn', 'createOrder-error', $params, $message, [$requestedAt, $responsedAt]);

			return [
				'message' => 'Failed',
				'data'    => $message,
			];
		}
	}

	public function getOrderInfo($params, $extraDatas = [], $extraHeaders = []): ?array
	{
		if ( ! $this->serviceUrl) {
			return null;
		}
		$requestedAt = date('d-m-Y H:i:s');

		try {
			$token   = $this->getToken();
			$headers = [
				'Accept'        => 'application/json',
				'Authorization' => $token,
			];
			$params  = ['orderCode' => $params['orderCode'], 'shopId' => $params['shopId']];
			$params  = is_array($extraDatas) ? array_merge($params, $extraDatas) : $params;
			$headers = is_array($extraHeaders) ? array_merge($headers, $extraHeaders) : $headers;

			$response    = $this->sendGetRequest($this->serviceUrl . '/get-order-info', $params, $headers);
			$body        = $response->body();

			$responsedAt = date('d-m-Y H:i:s');
			logToFile('ghn', 'getOrderInfo', $params, $body, [$requestedAt, $responsedAt]);

			$datas = json_decode($body, true);
			if ($response->ok()) {
				return [
					'message' => 'OK',
					'data'    => $datas['data'],
				];
			}

			return [
				'message' => 'Failed',
				'data'    => $datas,
			];
		} catch (\Exception $exception) {
			$responsedAt = date('d-m-Y H:i:s');
			$message     = "{$exception->getMessage()} - {$exception->getFile()} - {$exception->getLine()}";
			logToFile('ghn', 'getOrderInfo-error', $params, $message, [$requestedAt, $responsedAt]);

			return [
				'message' => 'Failed',
				'data'    => $message,
			];
		}
	}
}
