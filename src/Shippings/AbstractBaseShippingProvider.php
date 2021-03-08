<?php

namespace Cloudteam\Core\Shippings;

use Cloudteam\Core\Traits\Apiable;

abstract class AbstractBaseShippingProvider
{
	use Apiable;

	public $apiUrl;
	public $serviceUrl;
	protected $classChannel;
	public $tokenKeyName = '_shipping_service_token';

	abstract public function calculateFee($params, $extraDatas = [], $extraHeaders = []);

	abstract public function createOrder($params, $extraDatas = [], $extraHeaders = []);

	abstract public function getOrderInfo($orderCode, $extraDatas = [], $extraHeaders = []);
}
