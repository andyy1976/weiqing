<?php
	global $_W,$_GPC;
	$openid = m('user') -> getOpenid();
	if($_GPC['type']=='credit2'){
		//余额支付
		$num_money = pdo_fetchall("select * from".tablename('weliam_indiana_cart')."where uniacid = '{$_W['uniacid']}' and openid = '{$openid}'");
		$money = 0;
		foreach($num_money as $key =>$value){
			$goodsid = pdo_fetchcolumn("select goodsid from".tablename('weliam_indiana_period')."where period_number = '{$value['period_number']}'");
			$init_money = pdo_fetchcolumn("select init_money from".tablename('weliam_indiana_goodslist')."where id = '{$goodsid}'");
			$money = $money+$init_money*$value['num'];
		}
		$uniacid = $_GPC['uniacid'];
		$thismember = m('member') -> getInfoByOpenid($openid);
		$credit2 = $thismember['credit2']-$money;
		if($credit2>=0){
			
			if(m('codes')->code($openid,$uniacid)){
				//通知
				$url2 = $_W['siteroot'] . 'app/' . $this -> createMobileUrl('order');
				$tpl_id_short = $this->module['config']['m_pay'];
				$data  = array(
					"name"=>array( "value"=> "支付成功！预祝中大奖！","color"=>"#173177"),
					"remark"=>array('value' => "\r点击查看详情！", "color" => "#4a5077"),
				);
				m('common')->sendTplNotice($openid,$tpl_id_short,$data,$url2,'');
				
				$level=$this->module['config']['level'];
				if($level==1){
					$level1=$this->module['config']['level1'];
					$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
					foreach($invites as$key=>$value){
						m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$money);
						m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$money);
					}
				}
				if($level==2){
					$level1=$this->module['config']['level1'];
					$level2=$this->module['config']['level2'];
					$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
					foreach($invites as $key=>$value){
						m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$money);
						m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$money);
						$invites2=m('invite')->getInvitesByOpenid($value['invite_openid'],$_W['uniacid']);
						foreach($invites2 as $k=>$v){
							m('credit')->updateCredit1($v['invite_openid'],$_W['uniacid'],$level2*$money);
							m('invite')->updateBy2Openid($value['invite_openid'],$v['invite_openid'],$_W['uniacid'],$level2*$money);
						}
					}
					
				}
				if($level==3){
					$level1=$this->module['config']['level1'];
					$level2=$this->module['config']['level2'];
					$level3=$this->module['config']['level3'];
					$invites=m('invite')->getInvitesByOpenid($openid,$_W['uniacid']);
					foreach($invites as $key=>$value){
						m('credit')->updateCredit1($value['invite_openid'],$_W['uniacid'],$level1*$money);
						m('invite')->updateBy2Openid($openid,$value['invite_openid'],$_W['uniacid'],$level1*$money);
						$invites2=m('invite')->getInvitesByOpenid($value['invite_openid'],$_W['uniacid']);
						foreach($invites2 as $kk=>$vv){
							m('credit')->updateCredit1($vv['invite_openid'],$_W['uniacid'],$level2*$money);
							m('invite')->updateBy2Openid($value['invite_openid'],$vv['invite_openid'],$_W['uniacid'],$level2*$money);
							$invites3=m('invite')->getInvitesByOpenid($vv['invite_openid'],$_W['uniacid']);
							foreach($invites3 as $k=>$v){
								m('credit')->updateCredit1($v['invite_openid'],$_W['uniacid'],$level3*$money);
								m('invite')->updateBy2Openid($vv['invite_openid'],$v['invite_openid'],$_W['uniacid'],$level3*$money);
							}
						}
					}
				}
				die(json_encode(array("result" => 1,"money"=>$money)));	
			}else{
				die(json_encode(array("result" => 0,"why"=>'本期结束，购买失败!')));
			}
		}else{
			die(json_encode(array("result" => 0,"why"=>'余额不足')));	
		}
	}elseif($_GPC['type']=='recharge'){
		//充值
		$id = $_GPC['id'];
		$thisrecharge = pdo_fetch("select * from".tablename('weliam_indiana_rechargerecord')."where id={$id} ");
		$params['tid'] = $thisrecharge['ordersn'];
		$params['user'] = $thisrecharge['openid'];
		$params['out_trade_no'] = $thisrecharge['ordersn'];
		$params['fee'] = $thisrecharge['num'];
		$params['title'] = $_W['account']['name'];
		$params['ordersn'] = $thisrecharge['ordersn'];
		$this->pay($params);
	}else if($_GPC['wechat']){
		//微信支付
		if (empty($_GPC['id'])) {
	        message('抱歉，参数错误！', '', 'error');
	    }
		$orderid = intval($_GPC['id']);
		$uniacid=$_W['uniacid'];
		$order = pdo_fetch("SELECT * FROM " . tablename('weliam_indiana_rechargerecord') . " WHERE id ='{$orderid}'");
		$params['tid'] = $order['ordersn'];
		$params['user'] = $openid;
		$params['fee'] = $order['num'];
		$params['title'] = $_W['account']['name'];
		$params['ordersn'] = $order['ordersn'];
		$this->pay($params);
	}else{
		//云支付
		if (empty($_GPC['id'])) {
	        message('抱歉，参数错误！', '', 'error');
	    }
		$orderid = intval($_GPC['id']);
		$uniacid=$_W['uniacid'];
		$order = pdo_fetch("SELECT * FROM " . tablename('weliam_indiana_rechargerecord') . " WHERE id ='{$orderid}'");
		$params['tid'] = $order['ordersn'];
		$params['out_trade_no'] = $order['ordersn'];
		$params['user'] = $openid;
		$params['fee'] = $order['num'];
		$params['title'] = $_W['account']['name'];
		$params['ordersn'] = $order['ordersn'];
		$this->pay($params);
	}
	
	
	
?>