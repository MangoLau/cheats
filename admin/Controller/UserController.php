<?php

namespace Admin\Controller;

use Admin\Model\User;
use Admin\Model\Qrcode;

class UserController extends BaseController
{
	// 用户列表
	public function index()
	{
		$this->ajax_api = '/users';
		$this->search_desc = '请输入ID';
		if ($this->isAjax()) {
			$start_count = $this->getStartCount();
			// 总数
			$recordsTotal = User::count('users');
			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($start_count['search'])) {
				$recordsFiltered = User::count('users', ' id = ? ', [ $start_count['search'] ]);
				$users = User::findAll('users', ' id = ?' . $start_count['order_by'] . ' LIMIT ?, ?', [ $start_count['search'], $start_count['start'], $start_count['count'] ]);
			} else {
				$users = User::findAll('users', $start_count['order_by'] . ' LIMIT ?, ?', [ $start_count['start'], $start_count['count'] ]);
			}

			foreach ($users as $k => $v) {
				$users[$k]->avatar = getAvatarUrl($v->avatar);
				$users[$k]->vip_deadline = $v->vip_deadline >= getCurrentTime() ? '至 ' . date('Y-m-d H:i:s', $v->vip_deadline) : '无';
			}
						
			$this->json_encode_output(array('data' => array_values($users), 'draw' => intval($start_count['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));
		} else {
			$title = '列表';
			$this->render('user/list', array('title' => $title));
		}
	}
}