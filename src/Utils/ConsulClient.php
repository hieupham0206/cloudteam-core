<?php

namespace Cloudteam\Core\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConsulClient
{
	public static function register($name, $address, $port, $weight = 10, $tags_arr = [], $schema = 'http')
	{
		$host = config('consul.host');
		$url  = "$schema://$address" . (in_array($port, ['80', '443']) ? '' : ":$port") . "/" . config('consul.root_prefix');

		if (substr($url, -1) == '/') {
			$url = substr($url, 0, -1);
		}

		$tags_arr = array_merge($tags_arr, [
			"name"   => $name,
			"weight" => $weight,
			"url"    => $url,
		]);

		$array['service'] = [
			'id'      => $name . '-' . self::quickRandom(8),
			'name'    => $name,
			'tags'    => [json_encode($tags_arr)],
			'address' => $address,
			'port'    => (int) $port,
			'check'   => [
				'tcp'      => "$address:$port",
				'timeout'  => "5s",
				'interval' => "10s",
			],
		];

		/*  monitor healthcheck
	{
		"check": {
			"id": "vnpay_api",
			"name": "VNPAY Health Check API",
			"http": "http://10.101.118.149:10290/api/check-connection",
			"tls_skip_verify": true,
			"method": "GET",
			"body": "",
			"header": {"Content-Type": ["application/json"]},
			"interval": "30s",
			"timeout": "1s"
		}
	}
		 */
		if (file_exists('healthcheck.json')) {
			$healthcheck               = json_decode(file_get_contents('healthcheck.json'), true);
			$array['service']['check'] = $healthcheck['check'];
		}

		file_put_contents("service.json", json_encode($array, JSON_UNESCAPED_SLASHES));

		if ($host == 'localhost') {
			echo "Register service {$array['service']['id']} \r\n";
			echo exec("consul services register service.json");
		} else {
			$port = config('consul.port');
			$url  = "http://$host:$port/v1/agent/service/register";
			echo "Register service via $url\r\n";
			$response = Http::put($url, $array['service']);
			echo $response->body();
		}
	}

	public static function deregister($name)
	{
		// http://127.0.0.1:8500/v1/agent/service/deregister/JinhyeVnpayPaymnentGateway-1qR3f2hT
		if ( ! file_exists('service.json')) {
			echo "Service is not registered \r\n";

			return;
		}
		$host = config('consul.host');
		if ($host == 'localhost') {
			echo "consul services deregister -id $name\n";
			echo exec("consul services deregister service.json");
		} else {
			$port        = config('consul.port');
			$services    = json_decode(file_get_contents('service.json'), true);
			$serviceName = $services['service']['id'];
			$response    = Http::put("http://$host:$port/v1/agent/service/deregister/$serviceName", []);
			echo $response->body();
		}
	}

	public static function lookupService($name)
	{

		$findConsul = explode('consul@', $name);
		if (count($findConsul) > 1) {
			$name = $findConsul[1];
		} else {
			return $name;
		}

		$url = 'http://' . config('consul.host') . ':' . config('consul.port') . '/v1/health/service/' . $name . '?passing=true';

		$services = json_decode(self::curl_get($url), true);
		//var_dump($services[0]['Service']);die;
		if (empty($services)) {
			Log::error("Can not connect to Consul. Url=$url. Service Name: $name");
			Log::channel('slack')->error(config('app.name') . ' - ' . config('app.url') . ': Can not connect to Consul. Service Name: ' . $name);

			return false;
		}
		if (count($services) == 0) {
			Log::error("Can not found service $name. Url=$url");
			Log::channel('slack')->error(config('app.name') . ' - ' . config('app.url') . ": Can not found service $name. Url=$url");

			return false;
		}
		// randomize
		$service = $services[rand(0, count($services) - 1)];
		$tags    = json_decode($service['Service']['Tags'][0], true);
		if (empty($tags['url'])) {
			$urlService = 'http://' . $service['Service']['Address'] . ':' . $service['Service']['Port'] . '/' . config('consul.root_prefix');
		} else {
			$urlService = $tags['url'];
		}

		if (substr($urlService, -1) == '/') {
			$urlService = substr($urlService, 0, -1);
		}

		return $urlService;

	}

	/**
	 * @param $url
	 *
	 * @return bool|string
	 */
	public static function curl_get($url)
	{
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $url,
			CURLOPT_HTTPHEADER     => ['Content-type: application/json'],
			CURLOPT_SSL_VERIFYPEER => false,
		]);

		$resp = curl_exec($curl);
		//var_dump($resp);
		curl_close($curl);

		return $resp;
	}

	public static function quickRandom($length = 16)
	{
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
	}

}
