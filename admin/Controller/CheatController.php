<?php

namespace Admin\Controller;

use Admin\Model\Cheat;

class CheatController extends BaseController
{
	const STATUS_ONLINE = 1;		// 启用
	const STATUS_OFFLINE = 0;		// 未启用

	// 分类
	const CATEGORY_QZONE = 1;			// Qzone
	const CATEGORY_KUAISHOU = 2;		// 快手
	const CATEGORY_KG = 3;				// K歌

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/cheats';
		$this->search_desc = '请输入title';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Cheat::count('cheats');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Cheat::count('cheats', 'title = ?', [ $page_data['search'] ]);

				// 列表
				$cheats = Cheat::findAll('cheats', 'title = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$cheats = Cheat::findAll('cheats', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($cheats as $k => $cheat) {
				$cheats[$k]['remark'] = $cheat->remark ?: '无';
				$cheats[$k]['status'] = $cheat->status == self::STATUS_ONLINE ? '启用' : '停用';
				$cheats[$k]['category'] = $cheat->category == self::CATEGORY_QZONE ? 'QQ空间专区' : ( $cheat->category == self::CATEGORY_KUAISHOU ? '快手专区' : 'K歌专区' );
			}

			$this->json_encode_output(array('data' => array_values($cheats), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '刷赞类型列表';

			$this->render('cheat/list', array('title' => $title));
		}
	}

	/**
	 * 更新URL
	 * home: http://www.1gege.cn/index.php?m=Home&c=goods&a=detail&id=15&goods_type=6
	 * login: http://www.1gege.cn/index.php?m=Home&c=Card&a=login&id=15&goods_type=6
	 * url: http://www.1gege.cn/index.php?m=home&c=order&a=add&id=15&goods_type=6
	 * cardinfo: http://www.1gege.cn/index.php?m=Home&c=Card&a=cardinfo_no&id=15&goods_type=6
	 * progress: http://www.1gege.cn/index.php?m=home&c=order&a=orderlist_dtGrid&goods_id=15&goods_type=6 
	 */
	public function updateUrl()
	{
		$id = $_POST['id'];
		$home_url = $_POST['home_url'];

		if (empty($id) || empty($home_url)) {
			$this->return_error(400, '参数不完整');
		} elseif (!filter_var($home_url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
			$this->return_error(401, '链接不合法');
		} else {
			$cheat = Cheat::findOne('cheats', ' id = ? ', [ $id ]);
			if (empty($cheat)) {
				$this->return_error();
			} else {
				if ($cheat->home_url == $home_url) {
					$this->return_success();
				} else {
					$url_info = parse_url($home_url);
					parse_str($url_info['query'], $query_info);
					$host = $url_info['scheme'] . '://' . $url_info['host'] . $url_info['path'];

					$login_url = $host . '?m=' . $query_info['m'] . '&c=Card&a=login&id=' . $query_info['id'] . '&goods_type=' . $query_info['goods_type'];
					$url = $host . '?m=' . $query_info['m'] . '&c=order&a=add&id=' . $query_info['id'] . '&goods_type=' . $query_info['goods_type'];
					$cardinfo_url = $host . '?m=' . $query_info['m'] . '&c=Card&a=cardinfo_no&id=' . $query_info['id'] . '&goods_type=' . $query_info['goods_type'];
					$progress_url = $host . '?m=' . $query_info['m'] . '&c=order&a=orderlist_dtGrid&id=' . $query_info['id'] . '&goods_type=' . $query_info['goods_type'];

					$cheat->home_url = $home_url;
					$cheat->login_url = $login_url;
					$cheat->url = $url;
					$cheat->cardinfo_url = $cardinfo_url;
					$cheat->progress_url = $progress_url;
					$cheat->updated_at = new \Datetime;

					if (Cheat::store($cheat)) {
						$this->return_success();
					} else {
						$this->return_error();
					}
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

		$cheat = Cheat::findOne('cheats', ' id = ? ', [ $id ]);
		if (!empty($cheat)) {
			// 状态判断
			if ($cheat->status == self::STATUS_OFFLINE) {
				$cheat->status = self::STATUS_ONLINE;
				$cheat->updated_at = new \Datetime;
				if (Cheat::store($cheat)) {
					$this->return_success();
				} else {
					$this->error('update cheat failed', (array)$cheat);
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

		$cheat = Cheat::findOne('cheats', ' id = ? ', [ $id ]);
		if (!empty($cheat)) {
			// 状态判断
			if ($cheat->status == self::STATUS_ONLINE) {
				$cheat->status = self::STATUS_OFFLINE;
				$cheat->updated_at = new \Datetime;
				if (Cheat::store($cheat)) {
					$this->return_success();
				} else {
					$this->error('update cheat failed', (array)$cheat);
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
