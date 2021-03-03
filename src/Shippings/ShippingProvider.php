<?php

namespace Cloudteam\Core\Shippings;

class ShippingProvider
{
	public $provider;

	public function __construct($providerName)
	{
		$provider = null;

		if ($providerName === 'GHN') {
			$provider = new GhnProvider();
		}

		$this->provider = $provider;
	}

	public function calculateFee($params, $extraDatas = [], $extraHeaders = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->calculateFee($params, $extraDatas, $extraHeaders);
	}
}
