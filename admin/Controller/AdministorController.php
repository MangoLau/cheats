<?php

namespace Admin\Controller;

use Admin\Model\Administor;

class AdministorController extends BaseController
{
	// 登录
	public function login()
	{
		// 简单登录
		if (!empty($_POST)) {
			$username = trim($_POST['username']);
			$password = trim($_POST['password']);
			$remember_me = trim($_POST['remember_me']);

			$administor = Administor::findOne('administors', 'username = ?', [$username]);
			if (empty($administor)) {
				$this->return_error(101, '用户名或密码错误');
			} else {
				if (!password_verify($password, $administor->password)) {
					$this->return_error(101, '用户名或密码错误');
				} else {
					$administor->last_login_at = getCurrentTime();

					// 更新最后登录时间
					if (!Administor::store($administor)) {
						$this->error('admin login update last_login_at error', $_POST);
					}

					// 登录信息
					$_SESSION['uid'] = $administor->id;
					$_SESSION['userinfo'] = array('username' => $administor->username, 'last_login_at' => $administor->last_login_at);

					$this->redirect('/');
				}
			}
		}

		$this->render('administor/login');
	}

	// 管理员信息
	public function profile()
	{
		$title = '管理员设置';
		$notifications = array();

		if (!empty($_POST)) {
			$username = $_SESSION['userinfo']['username'];

			$old_password = trim($_POST['old_password']);
			$new_password = trim($_POST['new_password']);
			$confirm_new_password = trim($_POST['confirm_new_password']);

			if (empty($old_password) || empty($new_password) || empty($confirm_new_password)) {
				$notifications = array(
					'error' => true,
					'message' => '请填写完整所有信息',
				);
			} elseif($new_password != $confirm_new_password) {
				$notifications = array(
					'error' => true,
					'message' => '两次输入新密码不一致',
				);
			} else {
				$administor = Administor::findOne('administors', 'username = ?', [$username]);

				if (empty($administor)) {
					$notifications = array(
						'error' => true,
						'message' => '用户不存在',
					);
				} else {
					if (!password_verify($old_password, $administor->password)) {
						$notifications = array(
							'error' => true,
							'message' => '输入原密码不正确',
						);
					} else {
						$administor->password = password_hash($new_password, PASSWORD_BCRYPT);

						// 更新最后登录时间
						if (Administor::store($administor)) {
							$notifications = array(
								'error' => false,
								'message' => '修改密码成功',
							);
						} else {
							$notifications = array(
								'error' => true,
								'message' => '系统错误',
							);

							$this->error('admin profile update error', $_POST);
						}
					}
				}
			}
		}

		$this->render('administor/profile', array('title' => $title, 'notifications' => $notifications));
	}

	//  增加管理员
	public function add()
	{
		$title = '管理员设置';
		$notifications = array();

		// 简单注册
		if (!empty($_POST)) {
			$username = trim($_POST['username']);
			$password = trim($_POST['password']);
			$confirm_password = trim($_POST['confirm_password']);

			if (empty($username) || empty($password) || empty($confirm_password)) {
				$notifications = array(
					'error' => true,
					'message' => '请填写完整所有信息',
				);
			} elseif ($password != $confirm_password) {
				$notifications = array(
					'error' => true,
					'message' => '请填写完整所有信息',
				);
			} else {
				$administor = Administor::findOne('administors', 'username = ?', [$username]);
				if (!empty($administor)) {
					$notifications = array(
						'error' => true,
						'message' => '用户名已被注册',
					);
				} else {
					$administor = Administor::dispense('administors');
					$administor->username = $username;
					$administor->password = password_hash($password, PASSWORD_BCRYPT);
					$administor->created_at = getCurrentTime();

					if (Administor::store($administor)) {
						$notifications = array(
							'error' => false,
							'message' => '添加成功',
						);
					} else {
						$notifications = array(
							'error' => true,
							'message' => '系统错误',
						);
					}
				}
			}
		}

		$this->render('administor/add', array('title' => $title, 'notifications' => $notifications));
	}
	
	// 登出
	public function logout()
	{
		// Unset all of the session variables.
		$_SESSION = array();
		
		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		
		// Finally, destroy the session.
		session_destroy();

		$this->redirect('/login');
	}

	// 管理员列表
	public function index()
	{
		$this->ajax_api = '/administors';

		if ($this->isAjax()) {
			$page_data = $this->getStartCount();

			// 总数
			$recordsTotal = Administor::count('administors');

			// 过滤后的总数
			$recordsFiltered = $recordsTotal;
			if (!empty($page_data['search'])) {
				$recordsFiltered = Administor::count('administors', ' username = ?', [ $page_data['search'] ]);

				// 列表
				$administors = Administor::findAll('administors', ' username = ? ' . $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['search'], $page_data['start'], $page_data['count'] ]);
			} else {
				// 列表
				$administors = Administor::findAll('administors', $page_data['order_by'] . ' LIMIT ?, ?', [ $page_data['start'], $page_data['count'] ]);
			}

			$this->json_encode_output(array('data' => array_values($administors), 'draw' => intval($page_data['draw']), 'recordsFiltered' => $recordsFiltered, 'recordsTotal' => $recordsTotal));
		} else {
			$title = '管理员列表';

			$this->render('administor/list', array('title' => $title));
		}
	}
}