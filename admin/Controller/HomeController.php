<?php
// 概览
namespace Admin\Controller;

use Admin\Model\Recharge;
use Admin\Model\Channel;
use Admin\Model\Config;

class HomeController extends BaseController
{
	const STATUS_PAYING = 0;		// 未支付
	const STATUS_PAYED = 1;			// 已支付

	const TYPE_SCORE = 1;			// 积分
	const TYPE_VIP = 2;				// vip

	const PAY_TYPE_WECHAT = 1;		// 微信支付
	const PAY_TYPE_ALIPAY = 2;		// 支付宝支付
	const PAY_TYPE_OTHER = 3;		// 其他方式

	const PLATFORM_ANDROID = 0;		// 安卓平台
	const PLATFORM_IOS = 1;			// iOS平台

	const STATUS_ONLINE = 1;		// 在线

	const INCOME_SHOW_RATE_CONFIG_KEY = 'income_show_rate';		// 后台假数据倍率key

	public function index()
	{
		$start_date = date('Y-m-d H:i:s', strtotime($_GET['start-date'] ?: date('Ymd', strtotime('-15 days'))));
		$end_date = date('Y-m-d H:i:s', strtotime($_GET['end-date'] ?: date('Ymd', getCurrentTime())));

		if ($start_date >= $end_date) {
			$start_date = $end_date;
		}

		// 获取所有数据
		$data = array_values(Recharge::findAll('recharges', ' status = ? AND created_at >= ? AND created_at <= ? ', [ self::STATUS_PAYED, $start_date, $end_date ]));

		// 获取所有渠道
		$channels = Channel::findAll('channels', ' status = ? ', [ self::STATUS_ONLINE ]);

		// 是否有配置后台假数据倍率
		$income_show_rate = Config::findOne('configs', ' `key` = ? AND `status` = ? ', [ self::INCOME_SHOW_RATE_CONFIG_KEY, 1 ]);
		$income_show_rate = (empty($income_show_rate) || $income_show_rate->value <= 0) ? 1 : $income_show_rate->value;

		// 根据充值类型区分／根据支付方式区分／根据安卓iOS平台区分／根据渠道区分
		$ret_recharge_type = $ret_tmp_recharge_type = $ret_tmp_pay_type = $ret_pay_type = $ret_platform_type = $ret_tmp_platform_type = $ret_channel = $ret_tmp_channel = [];
		if (!empty($data)) {
			foreach ($data as $k => $d) {
				$d->money = intval(($d->money * $income_show_rate)/100) * 100;		// 确保转化为元没有小数点

				$d->channel = $d->channel ?: 'none';
				$created_day = date('Ymd', strtotime($d->created_at));

				$ret_tmp_recharge_type[$created_day][$d->type] += $d->money;
				$ret_tmp_pay_type[$created_day][$d->pay_type] += $d->money;
				$ret_tmp_platform_type[$created_day][$d->platform] += $d->money;
				$ret_tmp_channel[$created_day][$d->channel] += $d->money;
			}
		}

		for ($i = 0;; $i++) {
			$day_timestamp = strtotime($start_date) + $i * 24 * 60 *60;
			if ($day_timestamp > strtotime($end_date)) {
				break;
			}

			$day = date('m-d', $day_timestamp);
			$created_day = date('Ymd', $day_timestamp);

			// 根据充值类型区分
			$ret_recharge_type[$i] = [
				'created_day' => $day,
				'total_vip_fee' => empty($ret_tmp_recharge_type[$created_day][self::TYPE_VIP]) ? 0 : sprintf('%.2f', $ret_tmp_recharge_type[$created_day][self::TYPE_VIP]/100),
				'total_scores_fee' => empty($ret_tmp_recharge_type[$created_day][self::TYPE_SCORE]) ? 0 : sprintf('%.2f', $ret_tmp_recharge_type[$created_day][self::TYPE_SCORE]/100),
			];

			// 根据支付方式区分
			$ret_pay_type[$i] = [
				'created_day' => $day,
				'total_wechat_fee' => empty($ret_tmp_pay_type[$created_day][self::PAY_TYPE_WECHAT]) ? 0 : sprintf('%.2f', $ret_tmp_pay_type[$created_day][self::PAY_TYPE_WECHAT]/100),
				'total_alipay_fee' => empty($ret_tmp_pay_type[$created_day][self::PAY_TYPE_ALIPAY]) ? 0 : sprintf('%.2f', $ret_tmp_pay_type[$created_day][self::PAY_TYPE_ALIPAY]/100),
				'total_other_fee' => empty($ret_tmp_pay_type[$created_day][self::PAY_TYPE_OTHER]) ? 0 : sprintf('%.2f', $ret_tmp_pay_type[$created_day][self::PAY_TYPE_OTHER]/100),
			];

			// 根据安卓iOS平台区分
			$ret_platform_type[$i] = [
				'created_day' => $day,
				'total_android_fee' => empty($ret_tmp_platform_type[$created_day][self::PLATFORM_ANDROID]) ? 0 : sprintf('%.2f', $ret_tmp_platform_type[$created_day][self::PLATFORM_ANDROID]/100),
				'total_ios_fee' => empty($ret_tmp_platform_type[$created_day][self::PLATFORM_IOS]) ? 0 : sprintf('%.2f', $ret_tmp_platform_type[$created_day][self::PLATFORM_IOS]/100),
			];

			// 根据渠道区分
			$ret_channel[$i] = [
				'created_day' => $day,
			];
			$tmp = [];
			foreach ($channels as $channel) {
				$tmp[$channel->name] = empty($ret_tmp_channel[$created_day][$channel->name]) ? 0 : sprintf('%.2f', $ret_tmp_channel[$created_day][$channel->name]/100);
			}
			$tmp['none'] = empty($ret_tmp_channel[$created_day]['none']) ? 0 : sprintf('%.2f', $ret_tmp_channel[$created_day]['none']/100);

			$ret_channel[$i] += $tmp;
		}

		// dd([ $ret_recharge_type, $ret_pay_type, $ret_platform_type]);
		
		// 图表数据
		$arr_channels = [];
		foreach ($channels as $c) {
			$arr_channels[] = $c['name'];
		}
		$arr_channels[] = 'none';
		$morris_channel = [
			'ykeys' => $arr_channels,
			'labels' => $arr_channels,
		];

		$this->render('home/index', [ 'start_date' => date('Y-m-d', strtotime($start_date)), 'end_date' => date('Y-m-d', strtotime($end_date)), 'recharge_type_data' => $ret_recharge_type, 'pay_type_data' => $ret_pay_type, 'pay_platform_data' => $ret_platform_type, 'channel_data' => $ret_channel, 'morris_channel' => $morris_channel ]);
	}
}
