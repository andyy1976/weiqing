<?php

//decode by QQ:270656184 http://www.yunlu99.com/
defined('IN_IA') or exit('Access Denied');
class Zmcn_erpModule extends WeModule
{
	public function welcomeDisplay()
	{
		global $_W, $_GPC;
		load()->func('communication');
		$codeca = 'http://v1.zmcn.com/erp/?k=' . urlencode($_W['setting']['site']['key']) . '&vs=1612121&tt=' . urlencode($_W['clientip']) . '&web=' . urlencode($_SERVER['HTTP_HOST']) . '&g=' . urlencode(TIMESTAMP);
		$a = ihttp_get($codeca);
		$url = $this->createWebUrl('welcome');
		Header("Location: " . $url);
	}
}