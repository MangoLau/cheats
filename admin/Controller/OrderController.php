<?php

namespace Admin\Controller;

use Admin\Model\Order;
use Admin\Model\Cheat;
use Admin\Model\User;
use Admin\Middleware\Credis;

class OrderController extends BaseController
{
	const STATUS_DEALING = 1;					// 处理中
	const STATUS_COMPLETE = 2;					// 完成
	const STATUS_FAILED = 3;					// 失败

	const ORDER_QUEUE_KEY = 'order-queue';		// 队列处理的key

	const SCORE_LOG_RETURN_TYPE = 8;			// 积分纪录中退还类型

	/**
	 * 上线列表
	 */
	public function index()
	{
		$this->ajax_api = '/orders';
		$this->search_desc = '请输入qq';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Order::count('orders');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Order::count('orders', 'qq = ?', [ $page_data['search'] ]);

				// 列表
				$orders = Order::getAll('SELECT o.*, ct.title FROM `orders` o, `cheats` ct WHERE o.qq = ? AND o.cid = ct.id ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$orders = Order::getAll('SELECT o.*, ct.title FROM `orders` o, `cheats` ct WHERE o.cid = ct.id ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($orders as $k => $order) {
				$orders[$k]['channel'] = $order['channel'] ?: '无';
				$orders[$k]['platform'] = $order['platform'] == 0 ? 'android' : 'iOS';
				$orders[$k]['status'] = $order['status'] == self::STATUS_DEALING ? '处理中' : ( $order['status'] == self::STATUS_COMPLETE ? '已完成' : '失败' );
			}

			$this->json_encode_output(array('data' => array_values($orders), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '订单列表';

			$this->render('order/list', array('title' => $title));
		}
	}

	/**
	 * 拉拉圈订单
	 */
	public function laquanquan()
	{
		$this->ajax_api = '/orders/laquanquan';

		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();
			$cid = Cheat::getCell(' SELECT id FROM cheats WHERE `remark` = ?', [ __FUNCTION__ ] );

			// 总数
			$recordsTotal = Order::count('orders', 'cid = ?', [ $cid ]);

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Order::count('orders', 'qq = ? AND cid = ?', [ $page_data['search'], $cid ]);

				// 列表
				$orders = Order::getAll('SELECT o.*, ct.title FROM `orders` o, `cheats` ct WHERE o.qq = ? AND o.cid = ct.id AND o.cid = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $cid, $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$orders = Order::getAll('SELECT o.*, ct.title FROM `orders` o, `cheats` ct WHERE o.cid = ct.id AND o.cid = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $cid, $page_data['start'], $page_data['count'] ]);
			}

			foreach ($orders as $k => $order) {
				$orders[$k]['channel'] = $order['channel'] ?: '无';
				$orders[$k]['platform'] = $order['platform'] == 0 ? 'android' : 'iOS';
				$orders[$k]['status'] = $order['status'] == self::STATUS_DEALING ? '处理中' : ( $order['status'] == self::STATUS_COMPLETE ? '已完成' : '失败' );
			}

			$this->json_encode_output(array('data' => array_values($orders), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '拉圈圈订单列表';

			$this->render('order/list', array('title' => $title));
		}
	}

	/**
	 * 正在队列中处理的订单
	 */
	public function dealing()
	{
		$redis = Credis::getInstance();
		$order_ids = $redis->lrange(self::ORDER_QUEUE_KEY, 0, -1);

		$orders = [];
		if (!empty($order_ids)) {
			$orders = Order::find('orders', ' id IN (' . Order::genSlots($order_ids) . ') ORDER BY id DESC', $order_ids);
		}

		$this->render('order/dealing', [ 'title' => '队列正在处理的订单', 'orders' => $orders ]);
	}

	/**
	 * 将队列中的某个订单改为手动下单
	 */
	public function orderByHand()
	{
		$id = $_POST['order_id'];
		if (!empty($id)) {
			$redis = Credis::getInstance();
			$c = $redis->lrem(self::ORDER_QUEUE_KEY, $id, 0);
			if ($c > 0) {
				// 暂时直接将订单状态改为已完成, 因为手动下单不会将网站订单号写入数据库无法同步订单状态
				Order::exec(' UPDATE `orders` SET `status` = ' . self::STATUS_COMPLETE . ' WHERE id = ' . $id);

				$this->return_success();
			} else {
				$this->error('orderByHand failed', [ $id ]);
				$this->return_error(100, '队列中并不存在该订单或已经执行');
			}
		} else {
			$this->return_error();
		}
	}

	/**
	 * 退还积分
	 */
	public function returnScores()
	{
		$id = $this->getRequestID();

		if (empty($id)) {
			$this->return_error();
		} else {
			$order = Order::findOne('orders', ' id = ? ', [ $id ]);
			if (empty($order)) {
				$this->return_error();
			} else {
				if ($order->status == self::STATUS_DEALING || $order->status == self::STATUS_COMPLETE) {
					// 如果存在于redis中，删除掉
					$redis = Credis::getInstance();
					$redis->lrem(self::ORDER_QUEUE_KEY, $id, 0);

					// 事务处理
					Order::begin();
					try {
						// 将订单状态改为失败
						$order->status = self::STATUS_FAILED;
						Order::store($order);

						// scorelogs退还积分纪录
						$scorelog = \RedBeanPHP\R::dispense('scorelogs');
						$scorelog->uid = $order->uid;
						$scorelog->type = self::SCORE_LOG_RETURN_TYPE;
						$scorelog->amount = $order->scores;
						$scorelog->remark = '退回积分';
						\RedBeanPHP\R::store($scorelog);

						// 退回积分给用户
						User::exec(' UPDATE `users` SET `remaining_scores` =  `remaining_scores` + ' . $order->scores . ', `updated_at` = "' . date('Y-m-d H:i:s', time()) . '" WHERE id = ' . $order->uid);

						Order::commit();

						$this->return_success();
					} catch (Exception $e) {
						// $this->error('order create rollbak', [$e->getMessages()]);
						Order::rollback();
							
						$this->return_error();
					}
				} else {
					$this->return_error();
				}
			}
		}
	}
}
