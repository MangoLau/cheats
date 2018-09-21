<?php

namespace App\Controller;

use App\Model\Config;

class ConfigController extends BaseController
{
	CONST PLATFORM_SERVER = 1;
	CONST PALTFORM_CLIENT = 2;
	CONST PALTFORM_ALL = 3;

	CONST STATUS_ONLINE = 1;
	CONST STATUS_OFFLINE = 0;

	public function index()
	{
		$key = $_GET['k'];
		$ret = [];

		if (empty($key)) {
			$configs = Config::findAll('configs', ' platform != ? AND status = ? ', [ self::PLATFORM_SERVER, self::STATUS_ONLINE ]);

			if (foreachAble($configs)) {
				$configs = array_values($configs);
				foreach ($configs as $k => $config) {
					$ret[$k]['key'] = $config->key;
					$ret[$k]['value'] = $config->value;
					$ret[$k]['remark'] = $config->remark;
				}
			}
		} else {
			$config = Config::findOne('configs', ' `platform` != ? AND status = ? AND `key` = ? ', [ self::PLATFORM_SERVER, self::STATUS_ONLINE, $key ]);

			if (!empty($config)) {
				$ret['key'] = $config->key;
				$ret['value'] = $config->value;
				$ret['remark'] = $config->remark;
			}
		}

		$this->return_success($ret);
	}
}