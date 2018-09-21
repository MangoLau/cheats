<?php

namespace App\Model;

/**
* config
*/
class Config extends Base
{
	CONST STATUS_ONLINE = 1;
	CONST STATUS_OFFLINE = 0;

	// 普通用户签到获取的积分
	public static function getCommonAttendanceScore()
	{
		return self::getConfig('common_user_attendance_scores');
	}

	// VIP用户签到获取的积分
	public static function getVipAttendanceScore()
	{
		return self::getConfig('vip_user_attendance_scores');
	}

	// 充值VIP时每月赠送积分数
	public static function getVipMonthPresentScores()
	{
		return self::getConfig('vip_month_present_scores');
	}

	// VIP用户购买积分的折扣, 百分比
	public static function getVipRechargeDiscount()
	{
		return self::getConfig('vip_recharge_discount');
	}

	// 用户应用市场五星好评赠送的积分
	public static function getFiveStarsCommentScores()
	{
		return self::getConfig('five_comment');
	}

	// 邀请赠送积分
	public static function getInviteFriendScores()
	{
		return self::getConfig('invite_friend');
	}

	// 被邀请赠送积分
	public static function getInvitedScores()
	{
		return self::getConfig('invited_friend');
	}

	// 首充大礼包配置
	public static function getFirstRechargeParams()
	{
		return self::getConfig('first_recharge');
	}

	// 支付配置
	public static function getPayChoice()
	{
		return self::getConfig('pay_choice');
	}

	// 微信转账支付优惠
	public static function getWechatTransferPayDiscount()
	{
		return self::getConfig('wechat_discount');
	}

	// 钱进支付的支付方式
	// public static function getQjPayType()
	// {
	// 	return self::getConfig('qj_pay_type');
	// }

	// 获取配置
	private static function getConfig($key)
	{
		$config = self::findOne('configs', ' `key` = ? AND status = ? ', [ $key, self::STATUS_ONLINE ]);
		$val = '';

		if (!empty($config)) {
			$val = $config->value;
		}

		return $val;
	}
}
