<?php

namespace Cloudteam\Core\Payments;

use App\Models\Order;

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
		return $this->provider->purchase($model);
	}

	public function queryTransaction()
	{
		return $this->provider->queryTransaction();
	}

	public function refund()
	{

	}
}
