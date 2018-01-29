<?php
		global $_GPC, $_W;
		$this->getuserinfo();
		$this->checkpay($_W['openid']);
		$orderno = $_GPC['orderno'];
		$res=$this->refund($orderno,'',3);
		if($res=='success'){
			echo "<script>location.href='".$_W['siteroot'].'app/'.$this->createMobileUrl('index',array('result'=>'success'))."';</script>";
			exit;
		}else{
			echo "<script>location.href='".$_W['siteroot'].'app/'.$this->createMobileUrl('index',array('result'=>'fail'))."';</script>";
			exit;			
		}
?>