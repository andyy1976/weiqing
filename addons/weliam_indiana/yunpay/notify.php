<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
error_reporting(0);
define('IN_MOBILE', true);
require_once("lib/yun_md5.function.php");
require '../../../framework/bootstrap.inc.php';
//echo "<pre>";print_r($_REQUEST);exit;
$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `tid`=:tid';
$params = array();
$params[':tid'] = trim($_REQUEST['i2']);
$get = pdo_fetch($sql, $params);
$_W['uniacid'] = $_W['weid'] = $get['uniacid'];
//echo "<pre>";print_r($get);exit;
$setting = uni_setting($_W['uniacid'], array('payment'));
if(!is_array($setting['payment'])) {
	exit('没有设定支付参数.');
}
if($get['status']==1) {
	$log = $get;
	if(!empty($log)) {
		if (!empty($log['tag'])) {
			$tag = iunserializer($log['tag']);
			$log['uid'] = $tag['uid'];
		}
		$site = WeUtility::createModuleSite($log['module']);
		if(!is_error($site)) {
			$method = 'payResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['weid'] = $log['uniacid'];
				$ret['uniacid'] = $log['uniacid'];
				$ret['result'] = 'success';
				$ret['type'] = $log['type'];
				$ret['from'] = 'return';
				$ret['tid'] = $log['tid'];
				$ret['uniontid'] = $log['uniontid'];
				$ret['user'] = $log['openid'];
				$ret['fee'] = $log['fee'];
				$ret['tag'] = $tag;
				$ret['is_usecard'] = $log['is_usecard'];
				$ret['card_type'] = $log['card_type'];
				$ret['card_fee'] = $log['card_fee'];
				$ret['card_id'] = $log['card_id'];
				exit($site->$method($ret));
			}
		}
	}
}
//echo "<pre>";print_r($setting);exit;
if(is_array($setting['payment'])) {
	$yunpay = $setting['payment']['yunpay'];
	//echo "<pre>";print_r($yunpay);exit;
	if(!empty($yunpay)) {
		/*ksort($get);
		$string1 = '';
		foreach($get as $k => $v) {
			if($v != '' && $k != 'sign') {
				$string1 .= "{$k}={$v}&";
			}
		}
		$wechat['signkey'] = ($wechat['version'] == 1) ? $wechat['key'] : $wechat['signkey'];
		$sign = strtoupper(md5($string1 . "key={$wechat['signkey']}"));*/
		////////////////////////////////////////////////////////////////
		//计算得出通知验证结果
	$yunNotify = md5Verify($_REQUEST['i1'],$_REQUEST['i2'],$_REQUEST['i3'],$yunpay['key'],$yunpay['partner']);
	//echo $yunNotify;exit;
	if($yunNotify) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//商户订单号
	$out_trade_no = $_REQUEST['i2'];
	//云支付交易号
	$trade_no = $_REQUEST['i4'];
	//价格
	$yunprice=$_REQUEST['i1'];
		//////////////////////////////////////////////////////////////////
		//if($sign == $get['sign']) {
			if(!empty($get)){
				$log = $get;
				}else{
				$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `tid`=:tid';
				$params = array();
				$params[':tid'] = $_REQUEST['i2'];
				$log = pdo_fetch($sql, $params);
				}
			//echo "<pre>";print_r($log);exit;	
			if(!empty($log)) {
				if($log['status'] == '0'){
				$log['tag'] = iunserializer($log['tag']);
				$log['tag']['transaction_id'] = $trade_no;//云支付交易号
				$log['uid'] = $log['tag']['uid'];
				$record = array();
				$record['status'] = '1';
				$record['tag'] = iserializer($log['tag']);
				pdo_update('core_paylog', $record, array('plid' => $log['plid']));
				}
				/*if($log['is_usecard'] == 1 && $log['card_type'] == 1 &&  !empty($log['encrypt_code']) && $log['acid']) {
					load()->classs('coupon');
					$acc = new coupon($log['acid']);
					$codearr['encrypt_code'] = $log['encrypt_code'];
					$codearr['module'] = $log['module'];
					$codearr['card_id'] = $log['card_id'];
					$acc->PayConsumeCode($codearr);
				}

				if($log['is_usecard'] == 1 && $log['card_type'] == 2) {
					$now = time();
					$log['card_id'] = intval($log['card_id']);
					$iscard = pdo_fetchcolumn('SELECT iscard FROM ' . tablename('modules') . ' WHERE name = :name', array(':name' => $log['module']));
					$condition = '';
					if($iscard == 1) {
						$condition = " AND grantmodule = '{$log['module']}'";
					}
					pdo_query('UPDATE ' . tablename('activity_coupon_record') . " SET status = 2, usetime = {$now}, usemodule = '{$log['module']}' WHERE uniacid = :aid AND couponid = :cid AND uid = :uid AND status = 1 {$condition} LIMIT 1", array(':aid' => $_W['uniacid'], ':uid' => $log['uid'], ':cid' => $log['card_id']));
				}*/
				//echo "<pre>";print_r($log);exit;
				$site = WeUtility::createModuleSite($log['module']);
				if(!is_error($site)) {
					$method = 'payResult';
					if (method_exists($site, $method)) {
						$ret = array();
						$ret['weid'] = $log['weid'];
						$ret['uniacid'] = $log['uniacid'];
						$ret['acid'] = $log['acid'];
						$ret['result'] = 'success';
						$ret['type'] = $log['type'];
						$ret['from'] = 'notify';
						$ret['tid'] = $log['tid'];
						$ret['uniontid'] = $log['uniontid'];
						$ret['user'] = empty($get['openid']) ? $log['openid'] : $get['openid'];
						$ret['fee'] = $log['fee'] = $yunprice;
						$ret['tag'] = $log['tag'];
						$ret['is_usecard'] = $log['is_usecard'];
						$ret['card_type'] = $log['card_type'];
						$ret['card_fee'] = $log['card_fee'];
						$ret['card_id'] = $log['card_id'];
						$site->$method($ret);
						exit('success');
					}
				}
			}
		}
	}
}
exit('fail');