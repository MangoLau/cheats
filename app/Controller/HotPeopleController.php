<?php

namespace App\Controller;

use App\Model\HotPeople;

/**
* 空间红人
*/
class HotPeopleController extends BaseController
{
	CONST STATUS_ONLINE = 1;			// 启用
	CONST STATUS_OFFLINE = 0;			// 未启用

	public function index()
	{
		$people = array_values(HotPeople::findAll('hotpeople', ' status = ? ORDER BY `scores` DESC ', [ self::STATUS_ONLINE ]));
		$ret = [];

		if (foreachAble($people)) {
			foreach ($people as $k => $v) {
				$ret[$k]['qq'] = $v->qq;
				$ret[$k]['avatar'] = 'http://q1.qlogo.cn/g?b=qq&nk=' . $v->qq . '&s=100';
				$ret[$k]['scores'] = $v->scores;
			}
		}

		$this->return_success($ret);
	}

	/**
	 * 分享排行榜接口
	 */
	public function shareRanks()
	{
		$people = array_values(HotPeople::findAll('hotpeople', ' status = ? ', [ self::STATUS_ONLINE ]));
		$ret = [];

		if (foreachAble($people)) {
			foreach ($people as $k => $v) {
				$ret[$k]['qq'] = $v->qq;
				$ret[$k]['avatar'] = 'http://q1.qlogo.cn/g?b=qq&nk=' . $v->qq . '&s=100';
				$ret[$k]['scores'] = $v->scores + rand(-5000, 5000);
			}
		}

		usort($ret, function($a, $b) { return $a['scores'] < $b['scores']; });

		$this->return_success($ret);
	}
}