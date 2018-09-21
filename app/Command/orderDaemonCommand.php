<?php
/**
 * 2018-01-16 16:00:00
 * 下单进程守护进程，每5分钟检测一次如果脚本没有在运行就重启
 */
// namespace App\Command;

// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Output\OutputInterface;

// class orderDaemonCommand extends BaseCommand
// {
// 	const SCRIPT_COMMAND_CHEAT = '/www/cheats/bin/console';
// 	const SCRIPT_COMMAND_BEAUTIFY = '/www/beautify/bin/console';
// 	const SCRIPT_NAME = 'app:order-create';

// 	protected function configure()
// 	{
// 		$this->setName('app:order-daemon')
// 			 ->setDescription('下单进程守护进程')
// 			 ->setHelp('每5分钟检测一次如果脚本没有在运行就重启');
// 	}

// 	protected function execute(InputInterface $input, OutputInterface $output)
// 	{
// 		$handle = popen('ps -ef|grep ' . self::SCRIPT_NAME, 'r');
// 		$content = '';
// 		while($line = fgets($handle, 4096)) {
// 			$content = $content . $line;
// 		}
// 		pclose($handle);

// 		$ret = preg_match('#' . self::SCRIPT_COMMAND_CHEAT . '#', $content);
// 		if (empty($ret)) {
// 			$handle = popen('nohup php ' . self::SCRIPT_COMMAND_CHEAT . ' ' . self::SCRIPT_NAME . ' &', 'w');
// 			pclose($handle);
// 		}

// 		$ret = preg_match('#' . self::SCRIPT_COMMAND_BEAUTIFY . '#', $content);
// 		if (empty($ret)) {
// 			$handle = popen('nohup php ' . self::SCRIPT_COMMAND_BEAUTIFY . ' ' . self::SCRIPT_NAME . ' &', 'w');
// 			pclose($handle);
// 		}
// 	}
// }