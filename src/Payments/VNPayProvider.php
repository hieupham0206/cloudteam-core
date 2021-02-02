<?php

namespace Cloudteam\Core\Payments;

use Cloudteam\Core\Utils\ConsulClient;
use Illuminate\Support\Facades\Http;

class VNPayProvider extends AbstractBaseProvider
{
	public function __construct()
	{
		$this->apiUrl    = config('payment.vnpay_service_url');
		$this->returnUrl = config('payment.vnpay_return_url');

		$this->serviceUrl   = ConsulClient::lookupService($this->apiUrl);
		$this->classChannel = 'vnpay';
	}

	public function purchase($model, $bankCode = null)
	{
		if (! $this->serviceUrl) {
			return null;
		}

		$params      = [
			'amount'    => $model->total_payment,
			'returnUrl' => $this->returnUrl,
			'orderInfo' => 'Thanh toan: ' . $model->total_payment,
			'txnRef'    => $model->code,
			'bankCode'  => $bankCode,
		];
		$requestedAt = date('d-m-Y H:i:s');
		$token       = $this->getToken();

		$response = Http::withHeaders([
			'Accept'        => 'application/json',
			'Authorization' => $token,
		])->post($this->serviceUrl . '/purchase', $params);
		$body     = $response->body();

		$responsedAt = date('d-m-Y H:i:s');
		logToFile('vnpay', 'purchase', $params, $body, [$requestedAt, $responsedAt]);

		if ($response->ok()) {
			$datas = json_decode($body, true);

			return $datas['redirect_url'];
		}

		return null;
	}

	public function queryTransaction()
	{
		// TODO: Implement queryTransaction() method.
	}

	public function refund()
	{
		// TODO: Implement refund() method.
	}
}
