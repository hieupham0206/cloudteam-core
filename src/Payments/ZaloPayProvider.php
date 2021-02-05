<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;
use Illuminate\Support\Facades\Http;

class ZaloPayProvider extends AbstractBaseProvider
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
	 *
	 * @return mixed|null
	 */
	public function purchase($model, $bankCode = null)
	{
		if (! $this->serviceUrl) {
			return null;
		}

		$params = [
			'amount'    => $model->total_payment,
			'returnUrl' => $this->returnUrl,
			'orderInfo' => 'Thanh toan: ' . $model->total_payment,
			'txnRef'    => $model->code,
			'bankCode'  => $bankCode,
		];

		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();
		$response    = Http::withHeaders([
			'Accept'        => 'application/json',
			'Authorization' => $token,
		])->post($this->serviceUrl . '/purchase', $params);
		$body        = $response->body();
		$responsedAt = date('d-m-Y H:i:s');
		logToFile('zalopay', 'purchase', $params, $body, [$requestedAt, $responsedAt]);

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
