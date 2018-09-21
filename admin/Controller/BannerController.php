<?php

namespace Admin\Controller;

use Admin\Model\Banner;

class BannerController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	/**
	 * 上线列表
	 */
	public function index()
	{
		$this->ajax_api = '/banners';
		$this->search_desc = '请输入link';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Banner::count('banners');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Banner::count('banners', ' link = ?', [ $page_data['search'] ]);

				// 列表
				$banners = Banner::findAll('banners', ' link = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$banners = Banner::findAll('banners', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($banners as $k => $banner) {
				$banners[$k]['status'] = $banner->status == self::STATUS_ONLINE ? '启用' : '停用';
			}

			$this->json_encode_output(array('data' => array_values($banners), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = 'banner列表';

			$this->render('banner/list', array('title' => $title));
		}
	}

	/**
	 * 增加产品
	 */
	public function add()
	{
		$pic = $_POST['pic'];
		$link = $_POST['link'];
		$remark = $_POST['remark'];

		if (empty($pic) || empty($link) || empty($remark)) {
			$this->return_error(400, '参数不合法');
		} else {
			$banner = Banner::dispense('banners');
			$banner->pic = $pic;
			$banner->link = $link;
			$banner->remark = $remark;
			$banner->status = self::STATUS_ONLINE;

			if (Banner::store($banner)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 上线banner
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$banner = Banner::findOne('banners', ' id = ? ', [ $id ]);
		if (!empty($banner)) {
			// 状态判断
			if ($banner->status == self::STATUS_OFFLINE) {
				$banner->status = self::STATUS_ONLINE;
				$banner->updated_at = new \Datetime;
				if (Banner::store($banner)) {
					$this->return_success();
				} else {
					$this->error('update banner failed', (array)$banner);
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
	 * 下线banner
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$banner = Banner::findOne('banners', ' id = ? ', [ $id ]);
		if (!empty($banner)) {
			// 状态判断
			if ($banner->status == self::STATUS_ONLINE) {
				$banner->status = self::STATUS_OFFLINE;
				$banner->updated_at = new \Datetime;
				if (Banner::store($banner)) {
					$this->return_success();
				} else {
					$this->error('update banner failed', (array)$banner);
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
