<?php

namespace App\Controller;

use App\Model\Attendance;
use App\Model\User;
use App\Model\Config;
use App\Model\ScoreLog;

class AttendanceController extends BaseController
{
	/**
	 * 签到列表
	 */
	public function index()
	{
		$paging_data = $this->getPageCount();
		$attendances = array_values(Attendance::findAll('attendances', ' ORDER BY ID DESC LIMIT ?, ? ', [ $paging_data['start'], $paging_data['count'] ]));
		$ret = [];

		if (foreachAble($attendances)) {
			foreach ($attendances as $k => $attendance) {
				$ret[$k]['id'] = $attendance->id;
				$ret[$k]['day'] = $attendance->created_day;
			}
		}

		$this->return_success($ret);
	}

	/**
	 * 签到
	 */
	public function create()
	{
		$user = User::findOne('users', ' id = ? ', [ $this->token->uid ]);

		if (empty($user)) {
			$this->return_error();
		} else {
			$today = date('Ymd', getCurrentTime());
			$attendance = Attendance::findOne('attendances', ' uid = ? AND created_day = ? ', [ $user->id, $today ]);

			if (empty($attendance)) {
				$attendance = Attendance::dispense('attendances');
				$attendance->uid = $user->id;
				$attendance->created_day = $today;

				if (Attendance::store($attendance)) {
					// 增加积分
					if ($user->vip_deadline < getCurrentTime()) {
						$scores = Config::getCommonAttendanceScore();
					} else {
						$scores = Config::getVipAttendanceScore();
					}

					$user->total_scores += $scores;
					$user->remaining_scores += $scores;

					if (User::store($user)) {
						// 将增加的积分纪录也写入签到表
						$attendance->scores = $scores;
						if (!Attendance::store($attendance)) {
							$this->error('attendance table add scores failed', (array)$attendance);
						} else {
							// 积分变动记录
							ScoreLog::attendancePresent($user->id, $scores);
						}

						$this->return_success();
					} else {
						$this->error('attendance increase scores failed', [ $scores, (array)$user ]);
						$this->return_error();
					}
				} else {
					$this->return_error();
				}
			} else {
				$this->return_error(301, '今天您已经签过到了');
			}
		}
	}
}

