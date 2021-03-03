<?php

namespace Cloudteam\Core\Shippings;

use Cloudteam\Core\Traits\Apiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class AbstractBaseShippingProvider
{
	use Apiable;

	public $apiUrl;
	public $serviceUrl;
	protected $classChannel;
	public $tokenKeyName = '_shipping_service_token';

	abstract public function calculateFee($params, $extraDatas = [], $extraHeaders = []);
}
