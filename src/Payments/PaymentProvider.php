<?php

namespace Cloudteam\Core\Payments;

class PaymentProvider
{
	public $provider;

	public function __construct($providerName)
	{
		$provider = null;

		if ($providerName === 'VNPay') {
			$provider = new VNPayProvider();
		} elseif ($providerName === 'OnePay') {
			$provider = new OnepayProvider('domestic');
		} elseif ($providerName === 'Payoo') {
			$provider = new PayooProvider();
		} elseif ($providerName === 'ZaloPay') {
			$provider = new ZaloPayProvider();
		}

		$this->provider = $provider;
	}

	public function purchase($params, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->purchase($params, $bankCode, $extraDatas, $extraHeaders);
	}

	public function queryTransaction($params = [], $extraHeaders = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->queryTransaction($params, $extraHeaders);
	}

	public function checkConnection($params = [], $extraHeaders = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->checkConnection($params, $extraHeaders);
	}

	public function refund()
	{
		if (! $this->provider) {
			return null;
		}
	}
}
