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

	public function purchase($model, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->purchase($model, $bankCode, $extraDatas, $extraHeaders);
	}

	public function queryTransaction($params = [], $extraHeaders = [])
	{
		if (! $this->provider) {
			return null;
		}

		return $this->provider->queryTransaction($params, $extraHeaders);
	}

	public function refund()
	{
		if (! $this->provider) {
			return null;
		}
	}
}
