<?php

namespace Cloudteam\Core\Utils;

class ConsulClient
{
	public static function register($name, $address, $port, $weight = 10, $tags_arr = [])
	{
		$tags_arr         = array_merge($tags_arr, [
			"name"   => $name,
			"weight" => $weight,
			"url"    => "https://$address/" . config('consul.root_prefix'),
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
		file_put_contents("service.json", json_encode($array, JSON_UNESCAPED_SLASHES));
		echo exec("consul services register service.json");
	}

	public static function deregister($name)
	{
		echo "consul services deregister -id $name\n";
		echo exec("consul services deregister service.json");
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

		$services = json_decode(self::curlGet($url), true);
		//var_dump($services[0]['Service']);die;
		if (empty($services)) {
			return false;
		}
		$service = $services[random_int(0, count($services) - 1)];
		$tags    = json_encode($service['Service']['Tags']);
		if (empty($tags['url'])) {
			return 'http://' . $service['Service']['Address'] . ':' . $service['Service']['Port'] . '/' . config('consul.root_prefix');
		}

		return $tags['url'];
	}

	public static function curlGet($url)
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
