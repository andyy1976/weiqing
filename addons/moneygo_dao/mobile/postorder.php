<?php
	if (empty($_GPC['id'])) {
        message('抱歉，参数错误！', '', 'error');
    }
	$sid = intval($_GPC['id']);
	

	$ordersn=date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
	$proplemess = pdo_fetch("SELECT * FROM ".tablename('moneygo_member')." WHERE uniacid = '{$_W['uniacid']}' and from_user ='{$_W['fans']['from_user']}' ");
	if (empty($proplemess)) {
		message('请先填写您的资料！', $this->createMobileUrl('prodata'), 'warning');
	}
	
	

	$data=array(
		'from_user'=>$_W['fans']['from_user'],
		'nickname'=>$proplemess['nickname'],
		'uniacid'=>$_W['uniacid'],
		'sid'=>$sid,
		'ordersn'=>$ordersn,
		'status'=>0,
		'count'=>$_GPC['count'],
		'createtime' => TIMESTAMP,
		'zongji'=>$_GPC['count']*getshu($sid),
	);

	if(pdo_insert(moneygo_record,$data))
	{
		
		$orderid = pdo_insertid();
		
		
		message('提交成功！',$this->createMobileUrl('pay',array('id'=>$orderid)),'success');
	}else{
		message('提交失败！');
	}
	
  function getshu($id){
   	global $_W;
	$xinxi = pdo_fetch("SELECT * FROM".tablename('moneygo_goodslist')."WHERE uniacid='{$_W['uniacid']}' AND id=:id",array(':id'=>$id));
	return $xinxi['danjia'];
   }
?>