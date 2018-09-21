<?php
/**
 * 2017-05-27 15:00
 * 邀请功能的bug导致未给用户增加积分，影响至`scorelogs`表 id<=20795 && (type == 6 || type ==7) 的所有用户
 * 此脚本将漏掉的数据跑上，请勿重复执行
 */
namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Model\ScoreLog;
use App\Model\User;

class AddMissedScoresCommand extends BaseCommand
{
	CONST TYPE_ATTENDANCE = 1;					// 签到赠送
	CONST TYPE_RECHARGE = 2;					// 充值积分
	CONST TYPE_VIP_PRESENT = 3;					// 充值VIP赠送
	CONST TYPE_ORDER = 4;						// 下单消费
	CONST TYPE_INVITE = 6;						// 邀请
	CONST TYPE_INVITED = 7;						// 被邀请

	protected function configure()
	{
		$this->setName('app:add-missed-scores')
			 ->setDescription('补上漏掉的积分')
			 ->setHelp('邀请功能的bug导致未给用户增加积分');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$scorelogs = ScoreLog::findAll('scorelogs', ' id <= ? AND type >= ? ', [ 20795, self::TYPE_INVITE ]);
		if (is_array($scorelogs)) {
			foreach ($scorelogs as $k => $v) {
				$this->logger->addDebug('start adding scores', [ $v ]);
				$user = User::findOne('users', ' id = ? ', [ $v->uid ]);
				$user->total_scores += $v->amount;
				$user->remaining_scores += $v->amount;
				if (User::store($user)) {
					$this->logger->addDebug('end adding scores', []);
				} else {
					$this->logger->addDebug('failed adding scores', []);
				}
			}
		}
	}
}