<?php
// 充值VIP每月赠送积分
namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Model\User;
use App\Model\Config;
use App\Model\ScoreLog;

class VipPresentScoresCommand extends BaseCommand
{
	CONST TYPE_SCORE = 1;						// 积分类型
	CONST TYPE_VIP = 2;							// VIP类型

	CONST PAY_STATUS_YES = 1;					// 已支付
	CONST PAY_STATUS_NO = 0;					// 未支付

	protected function configure()
	{
		$this->setName('app:vip-month-present-scores')
			 ->setDescription('充值VIP每月赠送积分')
			 ->setHelp('充VIP时当月立即赠送积分，剩下几个月都在当月一号赠送积分');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// 每次处理1000个
		$count = 1000;

		// 下个月一号零点的时间戳
		$next_month = strtotime(date("Ymd", strtotime("first day of next month")));

		// 所有符合条件的用户的数量
		$total_count = User::count('users', ' WHERE vip_deadline >= ? ', [ $next_month ]);
		$total_times = ceil($total_count/$count);

		// 每月赠送积分数
		$each_month_present_scores = Config::getVipMonthPresentScores();

		for ($i = 0; $i < $total_times; $i++) {
			// 判断该月是否需要继续赠送积分(下个月是否还是VIP)
			$users = User::findAll('users', ' WHERE vip_deadline >= ? ORDER BY id ASC LIMIT ?, ? ', [ $next_month, $i * $count, $count ]);

			$this->logger->AddDebug('vip-month-present-scores info', [ $i, count($users), $each_month_present_scores ]);

			if (!empty($users) && foreachAble($users) && !empty($each_month_present_scores)) {
				foreach ($users as $k => $user) {
					$user->total_scores += $each_month_present_scores;
					$user->remaining_scores += $each_month_present_scores;
					$user->updated_at = new \Datetime;
	
					if (User::store($user)) {
						$this->logger->AddDebug('vip-month-present-scores success', [ $user, $each_month_present_scores, date("Ymd H:i:s", getCurrentTime()) ]);

						// 积分记录
						ScoreLog::vipPresent($user->id, $each_month_present_scores);
					} else {
						$this->logger->AddError('vip-month-present-scores store user failed', [ $user, $each_month_present_scores, date("Ymd H:i:s", getCurrentTime()) ]);
					}
				}
			}
		}

		
	}
}