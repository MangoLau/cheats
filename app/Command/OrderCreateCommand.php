<?php
/**
 * 实际下单操作
 * 每隔5秒执行一次, 执行失败重新入到队列尾部
 */

namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Middleware\Cheat as C;
use App\Middleware\Credis;
use App\Model\Order;
use App\Model\Cheat;
use App\Model\Card;

class OrderCreateCommand extends BaseCommand
{
	const ORDER_QUEUE_KEY = 'order-queue';		// 队列处理的key

	// 订单状态
	const ORDER_STATUS_DEALING = 1;				// 进行中
	const ORDER_STATUS_COMPLETE = 2;			// 完成
	const ORDER_STATUS_FAILED = 3;				// 失败

	protected function configure()
	{
		$this->setName('app:order-create')
			 ->setDescription('异步下单')
			 ->setHelp('实际去网站下单的操作');
	}

	/**
	 * 执行
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// 无限制超时
		set_time_limit(0);

		$redis = Credis::getInstance();
		// 一直执行
		while (true) {
			// 阻塞取值
			$l_order = $redis->blpop(self::ORDER_QUEUE_KEY, 0);
			if (!empty($l_order)) {
				$order_id = $l_order[1];
				$order = Order::findOne('orders', ' id = ? AND status = ? ', [ $order_id, self::ORDER_STATUS_DEALING ]);
				if (!empty($order)) {
					$cheat = Cheat::findOne('cheats', ' id = ? ', [ $order->cid ]);
					if (!empty($cheat)) {
						$c = new C($order->identify);
						$c->setLoginUrl($cheat->login_url);
						$c->setUrl($cheat->url);
	
						// 其他参数
						$extra = [];
						if (!empty($order->ssid)) {
							$extra['ssid'] = $order->ssid;
						}
						if (!empty($order->rzid)) {
							$extra['rzid'] = $order->rzid;
						}
						if (!empty($order->ksid)) {
							$extra['ksid'] = $order->ksid;
						}
						if (!empty($order->zpid)) {
							$extra['zpid'] = $order->zpid;
						}
						if (!empty($order->qmkg_gqid)) {
							$extra['qmkg_gqid'] = $order->qmkg_gqid;
						}
						if (!empty($order->douyin_uid)) {
							$extra['zh'] = $order->douyin_uid;
						}
						if (!empty($order->douyin_zpid)) {
							$extra['zh'] = $order->douyin_zpid;
						}
	
						$ret = $c->handle($order->qq, $order->amount, $extra);
						if ($ret->error) {
							$this->logger->addError('order curl error', [ $ret->error, $ret->errorMessage, $ret->curlErrorMessage, $cheat->login_url, $cheat->url, $order_id ]);

							// 重新入队列到尾部
							$redis->rpush(self::ORDER_QUEUE_KEY, $order_id);

							// 收集错误信息
							if (!empty($ret->errorMessage) && $order->errmsg != $ret->errorMessage) {
								$order->errmsg = $ret->errorMessage;
								Order::store($order);
							}
						} else {
							$response = $ret->response;
	
							if ($response->status == 0) {
								// 异常处理
								$this->logger->addError('order create failed', [ $order_id, $response->info, $extra]);
								// 重新入队列到尾部
								$redis->rpush(self::ORDER_QUEUE_KEY, $order_id);

								// 收集错误信息
								if (!empty($response->info) && $order->errmsg != $response->info) {
									$order->errmsg = $response->info;
									Order::store($order);
								}
							} else {
								// 成功
								$order->order_id = $response->order_id;
								$order->updated_at = new \Datetime;
								if (!Order::store($order)) {
									$this->logger->addError('update order failed', [ $order_id, $response ]);
								}

								// 更新卡密余额
								$card = Card::findOne('cards', ' identify = ? ', [ $order->identify ]);
								if (!empty($card) && isset($response->after_use_cardnum)) {
									$card->remaining = $response->after_use_cardnum;
									Card::store($card);
								}
							}
						}
					} else {
						$this->logger->addError('cheat not exits', [ $order_id ]);

						// 重新入队列到尾部
						$redis->rpush(self::ORDER_QUEUE_KEY, $order_id);
					}
				} else {
					$this->logger->addError('order not exist', [ $order_id ]);
				}
			} else {
				$this->logger->addError('order_id empty');
			}

			// 睡眠5秒
			sleep(5);
		}
	}
}