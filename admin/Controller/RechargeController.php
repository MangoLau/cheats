<?php

namespace Admin\Controller;

use Admin\Model\Recharge;

class RechargeController extends BaseController
{
	CONST STATUS_PAYING = 0;		// 未支付
	CONST STATUS_PAYED = 1;			// 已支付

	CONST TYPE_SCORE = 1;			// 积分
	CONST TYPE_VIP = 2;				// vip

	CONST PAY_TYPE_WECHAT = 1;		// 微信支付
	CONST PAY_TYPE_ALIPAY = 2;		// 支付宝支付
	CONST PAY_TYPE_OTHER = 3;		// 其他方式

	CONST PLATFORM_ANDROID = 0;		// 安卓平台
	CONST PLATFORM_IOS = 1;			// iOS平台

	CONST CALLBACK_URL = 'http://106.75.77.8/recharge/callback';

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/recharges';
		$this->search_desc = '请输入uid';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Recharge::count('recharges');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Recharge::count('recharges', ' uid = ?', [ $page_data['search'] ]);

				// 列表
				$recharges = Recharge::findAll('recharges', ' uid = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$recharges = Recharge::findAll('recharges', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($recharges as $k => $recharge) {
				$recharges[$k]['type'] = $recharge->type == self::TYPE_SCORE ? '积分' : 'vip(月)';
				$recharges[$k]['origin_status'] = $recharge->status;
				$recharges[$k]['status'] = $recharge->status == self::STATUS_PAYED ? '已支付' : '未支付';
				$recharges[$k]['pay_type'] = $recharge->pay_type == self::PAY_TYPE_WECHAT ? '微信' : ($recharge->pay_type == self::PAY_TYPE_ALIPAY ? '支付宝' : ($recharge->pay_type == self::PAY_TYPE_OTHER ? '其他' : '无'));
				$recharges[$k]['platform'] = $recharge->platform == self::PLATFORM_ANDROID ? '安卓' : 'iOS';
			}

			$this->json_encode_output(array('data' => array_values($recharges), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '充值列表';

			$this->render('recharge/list', array('title' => $title));
		}
	}

	/**
	 * 更新bmob订单号
	 */
	public function updateBmobOrderID()
	{
		$id = $_POST['id'];
		$bmob_order_id = $_POST['edit-bmoborderid'];

		if (empty($id) || empty($bmob_order_id)) {
			$this->return_error(400, '参数不完整');
		} else {
			$recharge = Recharge::findOne('recharges', ' id = ? ', [ $id ]);
			if (empty($recharge)) {
				$this->return_error(401, '订单不存在');
			} elseif (!empty($recharge->bmob_order_id)) {
				$this->return_error(402, '该订单已经存在bomb订单号');
			} elseif ($recharge->status == self::STATUS_PAYED) {
				$this->return_error(403, '该订单已经支付完成');
			} else {
				$recharge->bmob_order_id = $bmob_order_id;

				if (Recharge::store($recharge)) {
					$handle = curl_init(self::CALLBACK_URL);
					curl_setopt($handle, CURLOPT_POST, true);
					curl_setopt($handle, CURLOPT_POSTFIELDS, [
							'trade_status' => 1,
							'out_trade_no' => $bmob_order_id,
							'trade_no' => 4003602001201704136848217179
					]);
					$ret = curl_exec($handle);
					curl_close($handle);

					$this->return_success();
				} else {
					$this->return_error();
				}
			}
		}
	}

	/**
	 * 手动同步订单状态
	 */
	public function updateOrderStatus()
	{
		$id = $_POST['id'];

		$recharge = Recharge::findOne('recharges', ' id = ? ', [ $id ]);
			if (empty($recharge)) {
				$this->return_error(401, '订单不存在');
			} elseif ($recharge->status == self::STATUS_PAYED) {
				$this->return_error(403, '该订单已经支付完成');
			} else {
				$handle = curl_init(self::CALLBACK_URL);
				curl_setopt($handle, CURLOPT_POST, true);
				curl_setopt($handle, CURLOPT_POSTFIELDS, [
						'trade_status' => 1,
						'out_trade_no' => $recharge->bmob_order_id,
						'trade_no' => 4003602001201704136848217179
				]);
				$ret = curl_exec($handle);
				curl_close($handle);

				$this->return_success();
			}
	}

	/**
	 * 导出csv
	 */
	public function export()
	{
		$recharges = Recharge::findAll('recharges', ' status = ? ', [ self::STATUS_PAYED ]);
		$data = [];
		if (!empty($recharges)) {
			foreach ($recharges as $k => $v) {
				$data[] = [
					'id' => $v->id,
					'uid' => $v->uid,
					'pid' => $v->pid,
					'money' => sprintf('%.2f', $v->money/100) . '元',
					'content' => $v->amount . ($v->type == self::TYPE_SCORE ? '积分' : '个月VIP'),
					'pay_type' => $v->pay_type == self::PAY_TYPE_WECHAT ? '微信支付' : $v->pay_type == self::PAY_TYPE_ALIPAY ? '支付宝' : '其他',
					'channel' => $v->channel,
					'date' => $v->created_at,
				];
			}
		}

		setCsvDownloadHeader("空间点赞大师充值订单.csv");
		echo array2CSV($data);
		
		return;
	}
}
