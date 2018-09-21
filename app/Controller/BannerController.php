<?php

namespace App\Controller;

use App\Model\Banner;

class BannerController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	/**
	 * banner列表
	 */
	public function index()
	{
		$banners = array_values(Banner::findAll('banners', ' status = ? ', [ self::STATUS_ONLINE ]));
		$ret = [];
		if (foreachAble($banners)) {
			foreach ($banners as $k => $banner) {
				$ret[$k]['id'] = $banner->id;
				$ret[$k]['pic'] = $banner->pic;
				$ret[$k]['link'] = $banner->link;
			}
		}

		$this->return_success($ret);
	}
}