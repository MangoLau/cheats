<?php
/**
 * 
 */
namespace App\Controller;

use \RedBeanPHP\R;

class ComplaintController extends BaseController
{
	// 反馈
	public function create()
	{
		$qq = trim($_POST['qq']);
		$mobile = substr(trim($_POST['mobile']), 0, 11);
		$remark = trim($_POST['remark']);

		if (empty($qq) || empty($mobile)) {
			$this->return_error(400, '参数不完整');
		} else {
			$complaint = R::dispense('complaints');
			$complaint->uid = $this->token->uid;
			$complaint->qq = $qq;
			$complaint->mobile = $mobile;
			$complaint->channel = $this->channel;
			$complaint->remark = $remark;

			if (R::store($complaint)) {
				$this->return_success();
			} else {
				$this->return_error();
				$this->error('Save complaint failed!', array($complaint));
			}
		}
	}

	// 列表
	public function index()
	{
		$paging_data = $this->getPageCount();

		$complaints = R::findAll('complaints', ' uid = ? ORDER BY id DESC LIMIT ?, ? ', [ $this->token->uid, $paging_data['start'], $paging_data['count'] ]);

		if (!empty($complaints)) {
			foreach ($complaints as $k => &$v) {
				unset($v['channel'], $v['uid'], $v['status'], $v['remark'], $v['updated_at']);
			}
		}

		$this->return_success(array_values($complaints));
	}
}
