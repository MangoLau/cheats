<?php

namespace Admin\Controller;

use Admin\Model\Channel;
use Admin\Model\Cheat;

class ChannelController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 停用

	CONST PLATFORM_SERVER = 1;
	CONST PLATFORM_CLIENT = 2;
	CONST PLATFORM_ALL = 3;

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/channels';
		$this->search_desc = '请输入name';
		$cheats = Cheat::findAll('cheats', ' status = ? ', [ self::STATUS_ONLINE ]);

		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Channel::count('channels');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Channel::count('channels', ' name = ?', [ $page_data['search'] ]);

				// 列表
				$channels = Channel::findAll('channels', ' name = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$channels = Channel::findAll('channels', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($channels as $k => $channel) {
				$channels[$k]->status = $channel->status == self::STATUS_ONLINE ? '启用' : '停用';
				$channels[$k]->remark = $channel->remark ?: '无';
				$arr_cheat = json_decode($channel->cheats);
				$tmp = [];
				if (!empty($arr_cheat)) {
					foreach ($arr_cheat as $v) {
						if (isset($cheats[$v])) {
							$tmp[] = $cheats[$v]['title'];
						}
					}
				}
				$channels[$k]->cheats = $tmp;
				$channels[$k]->cheat_ids = $arr_cheat ? $arr_cheat : [];
			}

			$this->json_encode_output(array('data' => array_values($channels), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '渠道列表';

			$this->render('channel/list', array('title' => $title, 'cheats' => $cheats));
		}
	}

	/**
	 * 增加配置
	 */
	public function add()
	{
		$name = $_POST['name'];
		$remark = $_POST['remark'];
		$cheats = $_POST['cheats'];

		if (empty($name) || empty($remark)) {
			$this->return_error(400, '参数不合法');
		} else {
			$channel = Channel::dispense('channels');
			$channel->name = $name;
			$channel->status = self::STATUS_ONLINE;
			$channel->remark = $remark;
			$channel->cheats = !empty($cheats) ? json_encode($cheats) : '';

			if (Channel::store($channel)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 更新
	 */
	public function update()
	{
		$id = $_POST['id'];
		$name = $_POST['edit-name'];
		$remark = $_POST['edit-remark'];
		$cheats = $_POST['cheats'];

		if (empty($id) || empty($name) || empty($remark)) {
			$this->return_error(400, '参数不合法');
		} else {
			$channel = Channel::findOne('channels', ' id = ? ', [ $id ]);
			// $channel->name = $name;
			$channel->remark = $remark;
			$channel->cheats = !empty($cheats) ? json_encode($cheats) : '';
			$channel->updated_at = new \Datetime;

			if (Channel::store($channel)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 上线channel
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$channel = Channel::findOne('channels', ' id = ? ', [ $id ]);
		if (!empty($channel)) {
			// 状态判断
			if ($channel->status == self::STATUS_OFFLINE) {
				$channel->status = self::STATUS_ONLINE;
				$channel->updated_at = new \Datetime;
				if (Channel::store($channel)) {
					$this->return_success();
				} else {
					$this->error('update channel failed', (array)$channel);
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
	 * 下线channel
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$channel = Channel::findOne('channels', ' id = ? ', [ $id ]);
		if (!empty($channel)) {
			// 状态判断
			if ($channel->status == self::STATUS_ONLINE) {
				$channel->status = self::STATUS_OFFLINE;
				$channel->updated_at = new \Datetime;
				if (Channel::store($channel)) {
					$this->return_success();
				} else {
					$this->error('update channel failed', (array)$channel);
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
