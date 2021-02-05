<?php

return [
	'onepay_service_url' => env('ONEPAY_SERVICE_URL', 'http://127.0.0.1:8005/api'),
	'onepay_return_url'  => env('ONEPAY_RETURN_URL', 'http://127.0.0.1:8001/onepay-return-url'),

	'vnpay_service_url' => env('VNPAY_SERVICE_URL', 'http://127.0.0.1:8006/api'),
	'vnpay_return_url'  => env('VNPAY_RETURN_URL', 'http://127.0.0.1:8001/vnpay-return-url'),

	'payoo_service_url' => env('PAYOO_SERVICE_URL', 'http://127.0.0.1:8007/api'),
	'payoo_return_url'  => env('PAYOO_RETURN_URL', 'http://127.0.0.1:8001/payoo-return-url'),

	'zalopay_service_url' => env('ZALOPAY_SERVICE_URL', 'http://127.0.0.1:8008/api'),
	'zalopay_return_url'  => env('ZALOPAY_RETURN_URL', 'http://127.0.0.1:8001/zalopay-return-url'),
];