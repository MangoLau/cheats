<?php # 签到

namespace Admin\Controller;

use Admin\Model\Attendance;

class AttendanceController extends BaseController
{
	// 列表
	public function index()
	{
		$this->ajax_api = '/attendances';
		$this->search_desc = '请输入UID';
		if ($this->isAjax()) {
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Attendance::count('attendances');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Attendance::count('attendances', ' uid = ?', [ $page_data['search'] ]);

				// 列表
				$attendances = Attendance::findAll('attendances', ' uid = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$attendances = Attendance::findAll('attendances', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			$this->json_encode_output(array('data' => array_values($attendances), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));
		} else {
			$title = '签到列表';

			$this->render('attendance/list', array('title' => $title));
		}
	}
}