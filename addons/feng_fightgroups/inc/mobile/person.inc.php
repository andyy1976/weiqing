<?php
	global $_W, $_GPC;
	$this->getuserinfo();
	session_start();
	$_SESSION['goodsid']='';
	$_SESSION['tuan_id']='';
	$_SESSION['groupnum']='';
	$uid=$_W['member']['uid'];
	$share_data = $this -> module['config'];
	load()->model('mc');
	$result = mc_fetch($_W['member']['uid'], array('credit1', 'credit2','avatar','nickname'));
	mc_oauth_userinfo();
	load()->model('account');
	$modules = uni_modules();
	include $this->template('person');
?>
