<?php

namespace Admin\Controller;

use Admin\Model\Complaint;

class ComplaintController extends BaseController
{
	// 用户列表
	public function index()
	{
		$this->ajax_api = '/complaints';
		$this->search_desc = '请输入qq';
		if ($this->isAjax()) {
			$start_count = $this->getStartCount();
			// 总数
			$recordsTotal = Complaint::count('complaints');
			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($start_count['search'])) {
				$recordsFiltered = Complaint::count('complaints', ' qq = ? ', [ $start_count['search'] ]);
				$complaints = Complaint::findAll('complaints', ' qq = ?' . $start_count['order_by'] . ' LIMIT ?, ?', [ $start_count['search'], $start_count['start'], $start_count['count'] ]);
			} else {
				$complaints = Complaint::findAll('complaints', $start_count['order_by'] . ' LIMIT ?, ?', [ $start_count['start'], $start_count['count'] ]);
			}

			foreach ($complaints as $k => $v) {
				$complaints[$k]->avatar = getAvatarUrl($v->avatar);
			}
						
			$this->json_encode_output(array('data' => array_values($complaints), 'draw' => intval($start_count['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));
		} else {
			$title = '列表';
			$this->render('complaint/list', array('title' => $title));
		}
	}
}