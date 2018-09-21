<?php
/**
 *  基础controller类
 */

namespace App\Controller;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Model\Token;

class BaseController
{
	CONST DEFAULT_PAGE_COUNT = 20;	// 默认每页加载数量
	CONST TOKEN_EXPIRED_CODE = 599;	// token过期错误码,固定

	public $logger;

	protected $token;
	protected $platform;			// 平台:安卓，iOS
	protected $channel;				// 渠道 leshitv、yingyongbao等等
	protected $version;				// 版本

	//
	public function __construct()
	{
		// 日志初始化
		$this->logger = new Logger('cheats');
		$this->logger->pushHandler(new StreamHandler(getLogDir().getConfig('app.log.file'), $this->getLogLevel()));

		$this->platform = isIOS() ? 'ios' : 'android';
		$this->channel = strval($_REQUEST['channel'] ?: $_SERVER['HTTP_APP_CHANNEL']);
		$this->version = $this->getVersion();

		$this->debug('api requests: ' . $_SERVER['REQUEST_METHOD'] . ' - ' . $_SERVER['REQUEST_URI'], array($this->getCurrentAccesstoken(), $this->platform, $_SERVER['HTTP_USER_AGENT'], $_POST, $_GET));

		// (未)登录状态检测
		$this->loginCheck();
	}

	// 登录未登录状态处理
	public function loginCheck()
	{
		$path = getCurrentRequestPath();
		// 免登录接口
		if (in_array($path, ['/', '/test', '/login', '/refreshToken', '/recharge/callback', '/recharge/71_callback', '/recharge/you_callback', '/recharge/you_callback2', '/recharge/you_callback3', '/recharge/wm_callback', '/recharge/wx_callback', '/recharge/qj_callback', '/hot-people', '/qqProducts', '/v2/qqProducts', '/banners', '/broadcasts', '/laquanquan', '/shareRanks', '/config', '/user_exists', '/recharge/direct'])) {
			return;
		}

		// 登录验证
		$access_token = $this->getCurrentAccesstoken();
		if (!empty($access_token)) {
			$token = Token::findOne('tokens', ' `key` = ? ', [ $access_token ]);
			if (empty($token)) {
				$this->return_error(401, 'access-token不存在');
				exit;
			} elseif ($token->expires_in < getCurrentTime()) {
				$this->return_error(self::TOKEN_EXPIRED_CODE, 'access-token已过期');
				exit;
			}

			$this->token = $token;
		} else {
			$this->return_error(101, '请先登录');
			exit;
		}
	}

	// 获取传入的access_token
	public function getCurrentAccesstoken()
	{
		$http_header_auth_key = 'HTTP_ACCESS_TOKEN';
		return empty($_SERVER[$http_header_auth_key]) ? '' : $_SERVER[$http_header_auth_key];
	}

	//
	public function getVersion()
	{
		$version_key = 'HTTP_VERSION';
		return empty($_SERVER[$version_key]) ? '' : $_SERVER[$version_key];
	}

	// 获取日志记录等级
	protected function getLogLevel()
	{
		$level = getConfig('app.log.level');
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

	public function json_encode_output($data = array())
	{
		header('Content-Type: application/json');
		echo json_encode($data);
		// exit;
	}

	public function return_error($error_code = 100, $error = '系统错误')
	{
		$this->json_encode_output(
			array(
				'error_code' => $error_code,
				'error' => $error,
				'result' => null,
			)
		);
	}

	public function return_success($data = 'success')
	{
		$this->json_encode_output(
			array(
				'error_code' => 0,
				'error' => '',
				'result' => $data,
			)
		);
	}

	public function redirect($url = '/')
	{
		header("Location: $url");
		die();
	}

	// 分页数据
	public function getPageCount()
	{
		$page = (isset($_GET['page']) && intval($_GET['page']) >= 1) ? intval($_GET['page']) : 1;
		$count = (isset($_GET['count']) && intval($_GET['count']) >= 1) ? intval($_GET['count']) : self::DEFAULT_PAGE_COUNT;
		$start = ($page - 1) * $count;

		return [
			'page' => $page,
			'count' => $count,
			'start' => $start,
		];
	}

	// 获取uri中传入的id
	public function getRequestID()
	{
		$arr_query = explode('/', $_SERVER['REQUEST_URI']);

		return intval($arr_query[2]);
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
}