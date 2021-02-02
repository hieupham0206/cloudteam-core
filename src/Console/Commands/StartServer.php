<?php

namespace Cloudteam\Core\Console\Commands;

use Illuminate\Console\Command;

class StartServer extends Command
{
	public function handle()
	{

		$port = config('consul.port1');
		$name = config('consul.name');
		if ($this->options()['start']) {
			echo "Starting Service ... Name: $name. Port: $port\n";
			$this->call('Service:Register');
			echo "\n";
			//$this->call('Service:Register', ['--show']);
			echo "\n";
			exec("php -S 0.0.0.0:$port -t public");
			echo "Done\n";
		}
		if ($this->options()['stop']) {
			echo "Stopping Service ...\n";

			echo "Done\n";
		} else {
			echo "Usage: php artisan Service --start\n";
		}
	}

	protected $signature = 'Service {--start} {--stop}';

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
