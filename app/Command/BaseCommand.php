<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BaseCommand extends Command
{
	protected $log;

	public function __construct($name = null)
	{
		parent::__construct($name);

		// 日志
		$this->logger = new Logger('cheat_cron');
		$this->logger->pushHandler(new StreamHandler(dirname(dirname(dirname(__FILE__))) . '/log/cron.log', 'debug'));
	} 
}