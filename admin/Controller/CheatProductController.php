<?php

namespace Admin\Controller;

use Admin\Model\CheatProduct;
use Admin\Model\Cheat;

class CheatProductController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/cheatproducts';
		$this->search_desc = '请输入indentify';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = CheatProduct::count('cheatproducts');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = CheatProduct::count('cheatproducts', ' identify = ?', [ $page_data['search'] ]);

				// 列表
				$cheatproducts = CheatProduct::getAll('SELECT cp.*, ct.title FROM `cheatproducts` cp, `cheats` ct WHERE cp.identify = ? AND cp.cid = ct.id ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$cheatproducts = CheatProduct::getAll('SELECT cp.*, ct.title FROM `cheatproducts` cp, `cheats` ct WHERE cp.cid = ct.id ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($cheatproducts as $k => $card) {
				$cheatproducts[$k]['status'] = $card['status'] == self::STATUS_ONLINE ? '启用' : '停用';
			}

			$this->json_encode_output(array('data' => $cheatproducts, 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '刷赞商品列表';
			$cheats = Cheat::findAll('cheats', ' status = ? ', [ self::STATUS_ONLINE ]);

			$this->render('cheatproduct/list', array('title' => $title, 'cheats' => $cheats));
		}
	}

	/**
	 * 增加产品
	 */
	public function add()
	{
		$scores = intval($_POST['scores']);
		$cid = intval($_POST['cid']);
		$amount = intval($_POST['amount']);

		if ($scores <= 0 || $amount <= 0 || $cid <= 0) {
			$this->return_error(400, '参数不合法');
		} else {
			$cheatproduct = CheatProduct::dispense('cheatproducts');
			$cheatproduct->scores = $scores;
			$cheatproduct->cid = $cid;
			$cheatproduct->amount = $amount;
			$cheatproduct->status = self::STATUS_ONLINE;

			if (CheatProduct::store($cheatproduct)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 更新产品
	 */
	public function update()
	{
		$id = $_POST['id'];
		$scores = intval($_POST['edit-scores']);
		$cid = intval($_POST['edit-type']);
		$amount = intval($_POST['edit-amount']);

		if (empty($id) || $scores <= 0 || $amount <= 0 || $cid <= 0) {
			$this->return_error(400, '参数不合法');
		} else {
			$cheatproduct = CheatProduct::findOne('cheatproducts', ' id = ? ', [ $id ]);
			if (empty($cheatproduct)) {
				$this->return_error();
			} else {
				$cheatproduct->scores = $scores;
				$cheatproduct->cid = $cid;
				$cheatproduct->amount = $amount;
				$cheatproduct->updated_at = new \DateTime;
	
				if (CheatProduct::store($cheatproduct)) {
					$this->return_success();
				} else {
					$this->return_error();
				}
			}
		}
	}

	/**
	 * 上线cheatproduct
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$cheatproduct = CheatProduct::findOne('cheatproducts', ' id = ? ', [ $id ]);
		if (!empty($cheatproduct)) {
			// 状态判断
			if ($cheatproduct->status == self::STATUS_OFFLINE) {
				$cheatproduct->status = self::STATUS_ONLINE;
				$cheatproduct->updated_at = new \Datetime;
				if (CheatProduct::store($cheatproduct)) {
					$this->return_success();
				} else {
					$this->error('update cheatproduct failed', (array)$cheatproduct);
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
	 * 下线cheatproduct
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$cheatproduct = CheatProduct::findOne('cheatproducts', ' id = ? ', [ $id ]);
		if (!empty($cheatproduct)) {
			// 状态判断
			if ($cheatproduct->status == self::STATUS_ONLINE) {
				$cheatproduct->status = self::STATUS_OFFLINE;
				$cheatproduct->updated_at = new \Datetime;
				if (CheatProduct::store($cheatproduct)) {
					$this->return_success();
				} else {
					$this->error('update cheatproduct failed', (array)$cheatproduct);
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
