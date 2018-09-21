<?php
/**
 * 实际下单24小时后将订单标记为已完成
 * 原因：更新订单进度的计划任务已经关掉，订单会一直停留在处理中的状态
 */

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Middleware\Cheat as C;
use App\Middleware\Credis;
use App\Model\Order;
use App\Model\Cheat;
use App\Model\Card;

class OrderCompleteCommand extends BaseCommand
{
	// 订单状态
	const ORDER_STATUS_DEALING = 1;				// 进行中
	const ORDER_STATUS_COMPLETE = 2;			// 完成
	const ORDER_STATUS_FAILED = 3;				// 失败

	const DEALING_TIME = 86400;					// 多少秒后标记订单已完成

	protected function configure()
	{
		$this->setName('app:order-mark-complete')
			 ->setDescription('标记订单已完成')
			 ->setHelp('实际下单24小时后将订单标记为已完成');
	}

	/**
	 * 执行
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$current_time = time();
		Order::exec('UPDATE `orders` SET `status` = ' . self::ORDER_STATUS_COMPLETE . ', `updated_at` = "' . date('Y-m-d H:i:s', $current_time) . '" WHERE `status` = ' . self::ORDER_STATUS_DEALING . ' AND `order_id` != "" AND `updated_at` < "' . date('Y-m-d H:i:s', $current_time - self::DEALING_TIME) . '"');
	}
}