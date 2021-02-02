<?php

namespace Cloudteam\Core\Console\Commands;

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
			//$u = new Consul();
			//var_dump($u->getHealthClient()->getHealthyServicesInstances(config('consul.name')));
			echo "\n";

			return;
		} else {
			ConsulClient::deregister(config('consul.name'));
			$name    = config('consul.name');
			$address = config('consul.address');
			$port    = config('consul.port1');
			ConsulClient::register($name, $address, $port);
			echo "\n";
		}
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
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
}
