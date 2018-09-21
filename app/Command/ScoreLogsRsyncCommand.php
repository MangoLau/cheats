<?php
// 同步积分变动记录
namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Model\Attendance;
use App\Model\Cheat;
use App\Model\Recharge;
use App\Model\Order;
use App\Model\ScoreLog;

class ScoreLogsRsyncCommand extends BaseCommand
{
	CONST TYPE_SCORE = 1;						// 积分类型
	CONST TYPE_VIP = 2;							// VIP类型

	CONST PAY_STATUS_YES = 1;					// 已支付
	CONST PAY_STATUS_NO = 0;					// 未支付

	CONST TYPE_ATTENDANCE = 1;					// 签到赠送
	CONST TYPE_RECHARGE = 2;					// 充值积分
	CONST TYPE_VIP_PRESENT = 3;					// 充值VIP赠送
	CONST TYPE_ORDER = 4;						// 下单消费

	protected function configure()
	{
		$this->setName('app:score-logs-rsync')
			 ->setDescription('同步积分变动记录')
			 ->setHelp('全量读取签到表、充值表、订单表，写入积分变动记录表');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		ScoreLog::wipe( 'scorelogs' );

		// 签到积分赠送
		$attendances = Attendance::findAll('attendances');
		if (foreachAble($attendances)) {
			foreach ($attendances as $k => $a) {
				$score_log = ScoreLog::dispense('scorelogs');
				$score_log->uid = $a->uid;
				$score_log->type = self::TYPE_ATTENDANCE;
				$score_log->amount = $a->scores;
				$score_log->created_at = $a->created_at;

				ScoreLog::store($score_log);
			}
		}

		// 充值
		$recharges = Recharge::findAll('recharges', ' status = ? ', [ self::PAY_STATUS_YES ]);
		if (foreachAble($recharges)) {
			foreach ($recharges as $k => $r) {
				if ($r->type == self::TYPE_SCORE) {
					$score_log = ScoreLog::dispense('scorelogs');
					$score_log->uid = $r->uid;
					$score_log->type = self::TYPE_RECHARGE;
					$score_log->amount = $r->amount;
					$score_log->created_at = $r->created_at;

					ScoreLog::store($score_log);
				}
			}
		}

		// 刷赞等订单
		$orders = Order::findAll('orders');
		if (foreachAble($orders)) {
			$cheats = Cheat::findAll('cheats');
			foreach ($orders as $k => $o) {
				$score_log = ScoreLog::dispense('scorelogs');
				$score_log->uid = $o->uid;
				$score_log->type = self::TYPE_ORDER;
				$score_log->amount = $o->scores;
				$score_log->remark = $cheats[$o->cid]->title;
				$score_log->created_at = $o->created_at;

				ScoreLog::store($score_log);
			}
		}
	}
}