<?php

return [
	'host'         => env('CONSUL_AGENT_HOST', 'localhost'),
	'port'         => env('CONSUL_AGENT_PORT', '8500'),
	'name'         => env('CONSUL_SERVICE_NAME', 'JinhyeServiceCore'),
	'address'      => env('CONSUL_SERVICE_ADDRESS', 'localhost'),
	'port1'        => env('CONSUL_SERVICE_PORT', 10000),
	'schema'       => env('CONSUL_SERVICE_SCHEMA', 'http'),
	'root_prefix'  => env('CONSUL_SERVICE_PREFIX', ''),
];
