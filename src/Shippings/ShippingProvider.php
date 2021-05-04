<?php

namespace Cloudteam\Core\Shippings;

use Illuminate\Support\Facades\Log;

class ShippingProvider
{
	public $provider;
	public $providerName = '';

	public function __construct($providerName)
	{
		$provider = null;

		if ($providerName === 'GHN') {
			$provider = new GhnProvider();
		}

		$this->provider     = $provider;
		$this->providerName = $providerName;
	}

	public function calculateFee($params, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->provider) {
			Log::error("Không tìm thấy class cho {$this->providerName}");

			return null;
		}

		return $this->provider->calculateFee($params, $extraDatas, $extraHeaders);
	}

	public function createOrder($params, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->provider) {
			Log::error("Không tìm thấy class cho {$this->providerName}");

			return null;
		}

		return $this->provider->createOrder($params, $extraDatas, $extraHeaders);
	}

	public function getOrderInfo($params, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->provider) {
			Log::error("Không tìm thấy class cho {$this->providerName}");

			return null;
		}

		return $this->provider->getOrderInfo($params, $extraDatas, $extraHeaders);
	}
}
