<?php

namespace Admin\Controller;

use Admin\Model\Feedback;

class FeedbackController extends BaseController
{
	const FEEDBACK_STATUS_DEALING = 0;
	const FEEDBACK_STATUS_SUCCESS = 1;
	const FEEDBACK_STATUS_FAILED = 2;
	const FEEDBACK_STATUS_RELIED = 3;

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/feedbacks';
		$this->search_desc = '请输入uid';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Feedback::count('feedbacks');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Feedback::count('feedbacks', ' uid = ? ', [ $page_data['search'] ]);

				// 列表
				$feedbacks = Feedback::findAll('feedbacks', ' uid = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$feedbacks = Feedback::findAll('feedbacks', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($feedbacks as $k => $feedback) {
				$feedbacks[$k]['origin_status'] = $feedback['status'];
				$tmp_status = '';
				switch ($feedback['status']) {
					case self::FEEDBACK_STATUS_DEALING:
						$tmp_status = '未审核';
						break;
					case self::FEEDBACK_STATUS_SUCCESS:
						$tmp_status = '审核通过';
						break;
					case self::FEEDBACK_STATUS_FAILED:
						$tmp_status = '审核失败';
						break;
					case self::FEEDBACK_STATUS_RELIED:
						$tmp_status = '已回复';
						break;
					
					default:
						# code...
						break;
				}

				$feedbacks[$k]['status'] = $tmp_status;
			}

			$this->json_encode_output(array('data' => array_values($feedbacks), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '留言列表';

			$this->render('feedback/list', array('title' => $title));
		}
	}

	/**
	 * 回复
	 */
	public function reply()
	{
		$id = $_POST['id'];
		$content = trim($_POST['reply_content']);

		if (empty($id) || empty($content)) {
			$this->return_error(400, '参数不完整');
		} else {
			$o_feedback = Feedback::findOne('feedbacks', ' id = ? ', [ $id ]);

			if (empty($o_feedback)) {
				$this->return_error(402, '留言不存在');
			} else {
				$feedback = Feedback::dispense('feedbacks');
				$feedback->reply_id = $id;
				$feedback->uid = 0;
				$feedback->text_content = $content;
				$feedback->base64_content = base64_encode($content);
				$feedback->status = self::FEEDBACK_STATUS_SUCCESS;

				if (Feedback::store($feedback)) {
					$o_feedback->status = self::FEEDBACK_STATUS_RELIED;
					Feedback::store($o_feedback);

					$this->return_success();
				} else {
					$this->return_error();
				}
			}
		}
	}

	/**
	 * 上线
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$feedback = Feedback::findOne('feedbacks', ' id = ? ', [ $id ]);
		if (!empty($feedback)) {
			// 状态判断
			if ($feedback->status == self::FEEDBACK_STATUS_DEALING) {
				$feedback->status = self::FEEDBACK_STATUS_SUCCESS;
				$feedback->updated_at = new \Datetime;
				if (Feedback::store($feedback)) {
					$this->return_success();
				} else {
					$this->error('update feedback failed', (array)$feedback);
					$this->return_error();
				}
			} else {
				$this->return_success();
			}
		} else {
			$this->return_error(401, '非法请求');
		}
	}

	/**
	 * 下线
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$feedback = Feedback::findOne('feedbacks', ' id = ? ', [ $id ]);
		if (!empty($feedback)) {
			// 状态判断
			if ($feedback->status == self::FEEDBACK_STATUS_DEALING) {
				$feedback->status = self::FEEDBACK_STATUS_FAILED;
				$feedback->updated_at = new \Datetime;
				if (Feedback::store($feedback)) {
					$this->return_success();
				} else {
					$this->error('update feedback failed', (array)$feedback);
					$this->return_error();
				}
			} else {
				$this->return_success();
			}
		} else {
			$this->return_error(401, '非法请求');
		}
	}
}
