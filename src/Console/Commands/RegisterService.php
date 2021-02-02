<?php

namespace Cloudteam\Core\Console\Commands;

use Cloudteam\Core\Utils\ConsulClient;
use Illuminate\Console\Command;

class RegisterService extends Command
{
	public function handle()
	{
		if ($this->options()['deregister']) {
			ConsulClient::deregister(config('consul.name'));
			echo "\n";

			return;
		}

		if ($this->options()['show']) {
			echo ConsulClient::lookupService(config('consul.name'));
			echo "\n";
			return;
		}
		ConsulClient::deregister(config('consul.name'));
		$name    = config('consul.name');
		$address = config('consul.address');
		$port    = config('consul.port1');
		$schema  = config('consul.schema');
		ConsulClient::register($name, $address, $port, 10, [], $schema);
		echo "\n";
	}

	protected $signature = 'Service:Register {--deregister} {--show}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Register Service Consul';

	/**
	 * Create a new command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}
}
