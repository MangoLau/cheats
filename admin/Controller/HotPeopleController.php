<?php

namespace Admin\Controller;

use Admin\Model\HotPeople;

class HotPeopleController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/hotpeoples';
		$this->search_desc = '请输入qq';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = HotPeople::count('hotpeople');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = HotPeople::count('hotpeople', 'qq = ?', [ $page_data['search'] ]);

				// 列表
				$hotpeoples = HotPeople::findAll('hotpeople', 'qq = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$hotpeoples = HotPeople::findAll('hotpeople', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($hotpeoples as $k => $hotpeople) {
				$hotpeoples[$k]['avatar'] = $hotpeople->avatar ?: 'http://q1.qlogo.cn/g?b=qq&nk=' . $hotpeople->qq . '&s=100';
				$hotpeoples[$k]['status'] = $hotpeople->status == self::STATUS_ONLINE ? '启用' : '停用';
			}

			$this->json_encode_output(array('data' => array_values($hotpeoples), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '空间红人表';

			$this->render('hotpeople/list', array('title' => $title));
		}
	}

	/**
	 * 增加产品
	 */
	public function add()
	{
		$qq = intval($_POST['qq']);
		$scores = intval($_POST['scores']);

		if ($qq <= 0 || $scores <= 0) {
			$this->return_error(400, '参数不合法');
		} else {
			$hotpeople = HotPeople::dispense('hotpeople');
			$hotpeople->qq = $qq;
			$hotpeople->scores = $scores;
			$hotpeople->status = self::STATUS_ONLINE;

			if (HotPeople::store($hotpeople)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 上线hotpeople
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$hotpeople = HotPeople::findOne('hotpeople', ' id = ? ', [ $id ]);
		if (!empty($hotpeople)) {
			// 状态判断
			if ($hotpeople->status == self::STATUS_OFFLINE) {
				$hotpeople->status = self::STATUS_ONLINE;
				$hotpeople->updated_at = new \Datetime;
				if (HotPeople::store($hotpeople)) {
					$this->return_success();
				} else {
					$this->error('update hotpeople failed', (array)$hotpeople);
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
	 * 下线hotpeople
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$hotpeople = HotPeople::findOne('hotpeople', ' id = ? ', [ $id ]);
		if (!empty($hotpeople)) {
			// 状态判断
			if ($hotpeople->status == self::STATUS_ONLINE) {
				$hotpeople->status = self::STATUS_OFFLINE;
				$hotpeople->updated_at = new \Datetime;
				if (HotPeople::store($hotpeople)) {
					$this->return_success();
				} else {
					$this->error('update hotpeople failed', (array)$hotpeople);
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
