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

	public function purchase($model, $bankCode = null)
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->purchase($model, $bankCode);
	}

	public function queryTransaction($params = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->queryTransaction($params);
	}

	public function refund()
	{
		if (! $this->provider) {
			return null;
		}
	}
}
