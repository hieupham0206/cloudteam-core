<?php

namespace Cloudteam\Core\Payments;

use Illuminate\Support\Facades\Log;

class PaymentProvider
{
	public $provider;
	public $providerName;

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

		$this->provider     = $provider;
		$this->providerName = $providerName;
	}

	public function purchase($params, $bankCode = null, $extraDatas = [], $extraHeaders = [])
	{
		if ( ! $this->provider) {
			Log::error("Không tìm thấy class cho {$this->providerName}");

			return null;
		}

		return $this->provider->purchase($params, $bankCode, $extraDatas, $extraHeaders);
	}

	public function queryTransaction($params = [], $extraHeaders = [])
	{
		if ( ! $this->provider) {
			Log::error("Không tìm thấy class cho {$this->providerName}");

			return null;
		}

		return $this->provider->queryTransaction($params, $extraHeaders);
	}

	public function checkConnection($params = [], $extraHeaders = [])
	{
		if ( ! $this->provider) {
			return null;
		}

		return $this->provider->checkConnection($params, $extraHeaders);
	}

	public function refund()
	{
		if ( ! $this->provider) {
			Log::error("Không tìm thấy class cho {$this->providerName}");

			return null;
		}
	}
}
