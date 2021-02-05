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

	public function purchase($model)
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->purchase($model);
	}

	public function queryTransaction()
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->queryTransaction();
	}

	public function refund()
	{
		if (! $this->provider) {
			return null;
		}
	}
}
