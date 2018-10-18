<?php
// 更新订单状态
// 计划任务执行
namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Model\Order;
use App\Model\Cheat;
use App\Middleware\Cheat as C;

class OrderProgressCommand extends BaseCommand
{
	// 订单状态
	CONST ORDER_STATUS_DEALING = 1;				// 进行中
	CONST ORDER_STATUS_COMPLETE = 2;			// 完成
	CONST ORDER_STATUS_FAILED = 3;				// 失败

	// curl请求订单状态状态码
	CONST ORDER_COMPLETE_STATE = 3;				// 完成

	protected function configure()
	{
		$this->setName('app:update-order-progress')
			 ->setDescription('更新订单状态')
			 ->setHelp('计划任务获取刷赞订单的处理进度');
	}

	/**
	 * 每次执行100个
	 */
	/*protected function execute(InputInterface $input, OutputInterface $output)
	{
		set_time_limit(300);				// 五分钟超时时间, 如果不设置超时时间可能会出现极端情况进程假死一直占用服务器资源

		$handle_count = 200;				// 每次处理数量

		$orders = Order::findAll('orders', ' WHERE `status` = ? ORDER BY id ASC LIMIT ? ', [ self::ORDER_STATUS_DEALING, $handle_count ]);

		if (!empty($orders) && foreachAble($orders)) {
			$this->logger->AddDebug('Start updating order step 1 , total number : ' . count($orders), []);
			foreach ($orders as $k => $order) {
				$page = rand(1, 5);
				$cheat = Cheat::findOne('cheats', ' id = ? ', [ $order->cid ]);
				if (empty($cheat)) {
					$this->logger->AddError(['order progress update error - no cheat', (array)$order]);
					continue;
				}

				$c = new C($order->identify);
				$c->setLoginUrl($cheat->login_url);
				$c->setProgressUrl($cheat->progress_url);
				$ret = $c->orderProgress($page, $order->qq);
				if ($ret->error) {
					$this->logger->AddError('order progress update error - curl error', [ $ret->error, $ret->errorMessage, $ret->curlErrorMessage, $order->id ]);
				} else {
					$response = is_object($ret->response) ? $ret->response : json_decode($ret->response);
					$data = $response->exhibitDatas;
					$this->logger->AddDebug('order progress update step 2', [ $order->id, $ret->response, $response ]);
					if (!empty($data)) {
						$this->logger->AddDebug('order progress update step 3', [ $data ]);
						if (foreachAble($data)) {
							foreach ($data as $d) {
								if ($d->id == $order->order_id) {
									$order->real_amount = $d->now_num - $d->start_num;
									if ($order->real_amount >= $order->amount || $d->order_state == self::ORDER_COMPLETE_STATE) {
										$order->status = self::ORDER_STATUS_COMPLETE;
										$order->updated_at = new \Datetime;
									}

									if (!Order::store($order)) {
										$this->logger->AddError('order progress update error - store order error', [ $order ]);
									}

									$this->logger->AddDebug('order progress update step 4', [ $order, $d ]);
								}
							}
						}
					} else {
						$this->logger->AddDebug('order progress update step 5', []);
					}
				}
			}
		} else {
			$this->logger->AddDebug('order progress update - no orders', []);
		}
	}*/

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(300);
        $startDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        $completeStatus = self::ORDER_STATUS_COMPLETE;
        $sql = "UPDATE `orders` SET `status`={$completeStatus} WHERE `status`=1 AND `created_at` <= '{$startDate}'";
        $this->logger->AddDebug('order progress update ：', [ $sql ]);
        $res = Order::exec($sql);
        $this->logger->AddDebug('[complete] order progress update ：', [ (array)$res ]);
        if (!$res) {
            $this->logger->AddDebug('[error] order progress update ：', [ $sql ]);
        }
    }
}