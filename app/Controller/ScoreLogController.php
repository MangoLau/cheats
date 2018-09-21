<?php

namespace App\Controller;

use App\Model\User;
use App\Model\ScoreLog;

/**
 * 积分变动记录
 */
class ScoreLogController extends BaseController
{
	const TYPE_ATTENDANCE = 1;					// 签到赠送
	const TYPE_RECHARGE = 2;					// 充值积分
	const TYPE_VIP_PRESENT = 3;					// 充值VIP赠送
	const TYPE_ORDER = 4;						// 下单消费
	const TYPE_PRAISE = 5;						// 应用市场好评赠送积分
	const TYPE_INVITE = 6;						// 邀请赠送
	const TYPE_INVITED = 7;						// 被邀请赠送
	const TYPE_RETURN = 8;						// 退还积分

	/**
	 * 积分获取纪录
	 */
	public function index()
	{
		$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);

		if (empty($user)) {
			$this->return_error();
		} else {
			$month = isset($_GET['month']) ? $_GET['month'] : '';

			// 月份查询时不分页
			if (!empty($month)) {
				// month参数验证
				if ($month < 201702 || $month > date("Ym", strtotime("+1 month"))) {
					$this->return_success([]);
				} else {
					$start_time = substr($month, 0, 4) . '-' . substr($month, -2) . '-01 00:00:00';
					$end_time = date('Y-m-d H:i:s', strtotime("+1 month", strtotime($month . '01')));

					$score_logs = ScoreLog::findAll('scorelogs', ' uid = ? AND created_at >= ? AND created_at < ? ', [ $user->id, $start_time, $end_time ]);
					$data = [];
					if (foreachAble($score_logs)) {
						foreach ($score_logs as $k => $score_log) {
							$tmp = [];
							$tmp['type'] = $score_log->type;
							switch ($score_log->type) {
								case self::TYPE_RECHARGE:
									$tmp['remark'] = '充值';
									break;
								
								case self::TYPE_ATTENDANCE:
									$tmp['remark'] = '签到赠送';
									break;

								case self::TYPE_VIP_PRESENT:
									$tmp['remark'] = '购买VIP赠送';
									break;

								case self::TYPE_PRAISE:
									$tmp['remark'] = '应用市场五星好评赠送';
									break;

								case self::TYPE_INVITE:
									$tmp['remark'] = '邀请用户赠送';
									break;

								case self::TYPE_INVITED:
									$tmp['remark'] = '绑定邀请者赠送';
									break;

								case self::TYPE_RETURN:
									$tmp['remark'] = '退还积分';
									break;

								default:
									$tmp['remark'] = $score_log->remark;
									break;
							}
							$tmp['scores'] = ($score_log->type == self::TYPE_ORDER ? '-' : '+') . strval($score_log->amount);
							$tmp['created_at'] = strtotime($score_log->created_at);

							array_push($data, $tmp);
						}
					}

					usort($data, function($a, $b){ return $a['created_at'] >= $b['created_at']; });

					$this->return_success($data);
				}
			} else {
				$this->return_error(400, '缺少月份参数');
			}
		}
	}
}