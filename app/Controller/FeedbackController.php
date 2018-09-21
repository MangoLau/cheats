<?php
/**
 * 用户反馈
 */
namespace App\Controller;

use \RedBeanPHP\R;

class FeedbackController extends BaseController
{
	const FEEDBACK_STATUS_DEALING = 0;
	const FEEDBACK_STATUS_SUCCESS = 1;
	const FEEDBACK_STATUS_FAILED = 2;
	const FEEDBACK_STATUS_RELIED = 3;

	// 反馈
	public function create()
	{
		$content = trim($_POST['content']);

		if (empty($content)) {
			$this->return_error(400, '反馈内容不能为空');
		} else {
			$feedback = R::dispense('feedbacks');
			$feedback->uid = $this->token->uid;
			$feedback->text_content = $content;
			$feedback->base64_content = base64_encode($content);

			if (R::store($feedback)) {
				$this->return_success();
			} else {
				$this->return_error();
				$this->error('Save feedback failed!', array($feedback));
			}
		}
	}

	// 列表
	public function index()
	{
		$paging_data = $this->getPageCount();

		$feedbacks = R::findAll('feedbacks', ' (status = ? || status = ?) AND uid != 0 ORDER BY id DESC LIMIT ?, ? ', [ self::FEEDBACK_STATUS_SUCCESS, self::FEEDBACK_STATUS_RELIED, $paging_data['start'], $paging_data['count'] ]);
		$ret = [];

		if (!empty($feedbacks)) {
			$replied_ids = [];
			$user_ids = [];
			foreach ($feedbacks as $k => $v) {
				if ($v->status == self::FEEDBACK_STATUS_RELIED) {
					$replied_ids[] = $v->id;
				}

				$user_ids[] = $v->uid;
			}

			$users = R::findAll('users', ' id in ( ' . implode(',', $user_ids) . ' ) ', []);

			if (!empty($replied_ids)) {
				$admin_feedbacks = R::findAll('feedbacks', ' reply_id in (' . implode(',', $replied_ids) . ') ', []);
				if (!empty($admin_feedbacks)) {
					$tmp = [];

					foreach ($admin_feedbacks as $k => $v) {
						$tmp[$v->reply_id] = $v;
					}

					$admin_feedbacks = $tmp;
				}
			}

			foreach ($feedbacks as $k => $v) {
				$tmp = [];
				unset($v->status, $v->base64_content, $v->updated_at);
				$tmp['data'] = [];
				$tmp['comment'] = [];

				if (isset($users[$v->uid])) {
					$tmp['data']['content'] = $v->text_content;
					$tmp['data']['created_at'] = $v->created_at;
					$tmp['data']['nickname'] = $users[$v->uid]['nickname'];
					$tmp['data']['avatar'] = $users[$v->uid]['avatar'];
				}

				if (isset($admin_feedbacks[$v->id])) {
					$tmp['comment']['content'] = $admin_feedbacks[$v->id]['text_content'];
					$tmp['comment']['created_at'] = $admin_feedbacks[$v->id]['created_at'];
				} else {
					$tmp['comment'] = null;
				}

				$ret[] = $tmp;
			}
		}

		$this->return_success($ret);
	}
}
