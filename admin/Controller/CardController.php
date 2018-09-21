<?php

namespace Admin\Controller;

use Admin\Model\Card;
use Admin\Model\Cheat;

class CardController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	/**
	 * 卡密
	 */
	public function index()
	{
		$this->ajax_api = '/cards';
		$this->search_desc = '请输入账号';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Card::count('cards');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Card::count('cards', ' identify = ?', [ $page_data['search'] ]);

				// 列表
				$cards = Card::getAll('SELECT cd.*, ct.title FROM `cards` cd, `cheats` ct WHERE cd.identify = ? AND cd.type = ct.id ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$cards = Card::getAll('SELECT cd.*, ct.title FROM `cards` cd, `cheats` ct WHERE cd.type = ct.id ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($cards as $k => $card) {
				$cards[$k]['status'] = $card['status'] == self::STATUS_ONLINE ? '启用' : '停用';
				$cards[$k]['origin_password'] = $cards[$k]['password'];
				$cards[$k]['password'] = empty($card['password']) ? '空' : $card['password'];
			}

			$this->json_encode_output(array('data' => $cards, 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '卡密列表';
			$cheats = Cheat::findAll('cheats', ' status = ? ', [ self::STATUS_ONLINE ]);

			$this->render('card/list', array('title' => $title, 'cheats' => $cheats));
		}
	}

	/**
	 * 增加卡密
	 */
	public function add()
	{
		$type = intval($_POST['type']);
		$identify = $_POST['identify'];
		$password = $_POST['password'];
		$total = intval($_POST['total']);
		$remaining = intval($_POST['remaining']);

		if (empty($type) || empty($identify) || $total <= 0 || $total > pow(2, 24) || $remaining > pow(2, 24)) {
			$this->return_error(400, '参数不合法');
		} else {
			$card = Card::dispense('cards');
			$card->type = $type;
			$card->identify = $identify;
			$card->password = $password;
			$card->total = $total;
			$card->remaining = $remaining;
			$card->status = self::STATUS_ONLINE;

			if (Card::store($card)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 更新卡密
	 */
	public function update()
	{
		$id = $_POST['id'];
		$identify = $_POST['edit-identify'];
		$password = $_POST['edit-password'];
		$type = intval($_POST['edit-type']);
		$total = $_POST['edit-total'];
		$remaining = $_POST['edit-remaining'];

		if (empty($id) || empty($identify) || $total <= 0 || $total > pow(2, 24) || $remaining > pow(2, 24)) {
			$this->return_error(400, '参数不合法');
		} else {
			$card = Card::findOne('cards', ' id = ? ', [ $id ]);
			$card->password = $password;
			$card->type = $type;
			$card->total = $total;
			$card->remaining = $remaining;
			$card->updated_at = new \Datetime;

			if (Card::store($card)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 上线card
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$card = Card::findOne('cards', ' id = ? ', [ $id ]);
		if (!empty($card)) {
			// 状态判断
			if ($card->status == self::STATUS_OFFLINE) {
				$card->status = self::STATUS_ONLINE;
				$card->updated_at = new \Datetime;
				if (Card::store($card)) {
					$this->return_success();
				} else {
					$this->error('update card failed', (array)$card);
					$this->return_error();
				}
			} else {
				$this->return_success();
			}
		} else {
			$this->return_error(401, '非法请求');
		}
	}

	/**
	 * 下线card
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$card = Card::findOne('cards', ' id = ? ', [ $id ]);
		if (!empty($card)) {
			// 状态判断
			if ($card->status == self::STATUS_ONLINE) {
				$card->status = self::STATUS_OFFLINE;
				$card->updated_at = new \Datetime;
				if (Card::store($card)) {
					$this->return_success();
				} else {
					$this->error('update card failed', (array)$card);
					$this->return_error();
				}
			} else {
				$this->return_success();
			}
		} else {
			$this->return_error(401, '非法请求');
		}
	}
}
