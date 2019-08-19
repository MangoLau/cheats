<?php

namespace App\Controller;

use App\Middleware\Cheat;

/**
 * web首页
 */
class HomeController extends BaseController
{
	public function index()
	{

		$this->render('home/index');
	}

	public function test()
    {
        $r = Cheat::getQQTwittes(408857455, $page = 1);
        echo $r;exit;
        $retArray = json_decode($r,true);
        if($retArray['code'] == -4009) {
            $this->return_error(401, '请先绑定QQ');
        }
    }
}