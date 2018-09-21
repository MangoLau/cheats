<?php
/**
 *  基础controller类
 */

namespace Admin\Controller;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class BaseController
{
	protected $logger;							// 日志

	protected $suffix_title = '空间点赞大师';		// title

	protected $ajax_api;						// ajax接口
	protected $search_desc = '请输入要搜索的内容'; // 搜索框文案

	public $path;								// 请求路径

	public function __construct()
	{
		if (!$this->checkLogin()) {
			// todo ajax
			$this->redirect(getAdminConfig('common.login_page'));
		}

		// 日志初始化
		$this->logger = new Logger('wechat-appstore');
		$this->logger->pushHandler(new StreamHandler(getLogDir().getAdminConfig('common.log.file'), $this->getLogLevel()));

		$this->path = getCurrentRequestPath();
	}

	public function checkLogin()
	{
		// 登录页面
		if ($_SERVER['REQUEST_URI'] == '/login' || $_SERVER['REQUEST_URI'] == '/register') {
			return true;
		}

		return isset($_SESSION['uid']);
	}

	// 获取日志记录等级
	protected function getLogLevel()
	{
		$level = getAdminConfig('common.log.level');
		$ret = 0;
		switch ($level) {
			case 'DEBUG':
				$ret = Logger::DEBUG;
				break;
			case 'INFO':
				$ret = Logger::INFO;
				break;
			case 'NOTICE':
				$ret = Logger::NOTICE;
				break;
			case 'WARNING':
				$ret = Logger::WARNING;
				break;
			case 'ERROR':
				$ret = Logger::ERROR;
				break;
			case 'CRITICAL':
				$ret = Logger::CRITICAL;
				break;
			case 'ALERT':
				$ret = Logger::ALERT;
				break;
			case 'EMERGENCY':
				$ret = Logger::EMERGENCY;
				break;
			default:
				$ret = Logger::WARNING;
				break;
		}

		return $ret;

	}

	public function debug($message, array $context = array())
    {
        $this->logger->AddDebug($message, $context);
    }

	public function error($message, array $context = array())
    {
        $this->logger->AddError($message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->logger->AddWarning($message, $context);
    }    

	public function params($key)
	{
		return $this->requests->$key ?: ($_POST[$key] ? urldecode($_POST[$key]) : '');
	}

	public function getCurrentUid()
	{
		return (isset($GLOBALS['session']) && isset($GLOBALS['session']['uid'])) ? $GLOBALS['session']['uid'] : '';
	}

	public function json_encode_output($data = array())
	{
		header('Content-Type: application/json');
		echo json_encode($data);
		exit;
	}

	public function return_error($error_code = 100, $error = '系统错误')
	{
		$this->json_encode_output(
			array(
				'error_code' => $error_code,
				'error' => $error,
			)
		);
	}

	public function return_success($data = 'success')
	{
		$this->json_encode_output(
			array(
				'error_code' => 0,
				'result' => $data,
			)
		);
	}

	public function redirect($url = '/')
	{
		header("Location: $url");
		die();
	}

	// 渲染模板
	public function render($path, $data = array())
	{
		// 从数组中将变量导入到当前的符号表
		!empty($data) && extract($data, EXTR_PREFIX_SAME, "tp_");
		
		$path = explode('/', $path);
		$file = '';
		foreach ($path as $p) {
			$file = $file . DIRECTORY_SEPARATOR . $p;
		}

		$file = getResourceDir() . DIRECTORY_SEPARATOR . 'views' . $file . '.php';

		if (is_readable($file)) {
			require $file;
		} else {
			$this->error('render template not exists', array($path, $data));
		}
	}

	// 分页数据
	public function getPageCount()
	{
		$page = (isset($_GET['page']) && intval($_GET['page']) >= 1) ? intval($_GET['page']) : 1;
		$count = (isset($_GET['count']) && intval($_GET['count']) >= 1) ? intval($_GET['count']) : 20;
		$start = ($page - 1) * $count;

		return [
			'page' => $page,
			'count' => $count,
			'start' => $start,
		];
	}

	// datatable插件的分页排序搜索参数
	// 排序规则默认加上status字段	03/22/2017 10:00
	public function getStartCount()
	{
		$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
		$count = (isset($_GET['length']) && $_GET['length'] >= 1) ? intval($_GET['length']) : 20;

		$draw = $_GET["draw"];	//counter used by DataTables to ensure that the Ajax returns from server-side processing requests are drawn in sequence by DataTables
   		$orderByColumnIndex  = $_GET['order'][0]['column'];// index of the sorting column (0 index based - i.e. 0 is the first record)
   		$orderBy = $_GET['columns'][$orderByColumnIndex]['data'];//Get name of the sorting column from its index
   		$orderType = $_GET['order'][0]['dir'] == 'asc' ? ' ASC' : ' DESC'; // ASC or DESC

   		$search = $_GET['search']['value'];

		return [
			'draw' => $draw,
			'start' => $start,
			'count' => $count,
			'order_by' => empty($orderBy) ? ' ORDER BY `id` DESC, `status` DESC ' : 'ORDER BY ' . $orderBy . $orderType,
			'search' => $search
		];
	}

	public function isAjax()
	{
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	public function abort($message = '系统错误')
	{
		echo $message;
		exit();
	}

	// 获取uri中传入的id
	public function getRequestID()
	{
		$arr_query = explode('/', $_SERVER['REQUEST_URI']);

		return intval($arr_query[2]);
	}
}