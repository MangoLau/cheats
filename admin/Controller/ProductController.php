<?php

namespace Admin\Controller;

use Admin\Model\Product;

class ProductController extends BaseController
{
	CONST STATUS_ONLINE = 1;		// 启用
	CONST STATUS_OFFLINE = 0;		// 未启用

	CONST TYPE_SCORE = 1;			// 积分
	CONST TYPE_VIP = 2;				// VIP

	/**
	 * 列表
	 */
	public function index()
	{
		$this->ajax_api = '/products';
		$this->search_desc = '请输入money';
		if ($this->isAjax()) {
			// datatable插件参数
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Product::count('products');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Product::count('products', ' money = ?', [ $page_data['search'] ]);

				// 列表
				$products = Product::findAll('products', ' money = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$products = Product::findAll('products', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			foreach ($products as $k => $product) {
				$products[$k]['origin_type'] = $product->type;
				$products[$k]['type'] = $product->type == self::TYPE_SCORE ? '积分' : 'VIP(月)';
				$products[$k]['status'] = $product->status == self::STATUS_ONLINE ? '启用' : '停用';
			}

			$this->json_encode_output(array('data' => array_values($products), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));

		} else {
			$title = '充值产品列表';

			$this->render('product/list', array('title' => $title));
		}
	}

	/**
	 * 增加产品
	 */
	public function add()
	{
		$money = intval($_POST['money']);
		$type = $_POST['type'] == self::TYPE_SCORE ? self::TYPE_SCORE : self::TYPE_VIP;
		$amount = intval($_POST['amount']);

		if ($money <= 0 || $amount <= 0) {
			$this->return_error(400, '参数不合法');
		} else {
			$product = Product::dispense('products');
			$product->money = $money;
			$product->type = $type;
			$product->amount = $amount;
			$product->status = self::STATUS_ONLINE;

			if (Product::store($product)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 更新产品
	 */
	public function update()
	{
		$id = $_POST['id'];
		$money = $_POST['edit-money'];
		$type = $_POST['edit-type'];
		$amount = $_POST['edit-amount'];

		if (empty($id) || empty($money) || empty($amount) || !in_array($type, [ self::TYPE_SCORE, self::TYPE_VIP ])) {
			$this->return_error(400, '参数不合法');
		} else {
			$product = Product::findOne('products', ' id = ? ', [ $id ]);
			$product->money = $money;
			$product->type = $type;
			$product->amount = $amount;
			$product->updated_at = new \Datetime;

			if (Product::store($product)) {
				$this->return_success();
			} else {
				$this->return_error();
			}
		}
	}

	/**
	 * 上线product
	 */
	public function pushOnline()
	{
		$id = $this->getRequestID();

		$product = Product::findOne('products', ' id = ? ', [ $id ]);
		if (!empty($product)) {
			// 状态判断
			if ($product->status == self::STATUS_OFFLINE) {
				$product->status = self::STATUS_ONLINE;
				$product->updated_at = new \Datetime;
				if (Product::store($product)) {
					$this->return_success();
				} else {
					$this->error('update product failed', (array)$product);
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
	 * 下线product
	 */
	public function pushOffline()
	{
		$id = $this->getRequestID();

		$product = Product::findOne('products', ' id = ? ', [ $id ]);
		if (!empty($product)) {
			// 状态判断
			if ($product->status == self::STATUS_ONLINE) {
				$product->status = self::STATUS_OFFLINE;
				$product->updated_at = new \Datetime;
				if (Product::store($product)) {
					$this->return_success();
				} else {
					$this->error('update product failed', (array)$product);
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
