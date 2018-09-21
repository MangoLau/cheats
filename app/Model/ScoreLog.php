<?php

namespace App\Model;

class ScoreLog extends Base
{
	CONST TYPE_ATTENDANCE = 1;					// 签到赠送
	CONST TYPE_RECHARGE = 2;					// 充值积分
	CONST TYPE_VIP_PRESENT = 3;					// 充值VIP赠送
	CONST TYPE_ORDER = 4;						// 下单消费

	// 签到赠送积分
	public static function attendancePresent($uid = 0, $amount = 0)
	{
		return self::createOne($uid, self::TYPE_ATTENDANCE, $amount);
	}

	// 充值积分
	public static function recharge($uid = 0, $amount = 0)
	{
		return self::createOne($uid, self::TYPE_RECHARGE, $amount);
	}

	// 充值VIP赠送积分
	public static function vipPresent($uid = 0, $amount = 0)
	{
		return self::createOne($uid, self::TYPE_VIP_PRESENT, $amount);
	}

	// 刷赞等等消耗积分
	public static function orderSpending($uid = 0, $amount = 0, $remark = '')
	{
		return self::createOne($uid, self::TYPE_ORDER, $amount, $remark);
	}

	public static function createOne($uid, $type, $amount, $remark = '')
	{
		$score_log = self::dispense('scorelogs');
		$score_log->uid = $uid;
		$score_log->type = $type;
		$score_log->amount = $amount;
		$score_log->remark = $remark;

		return self::store($score_log);
	}
}