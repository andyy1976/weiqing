<?php
defined('IN_IA') or exit('Access Denied');
require IA_ROOT. '/addons/weliam_indiana/defines.php';
require WELIAM_INDIANA_INC.'function.php'; 
class weliam_indianaModuleSite extends WeModuleSite {
	public function __construct(){
		global $_W;
		//判断用户信息是否存在，不存在添加
		if (is_weixin()) {
			m('member') -> checkMember();
		} 
		//异步请求失败后，处理中奖结果
		$status2 = pdo_fetchall("select status,endtime,id,openid,goodsid from".tablename('weliam_indiana_period')."where uniacid={$_W['uniacid']} and status=2 ");
		if($status2){
			foreach($status2 as$k=>$v){
				$t = $v['endtime'] - time();
				if($t<=0){
					pdo_update("weliam_indiana_period",array('status'=>3),array('id'=>$v['id']));
					$goods = pdo_fetch("select title from".tablename("weliam_indiana_goodslist")."where id='{$v['goodsid']}' and uniacid='{$_W['uniacid']}'");
					$datam = array(
						"first"=>array( "value"=> "恭喜你！你参与的一元夺宝已中奖！","color"=>"#173177"),
						"keyword1"=>array('value' => "一元夺宝", "color" => "#4a5077"),
						"keyword2"=>array('value' => $goods['title'], "color" => "#4a5077"),
						"remark"=>array("value"=>'点击查看详情', "color" => "#4a5077"),
					);
					$url2 = $_W['siteroot']."app/index.php?i=".$_W['uniacid']."&c=entry&do=order_get&m=weliam_indiana";
					$sql = 'SELECT `settings` FROM ' . tablename('uni_account_modules') . ' WHERE `uniacid` = :uniacid AND `module` = :module';
					$settings = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':module' => 'weliam_indiana'));
					$settings = iunserializer($settings);
					$template_id = $settings['m_suc'];
					$account= WeAccount :: create($_W['acid']);
					$account -> sendTplNotice($v['openid'], $template_id, $datam, $url2);
				}
			}
		}
	}

	function doRequest($url, $param=array()){  
	    $urlinfo = parse_url($url);  
	    $host = $urlinfo['host'];  
	    $path = $urlinfo['path'];  
	    $query = isset($param)? http_build_query($param) : '';  
	    $port = 80;  
	    $errno = 0;  
	    $errstr = '';  
	    $timeout = 70;  
	  
	    $fp = fsockopen($host, $port, $errno, $errstr, $timeout);  
	    $out = "POST ".$path." HTTP/1.1\r\n";  
	    $out .= "host:".$host."\r\n";  
	    $out .= "content-length:".strlen($query)."\r\n";  
	    $out .= "content-type:application/x-www-form-urlencoded\r\n";  
	    $out .= "connection:close\r\n\r\n";  
	    $out .= $query;  
	  
	    fputs($fp, $out);  
	    fclose($fp);  
	} 

	protected function pay($params = array(), $mine = array()) {
		global $_W;
		//购买的商品
		$openid = m('user') -> getOpenid();
		$record = pdo_fetch("select openid,uniacid,type from".tablename('weliam_indiana_rechargerecord')."where ordersn='{$params['tid']}'");
		$openid = m('user') -> getOpenid();
		isetcookie('uniacid',$_W['uniacid'],600);
		if($record['type']==1){
			//充值
			$money = $params['fee'];
		}else{
			//删除支付数量为0的购物车记录
			pdo_delete("weliam_indiana_cart",array('uniacid'=>$_W['uniacid'],'num'=>0));
			//支付
			$thisCart = pdo_fetchall("select * from".tablename('weliam_indiana_cart')."where openid='{$record['openid']}' and uniacid={$record['uniacid']}");				
			$money=0;
			$num = 0;
			foreach($thisCart as $key=>$value){
				$goodslist = m('goods')->getListByPeriod_number($value['period_number']);
				$money +=$value['num']*$goodslist['init_money'];
				$thisCart[$key]['num']=$value['num'];
				$num++;
			}
			//账户余额夺宝币
			$thismember = m('member') -> getInfoByOpenid($record['openid']);
		}
		if(!$this->inMobile) {
			message('支付功能只能在手机上使用');
		}
		$share_data = $this -> module['config'];
		$_W['page']['footer'] = $share_data['copyright'];
		$title = '支付方式';
		if($share_data['paytype'] == 2){
			include $this->template('paycenter');
		}else{
			$params['module'] = $this->module['name'];
			$pars = array();
			$pars[':uniacid'] = $_W['uniacid'];
			$pars[':module'] = $params['module'];
			$pars[':tid'] = $params['tid'];
			if($params['fee'] <= 0) {
				$pars['from'] = 'return';
				$pars['result'] = 'success';
				$pars['type'] = 'alipay';
				$pars['tid'] = $params['tid'];
				$site = WeUtility::createModuleSite($pars[':module']);
				$method = 'payResult';
				if (method_exists($site, $method)) {
					exit($site->$method($pars));
				}
			}
	
			$sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid';
			$log = pdo_fetch($sql, $pars);
			if(!empty($log) && $log['status'] == '1') {
				message('这个订单已经支付成功, 不需要重复支付.');
			}
			$setting = uni_setting($_W['uniacid'], array('payment', 'creditbehaviors'));
			if(!is_array($setting['payment'])) {
				message('没有有效的支付方式, 请联系网站管理员.');
			}
			$pay = $setting['payment'];
			$pay['credit']['switch'] = false;
			$pay['delivery']['switch'] = false;
			if (!empty($pay['credit']['switch'])) {
				$credtis = mc_credit_fetch($_W['member']['uid']);
			}
			$iscard = pdo_fetchcolumn('SELECT iscard FROM ' . tablename('modules') . ' WHERE name = :name', array(':name' => $params['module']));
			$you = 0;
			if($pay['card']['switch'] == 2 && !empty($_W['openid'])) {
				if($_W['card_permission'] == 1 && !empty($params['module'])) {
					$cards = pdo_fetchall('SELECT a.id,a.card_id,a.cid,b.type,b.title,b.extra,b.is_display,b.status,b.date_info FROM ' . tablename('coupon_modules') . ' AS a LEFT JOIN ' . tablename('coupon') . ' AS b ON a.cid = b.id WHERE a.acid = :acid AND a.module = :modu AND b.is_display = 1 AND b.status = 3 ORDER BY a.id DESC', array(':acid' => $_W['acid'], ':modu' => $params['module']));
					$flag = 0;
					if(!empty($cards)) {
						foreach($cards as $temp) {
							$temp['date_info'] = iunserializer($temp['date_info']);
							if($temp['date_info']['time_type'] == 1) {
								$starttime = strtotime($temp['date_info']['time_limit_start']);
								$endtime = strtotime($temp['date_info']['time_limit_end']);
								if(TIMESTAMP < $starttime || TIMESTAMP > $endtime) {
									continue;
								} else {
									$param = array(':acid' => $_W['acid'], ':openid' => $_W['openid'], ':card_id' => $temp['card_id']);
									$num = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND openid = :openid AND card_id = :card_id AND status = 1', $param);
									if($num <= 0) {
										continue;
									} else {
										$flag = 1;
										$card = $temp;
										break;
									}
								}
							} else {
								$deadline = intval($temp['date_info']['deadline']);
								$limit = intval($temp['date_info']['limit']);
								$param = array(':acid' => $_W['acid'], ':openid' => $_W['openid'], ':card_id' => $temp['card_id']);
								$record = pdo_fetchall('SELECT addtime,id,code FROM ' . tablename('coupon_record') . ' WHERE acid = :acid AND openid = :openid AND card_id = :card_id AND status = 1', $param);
								if(!empty($record)) {
									foreach($record as $li) {
										$time = strtotime(date('Y-m-d', $li['addtime']));
										$starttime = $time + $deadline * 86400;
										$endtime = $time + $deadline * 86400 + $limit * 86400;
										if(TIMESTAMP < $starttime || TIMESTAMP > $endtime) {
											continue;
										} else {
											$flag = 1;
											$card = $temp;
											break;
										}
									}
								}
								if($flag) {
									break;
								}
							}
						}
					}
					if($flag) {
						if($card['type'] == 'discount') {
							$you = 1;
							$card['fee'] = sprintf("%.2f", ($params['fee'] * ($card['extra'] / 100)));
						} elseif($card['type'] == 'cash') {
							$cash = iunserializer($card['extra']);
							if($params['fee'] >= $cash['least_cost']) {
															$you = 1;
								$card['fee'] = sprintf("%.2f", ($params['fee'] -  $cash['reduce_cost']));
							}
						}
						load()->classs('coupon');
						$acc = new coupon($_W['acid']);
						$card_id = $card['card_id'];
						$time = TIMESTAMP;
						$randstr = random(8);
						$sign = array($card_id, $time, $randstr, $acc->account['key']);
						$signature = $acc->SignatureCard($sign);
						if(is_error($signature)) {
							$you = 0;
						}
					}
				}
			}
	
			if($pay['card']['switch'] == 3 && $_W['member']['uid']) {
				$cards = array();
				if(!empty($params['module'])) {
					$cards = pdo_fetchall('SELECT a.id,a.couponid,b.type,b.title,b.discount,b.condition,b.starttime,b.endtime FROM ' . tablename('activity_coupon_modules') . ' AS a LEFT JOIN ' . tablename('activity_coupon') . ' AS b ON a.couponid = b.couponid WHERE a.uniacid = :uniacid AND a.module = :modu AND b.condition <= :condition AND b.starttime <= :time AND b.endtime >= :time  ORDER BY a.id DESC', array(':uniacid' => $_W['uniacid'], ':modu' => $params['module'], ':time' => TIMESTAMP, ':condition' => $params['fee']), 'couponid');
					if(!empty($cards)) {
						$condition = '';
						if($iscard == 1) {
							$condition = " AND grantmodule = '{$params['module']}'";
						}
						foreach($cards as $key => &$card) {
							$has = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('activity_coupon_record') . ' WHERE uid = :uid AND uniacid = :aid AND couponid = :cid AND status = 1' . $condition, array(':uid' => $_W['member']['uid'], ':aid' => $_W['uniacid'], ':cid' => $card['couponid']));
							if($has > 0){
								if($card['type'] == '1') {
									$card['fee'] = sprintf("%.2f", ($params['fee'] * $card['discount']));
									$card['discount_cn'] = sprintf("%.2f", $params['fee'] * (1 - $card['discount']));
								} elseif($card['type'] == '2') {
									$card['fee'] = sprintf("%.2f", ($params['fee'] -  $card['discount']));
									$card['discount_cn'] = $card['discount'];
								}
							} else {
								unset($cards[$key]);
							}
						}
					}
				}
				if(!empty($cards)) {
					$cards_str = json_encode($cards);
				}
			}
			include $this->template('wpaycenter');
		}
	}
//付款结果返回
	public function payResult($params){
		global $_W, $_GPC;
		$uniacid=$_W['uniacid'];
		$fee = $params['fee'];
		$buy_codes = $fee;
		$openid = $params['user'];
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$paytype = array('credit' => '1', 'wechat' => '3', 'alipay' => '2');
		$data['paytype'] = $paytype[$params['type']];
		if ($params['type'] == 'wechat') {
			$data['transid'] = $params['tag']['transaction_id'];
		}
		if ($params['type'] == 'yunpay') {
			$data['transid'] = $params['tag']['transaction_id'];//二次开发
		}
		$record = pdo_fetch("SELECT * FROM " . tablename('weliam_indiana_rechargerecord') . " WHERE ordersn ='{$params['tid']}'");//获取商品ID
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			//微信支付
			if (empty($record['status'])) {
				$data['status'] = 1;
				$pays = m('credit')->checkpay($params['tid']);
				$data['num'] = $pays['fee'];
				pdo_update('weliam_indiana_rechargerecord', $data, array('ordersn' => $params['tid']));
				m('credit')->updateCredit2($record['openid'],$_W['uniacid'],$pays['fee']);
				if($record['type']==1){
					$result_mess = '支付成功！';
				}elseif($record['num'] != $data['num']){
					echo '支付异常，支付金额返回余额';
					exit;
				}else{
					if(m('codes')->code($record['openid'],$record['uniacid'])){
						$level=$this->module['config']['level'];
						if($level==1){
							$level1=$this->module['config']['level1'];
							$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
							foreach($invites as$key=>$value){
								m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
								m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
							}
						}
						if($level==2){
							$level1=$this->module['config']['level1'];
							$level2=$this->module['config']['level2'];
							$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
							foreach($invites as $key=>$value){
								m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
								m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
								$invites2=m('invite')->getInvitesByOpenid($value['invite_openid'],$_W['uniacid']);
								foreach($invites2 as $k=>$v){
									m('credit')->updateCredit1($v['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
									m('invite')->updateBy2Openid($value['invite_openid'],$v['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
								}
							}
							
						}
						if($level==3){
							$level1=$this->module['config']['level1'];
							$level2=$this->module['config']['level2'];
							$level3=$this->module['config']['level3'];
							$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
							foreach($invites as $key=>$value){
								m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
								m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
								$invites2=m('invite')->getInvitesByOpenid($value['invite_openid'],$_W['uniacid']);
								foreach($invites2 as $kk=>$vv){
									m('credit')->updateCredit1($vv['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
									m('invite')->updateBy2Openid($value['invite_openid'],$vv['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
									$invites3=m('invite')->getInvitesByOpenid($vv['invite_openid'],$_W['uniacid']);
									foreach($invites3 as $k=>$v){
										m('credit')->updateCredit1($v['invite_openid'],$_W['uniacid'],$level3*$buy_codes);
										m('invite')->updateBy2Openid($vv['invite_openid'],$v['invite_openid'],$_W['uniacid'],$level3*$buy_codes);
									}
								}
							}
						}
					}
				}
			}
		}

		if ($params['from'] == 'return' && $params['result'] == 'success') {
			//微信云支付通知
			
			/**************   积分返还开始  ********************/
			$level1=$this->module['config']['level1'];
			$invitemess = pdo_fetch("select invite_openid from".tablename('weliam_indiana_invite')."where uniacid='{$_W['uniacid']}' and beinvited_openid = '{$openid}' and type = 0 ");
			m('credit')->updateCredit1($invitemess['invite_openid'],$_W['uniacid'],$level1*$record['num']);
			/**************   积分返还结束  ********************/
			
			$siterooturl = $_W['siteroot']."app/";
			if(strpos($siterooturl,'addons')!==false||strpos($siterooturl,'yunpay')!==false)$siterooturl = $_W['siteroot']."../../../app/";//二次开发
			$url2 = $siterooturl.$this -> createMobileUrl('order');
			$tpl_id_short = $this->module['config']['m_pay'];
			$data  = array(
				"name"=>array( "value"=> "支付成功！预祝中大奖！","color"=>"#173177"),
				"remark"=>array('value' => "\r点击查看详情！", "color" => "#4a5077"),
			);
			m('common')->sendTplNotice($record['openid'],$tpl_id_short,$data,$url2,'');
			
			if($record['type']==1){
				$siterooturl = $_W['siteroot']."app/";
				if(strpos($siterooturl,'addons')!==false||strpos($siterooturl,'yunpay')!==false)$siterooturl = $_W['siteroot']."../../../app/";//二次开发
				header("location:".$siterooturl.str_replace('./','',$this->createMobileUrl('person')));
			}else{
				$siterooturl = $_W['siteroot']."app/";
				if(strpos($siterooturl,'addons')!==false||strpos($siterooturl,'yunpay')!==false)$siterooturl = $_W['siteroot']."../../../app/";//二次开发
				header("location:".$siterooturl.str_replace('./','',$this->createMobileUrl('endbuy')));
			}
		}
	}

//ping++支付结果
	public function othrtpayResult($params){
		global $_W, $_GPC;
		$uniacid=$_W['uniacid'];
		$fee = $params['fee'];
		$buy_codes = $fee;
		$paytype = array('credit' => '1', 'wx_pub' => '3', 'alipay_wap' => '2','jdpay_wap' => '4' , 'bfb_wap' => '5');
		$data['paytype'] = $paytype[$params['type']];

		$record = pdo_fetch("SELECT * FROM " . tablename('weliam_indiana_rechargerecord') . " WHERE ordersn ='{$params['tid']}'");//获取商品ID
		file_put_contents(WELIAM_INDIANA."/params.log", var_export($record, true).PHP_EOL, FILE_APPEND);
		$openid = $record['openid'];
		if (empty($record['status'])) {
			$data['status'] = 1;
			pdo_update('weliam_indiana_rechargerecord', $data, array('ordersn' => $params['tid']));
			m('credit')->updateCredit2($record['openid'],$_W['uniacid'],$record['num']);
			if($record['type']==1){
				$result_mess = '支付成功！';
			}else{
				if(m('codes')->code($record['openid'],$record['uniacid'])){
					$level=$this->module['config']['level'];
					if($level==1){
						$level1=$this->module['config']['level1'];
						$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
						file_put_contents(WELIAM_INDIANA."/params.log", var_export($openid, true).PHP_EOL, FILE_APPEND);
						file_put_contents(WELIAM_INDIANA."/params.log", var_export($invites, true).PHP_EOL, FILE_APPEND);
						foreach($invites as$key=>$value){
							m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
							m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
						}
					}
					if($level==2){
						$level1=$this->module['config']['level1'];
						$level2=$this->module['config']['level2'];
						$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
						foreach($invites as $key=>$value){
							m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
							m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
							$invites2=m('invite')->getInvitesByOpenid($value['invite_openid'],$_W['uniacid']);
							foreach($invites2 as $k=>$v){
								m('credit')->updateCredit1($v['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
								m('invite')->updateBy2Openid($value['invite_openid'],$v['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
							}
						}
						
					}
					if($level==3){
						$level1=$this->module['config']['level1'];
						$level2=$this->module['config']['level2'];
						$level3=$this->module['config']['level3'];
						$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
						foreach($invites as $key=>$value){
							m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
							m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$buy_codes);
							$invites2=m('invite')->getInvitesByOpenid($value['invite_openid'],$_W['uniacid']);
							foreach($invites2 as $kk=>$vv){
								m('credit')->updateCredit1($vv['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
								m('invite')->updateBy2Openid($value['invite_openid'],$vv['invite_openid'],$_W['uniacid'],$level2*$buy_codes);
								$invites3=m('invite')->getInvitesByOpenid($vv['invite_openid'],$_W['uniacid']);
								foreach($invites3 as $k=>$v){
									m('credit')->updateCredit1($v['invite_openid'],$_W['uniacid'],$level3*$buy_codes);
									m('invite')->updateBy2Openid($vv['invite_openid'],$v['invite_openid'],$_W['uniacid'],$level3*$buy_codes);
								}
							}
						}
					}
				}
			}
			
		}
	
		if ($params['type'] == $credit) {
			header("location:".$this->createMobileUrl('endbuy'));
		} else {
			header("location:".'../../app/' . $this->createMobileUrl('endbuy'));
		}
		
	}

/*＝＝＝＝＝＝＝＝＝＝＝＝＝＝以下为后台管理＝＝＝＝＝＝＝＝＝＝＝＝＝＝*/
//商品管理
	private function getGoodsStatus($status){
		$status = intval($status);
		if ($status == 1) {
			return '下架';
		} elseif ($status == 2) {
			return '上架';
		} else {
			return '未知';
		}
	}
/*＝＝＝＝＝＝＝＝＝＝＝＝＝＝设置商品上下架函数＝＝＝＝＝＝＝＝＝＝＝＝＝＝*/	
	public function doWebSetGoodsProperty() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$type = $_GPC['type'];
		$data = intval($_GPC['data']);
		if (in_array($type, array('new', 'hot', 'recommand', 'discount'))) {
			$data = ($data==1?'0':'1');
			pdo_update("weliam_indiana_goodslist", array("is" . $type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array('status'))) {
			$data = ($data==2?'1':'2');
			if($data==1){				
				pdo_update("weliam_indiana_period",array('status'=> 0),array('goodsid'=>$id,'uniacid'=>$_W['uniacid'],'status'=>1));
				pdo_update("weliam_indiana_goodslist", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
			}else{
				//判定是否是重新上架
				$max_periods = pdo_fetchcolumn("select max(periods) from".tablename('weliam_indiana_period')."where uniacid = '{$_W['uniacid']}' and goodsid = '{$id}'");//检测当前商品最大期数
				$periods_result = pdo_fetch("select shengyu_codes from".tablename('weliam_indiana_period')."where uniacid = '{$_W['uniacid']}' and goodsid = '{$id}' and periods = '{$max_periods}'");
				if($periods_result['shengyu_codes'] >0){
					pdo_update("weliam_indiana_period",array('status'=> 1),array('goodsid'=>$id,'uniacid'=>$_W['uniacid'],'status'=>0));
					pdo_update("weliam_indiana_goodslist", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
				}else{
					$goods_result = m('goods')->getGoods($id);
					if($goods_result['maxperiods'] > $max_periods){
						//生成重新上架的夺宝码
						$CountNum=intval($goods_result['price'])/$goods_result['init_money'];
						$periods = $max_periods + 1;
						$codes=array();
						for($i=1;$i<=$CountNum;$i++){
							$codes[$i]=1000000+$i;
						}
						
						shuffle($codes);
						$codes=serialize($codes);
						$data1['canyurenshu'] = 0;
						$data1['scale'] = 0;
						$data1['uniacid'] = $_W['uniacid'];
						$data1['goodsid'] = $goods_result['id'];
						$data1['shengyu_codes'] = $CountNum;
						$data1['zong_codes'] = $CountNum;
						$data1['codes'] = $codes;
						$data1['allcodes'] = $codes;
						$data1['periods'] = $periods;
						$data1['status'] = 1;
						$data1['period_number'] = date('Ymd').substr(time(), -5).substr(microtime(), 2, 5).sprintf('%02d', rand(0, 99));
						$data1['createtime'] = TIMESTAMP;
						$ret = pdo_insert('weliam_indiana_period', $data1);
						unset($codes);
						pdo_update("weliam_indiana_goodslist", array($type => $data,'periods'=>$periods), array("id" => $id, "uniacid" => $_W['uniacid']));
					}
				}
				
			}
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		if (in_array($type, array('type'))) {
			$data = ($data==1?'2':'1');
			pdo_update("weliam_indiana_goodslist", array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
			die(json_encode(array("result" => 1, "data" => $data)));
		}
		die(json_encode(array("result" => 0)));
	}
	
/*＝＝＝＝＝＝＝＝＝＝＝＝＝＝以下为其他函数＝＝＝＝＝＝＝＝＝＝＝＝＝＝*/
  	//微信图片下载两个方法downloadWeiXinFile(),saveWeiXinFile()
  	public function downloadWeiXinFile($url){
  		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$package = curl_exec($ch);
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		return "$package";
		
  	}
	
	public function saveWeiXinFile($filename,$filecontent){
  		$local_file = fopen($filename, 'w');
		if(false !== $local_file){
			if(FALSE !== fwrite($local_file, $filecontent)){
				fclose($local_file);
				return "ture";
			}
		}
  	}
	
	public function template($filename, $type = TEMPLATE_INCLUDEPATH) {
		global $_W;
		$name = strtolower($this -> modulename);
		if (defined('IN_SYS')) {
			$source = IA_ROOT . "/web/themes/{$_W['template']}/{$name}/{$filename}.html";
			$compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$name}/{$filename}.tpl.php";
			if (!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$name}/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/addons/{$name}/template/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/web/themes/{$_W['template']}/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/web/themes/default/{$filename}.html";
			} 
		} else {
			$template = $this->module['config']['style'];
			$file = IA_ROOT . "/addons/{$name}/data/template/shop_" . $_W['uniacid'];
			if (is_file($file)) {
				$template = file_get_contents($file);
				if (!is_dir(IA_ROOT . '/addons/{$name}/template/mobile/' . $template)) {
					$template = "default";
				} 
			} 
			$compile = IA_ROOT . "/data/tpl/app/{$name}/{$template}/mobile/{$filename}.tpl.php";
			$source = IA_ROOT . "/addons/{$name}/template/mobile/{$template}/{$filename}.html";
			if (!is_file($source)) {
				$source = IA_ROOT . "/addons/{$name}/template/mobile/default/{$filename}.html";
			}
			if (!is_file($source)) {
				$source = IA_ROOT . "/app/themes/{$_W['template']}/{$filename}.html";
			} 
			if (!is_file($source)) {
				$source = IA_ROOT . "/app/themes/default/{$filename}.html";
			} 
		} 
		if (!is_file($source)) {
			exit("Error: template source '{$filename}' is not exist!");
		} 
		if (DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
			template_compile($source, $compile, true);
		} 
		return $compile;
	} 
/*＝＝＝＝＝＝＝＝＝＝＝＝＝＝以下为打印记录函数＝＝＝＝＝＝＝＝＝＝＝＝＝＝*/
	public function printrecord($filename,$filedata){
		file_put_contents(WELIAM_INDIANA."/".$filename.".log", var_export($filedata, true).PHP_EOL, FILE_APPEND);
	} 
}