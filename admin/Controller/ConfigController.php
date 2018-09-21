<?php

namespace Admin\Controller;

use Admin\Model\Config;

class ConfigController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	CONST PLATFORM_SERVER = 1;
	CONST PLATFORM_CLIENT = 2;
	CONST PLATFORM_ALL = 3;

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/configs';
		$this->search_desc = '请输入key';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Config::count('configs');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Config::count('configs', ' key = ?', [ $page_data['search'] ]);

				// 列表
				$configs = Config::findAll('configs', ' key = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$configs = Config::findAll('configs', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($configs as $k => $config) {
				$configs[$k]->status = $config->status == self::STATUS_ONLINE ? '启用' : '停用';
				$configs[$k]->remark = $config->remark ?: '无';
				$configs[$k]->origin_platform = $config->platform;
				$configs[$k]->platform = $config->platform == self::PLATFORM_SERVER ? '服务端' : ($config->platform == self::PLATFORM_CLIENT ? '客户端' : '所有');
			}

			$this->json_encode_output(array('data' => array_values($configs), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '动态参数列表';

			$this->render('config/list', array('title' => $title));
		}
	}

	/**
	 * 增加配置
	 */
	public function add()
	{
		$key = $_POST['key'];
		$value = $_POST['value'];
		$platform = intval($_POST['platform']);
		$remark = $_POST['remark'];

		if (empty($key) || empty($value) || empty($remark) || !in_array($platform, [ self::PLATFORM_SERVER, self::PLATFORM_CLIENT, self::PLATFORM_ALL ])) {
			$this->return_error(400, '参数不合法');
		} else {
			$config = Config::dispense('configs');
			$config->key = $key;
			$config->value = $value;
			$config->platform = $platform;
			$config->status = self::STATUS_ONLINE;
			$config->remark = $remark;

			if (Config::store($config)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 更新配置
	 */
	public function update()
	{
		$id = $_POST['id'];
		$key = $_POST['edit-key'];
		$value = $_POST['choice'] ?: $_POST['edit-value'];			// 含特殊选项的配置优先使用选项值
		$platform = intval($_POST['edit-platform']);
		$remark = $_POST['edit-remark'];

		if (empty($id) || empty($key) || empty($value) || empty($remark) || !in_array($platform, [ self::PLATFORM_SERVER, self::PLATFORM_CLIENT, self::PLATFORM_ALL ])) {
			$this->return_error(400, '参数不合法');
		} else {
			$config = Config::findOne('configs', ' id = ? ', [ $id ]);
			// $config->key = $key;
			$config->value = $value;
			$config->platform = $platform;
			$config->remark = $remark;
			$config->updated_at = new \Datetime;

			if (Config::store($config)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 上线config
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$config = Config::findOne('configs', ' id = ? ', [ $id ]);
		if (!empty($config)) {
			// 状态判断
			if ($config->status == self::STATUS_OFFLINE) {
				$config->status = self::STATUS_ONLINE;
				$config->updated_at = new \Datetime;
				if (Config::store($config)) {
					$this->return_success();
				} else {
					$this->error('update config failed', (array)$config);
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
	 * 下线config
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$config = Config::findOne('configs', ' id = ? ', [ $id ]);
		if (!empty($config)) {
			// 状态判断
			if ($config->status == self::STATUS_ONLINE) {
				$config->status = self::STATUS_OFFLINE;
				$config->updated_at = new \Datetime;
				if (Config::store($config)) {
					$this->return_success();
				} else {
					$this->error('update config failed', (array)$config);
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
