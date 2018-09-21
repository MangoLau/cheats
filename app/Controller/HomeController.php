<?php

namespace App\Controller;

/**
 * web首页
 */
class HomeController extends BaseController
{
	public function index()
	{

		$this->render('home/index');
	}
}