<?php

namespace App\Controller;

use App\Model\Product;
use App\Model\Cheat;
use App\Model\CheatProduct;
use App\Model\Channel;

class ProductController extends BaseController
{
	const STATUS_ONLINE = 1;			// 产品状态-上架
	const STATUS_OFFLINE = 0;			// 产品状态-下架

	const TYPE_SHUOSHUO = 1;			// 说说类型
	const TYPE_RIZHI = 2;				// 日志类型
	const TYPE_QQ_OTHER = 3;			// qq其他
	const TYPE_LALAQUAN = 4;			// 拉圈圈
	const TYPE_KS_FANS = 5;				// 快手粉丝
	const TYPE_KS_OTHER = 6;			// 快手其他
	const TYPE_KG = 7;					// K歌
	const TYPE_OTHER = 8;				// 其它
	const TYPE_DOUYIN_FANS = 9;			// 抖音粉丝
	const TYPE_DOUYIN_OTHER = 10;		// 抖音其他
	

	// 分类
	const CATEGORY_QZONE = 1;			// Qzone
	const CATEGORY_KUAISHOU = 2;		// 快手
	const CATEGORY_KG = 3;				// K歌
	const CATEGORY_DOUYIN = 4;			// 抖音

	const SHOW_OFFLINE_PRODUCT_VERSION = '3.0.0';

	/**
	 * 产品列表
	 */
	public function index()
	{
		$products = array_values(Product::findAll('products', ' status = ? ORDER BY `amount` ', [ self::STATUS_ONLINE ]));

		if (foreachAble($products)) {
			foreach ($products as $k => $product) {
				unset($products[$k]['attach']);
				unset($products[$k]['status']);
				unset($products[$k]['created_at']);
				unset($products[$k]['updated_at']);
			}
		}

		$this->return_success($products);
	}

	/**
	 * QQ刷赞/转发等产品列表
	 */
	public function qq()
	{
		$products = [];

		// 苹果审核期间暂时关闭说说、日志接口 2017/05/12 09:00:00
		if ($this->platform == 'ios') {
			// $products = array_values(Cheat::findAll('cheats', ' status = ? AND remark != ? AND remark != ? AND remark != ? ', [ self::STATUS_ONLINE, 'shuoshuo', 'rizhi', 'qzone' ]));
		} else {
			// 只有channle参数存在于表里面才生效
			if (!empty($this->channel)) {
				$channel_cheats = Channel::findOne('channels', ' name = ? AND status = ? ', [ $this->channel, self::STATUS_ONLINE ]);
				$cheat_ids = [];
				if (!empty($channel_cheats)) {
					if (empty($channel_cheats->cheats)) {
						$products = [];
					} else {
						$cheat_ids = json_decode($channel_cheats->cheats, true) ?: [];
						$products = array_values(Cheat::findAll('cheats', ' `category` = ? AND `id` IN (' . implode(',', $cheat_ids) . ')', [ self::CATEGORY_QZONE ]));
				}
				} else {
					$products = array_values(Cheat::findAll('cheats', ' `category` = ? ', [ self::CATEGORY_QZONE ]));
				}
			} else {
				$products = array_values(Cheat::findAll('cheats', ' `category` = ? ', [ self::CATEGORY_QZONE ]));
			}
		}

		$ret = [];

		if (foreachAble($products)) {
			foreach ($products as $k => $product) {
				if (version_compare($this->version, self::SHOW_OFFLINE_PRODUCT_VERSION) < 0 && $product->status == self::STATUS_OFFLINE) {
					continue;
				}

				$ret[$k]['id'] = $product->id;
				$ret[$k]['title'] = $product->title;
				$ret[$k]['icon'] = getIconUrl($product->icon);
				$ret[$k]['need_vip'] = $product->need_vip;
				$ret[$k]['status'] = $product->status;
				$ret[$k]['type'] = $product->remark == 'shuoshuo' ? self::TYPE_SHUOSHUO : ($product->remark == 'rizhi' ? self::TYPE_RIZHI : ($product->remark == 'laquanquan' ? self::TYPE_LALAQUAN : self::TYPE_QQ_OTHER));
			}
		}

		$this->return_success($ret);
	}

	/**
	 * v2 接口，增加了分类
	 * 2017-8-8 14:00
	 */
	public function qqv2()
	{
		$products = [];

		// 苹果审核期间暂时关闭说说、日志接口 2017/05/12 09:00:00
		if ($this->platform == 'ios') {
			// $products = array_values(Cheat::findAll('cheats', ' status = ? AND remark != ? AND remark != ? AND remark != ? ', [ self::STATUS_ONLINE, 'shuoshuo', 'rizhi', 'qzone' ]));
			$products = [];
		} else {
			// 只有channle参数存在于表里面才生效
			if (!empty($this->channel)) {
				$channel_cheats = Channel::findOne('channels', ' name = ? AND status = ? ', [ $this->channel, self::STATUS_ONLINE ]);
				$cheat_ids = [];
				if (!empty($channel_cheats)) {
					if (empty($channel_cheats->cheats)) {
						$products = [];
					} else {
						$cheat_ids = json_decode($channel_cheats->cheats, true) ?: [];
						$products = array_values(Cheat::findAll('cheats', ' `id` IN (' . implode(',', $cheat_ids) . ')', []));
				}
				} else {
					$products = array_values(Cheat::findAll('cheats'));
				}
			} else {
				$products = array_values(Cheat::findAll('cheats'));
			}
		}

		$ret = [
			[
				'section' => '最热专区',
				'icon' => 'http://106.75.77.8/uploads/icons/ic_huo.png',
				'data' => [],
			],
			[
				'section' => '快手专区',
				'icon' => 'http://106.75.77.8/uploads/icons/ic_kuaishou.png',
				'data' => [],
			],
			[
				'section' => 'K歌专区',
				'icon' => 'http://106.75.77.8/uploads/icons/ic_kge.png',
				'data' => [],
			],
			[
				'section' => '抖音专区',
				'icon' => 'http://106.75.77.8/uploads/icons/ic_kge.png',
				'data' => [],
			],
		];

		if (foreachAble($products)) {
			foreach ($products as $k => $product) {
				if (version_compare($this->version, self::SHOW_OFFLINE_PRODUCT_VERSION) < 0 && $product->status == self::STATUS_OFFLINE) {
					continue;
				}

				$tmp = [];
				$tmp['id'] = $product->id;
				$tmp['title'] = $product->title;
				$tmp['icon'] = getIconUrl($product->icon);
				$tmp['need_vip'] = $product->need_vip;
				$tmp['status'] = $product->status;
				if ($product->category == self::CATEGORY_QZONE) {
					$tmp['type'] = $product->remark == 'shuoshuo' ? self::TYPE_SHUOSHUO : ($product->remark == 'rizhi' ? self::TYPE_RIZHI : ($product->remark == 'laquanquan' ? self::TYPE_LALAQUAN : self::TYPE_QQ_OTHER));
				} elseif ($product->category == self::CATEGORY_KUAISHOU) {
					$tmp['type'] = $product->remark == 'ksfans' ? self::TYPE_KS_FANS : self::TYPE_KS_OTHER;
				} elseif ($product->category == self::CATEGORY_KG) {
					$tmp['type'] = self::TYPE_KG;
				} elseif ($product->category == self::CATEGORY_DOUYIN) {
					$tmp['type'] = $product->remark == 'douyinfans' ? self::TYPE_DOUYIN_FANS : self::TYPE_DOUYIN_OTHER;
				} else {
					$tmp['type'] = self::TYPE_OTHER;
				}

				array_push($ret[$product->category-1]['data'], $tmp);
			}
		}

		$this->return_success(array_values($ret));
	}

	/**
	 * QQ某个刷赞类型的产品列表
	 */
	public function qqList()
	{
		$cid = $_GET['cid'];

		$products = array_values(CheatProduct::findAll('cheatproducts', ' cid = ? AND status = ? ORDER BY `amount` ASC ', [ $cid, self::STATUS_ONLINE ]));

		$ret = [];
		if (foreachAble($products)) {
			foreach ($products as $k => $product) {
				$ret[$k]['id'] = $product->id;
				$ret[$k]['amount'] = $product->amount;
				$ret[$k]['scores'] = $product->scores;
			}
		}

		$this->return_success($ret);
	}
}
