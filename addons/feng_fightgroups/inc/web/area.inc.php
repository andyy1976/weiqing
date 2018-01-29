<?php
global $_W, $_GPC;
$this->backlists();
$operation = !empty($_GPC['op'])? $_GPC['op'] : 'limit';

if($operation=='limit'){
		/*区域限制*/
		$array="";
		$arealimit2 = pdo_fetch("SELECT * FROM " . tablename('tg_arealimit') . " WHERE  uniacid = '{$_W['uniacid']}'");
		if (!empty($arealimit2)) {
			$arealimit_areas = unserialize($arealimit2['areas']);
		}
		$areafile = IA_ROOT . "/addons/feng_fightgroups/static/area/areaslimit";
		$areas = json_decode(@file_get_contents($areafile), true);
		if (!is_array($areas)) {
			require_once IA_ROOT . "/addons/feng_fightgroups/static/area/xml2json.php";
			$file = IA_ROOT . "/addons/feng_fightgroups/static/area/Area.xml";
			$content = file_get_contents($file);
			$json = xml2json :: transformXmlStringToJson($content);
			$areas = json_decode($json, true);
			file_put_contents($areafile, $json);
		}
		if (checksubmit('submitlimit')) {
			$areas = array();
			$randoms = $_GPC['random'];
			$arealimit = pdo_fetch("SELECT * FROM " . tablename('tg_arealimit') . " WHERE uniacid = '{$_W['uniacid']}'");
			if (is_array($randoms)) {
				foreach($randoms as $random) {
					$areas[] = array('citys' => $_GPC['citys'][$random]);
				} 
			} 
			$data = array('uniacid' => $_W['uniacid'], 'arealimitname' => trim($_GPC['arealimitname']), 'areas' => iserializer($areas),'enabled' => intval($_GPC['enabled']));
			if (!empty($arealimit)) {
				pdo_update('tg_arealimit', $data, array('id' => $arealimit['id']));
			} else {
				pdo_insert('tg_arealimit', $data);
				$id = pdo_insertid();
			} 
			$arealimit3 = pdo_fetch("SELECT * FROM " . tablename('tg_arealimit') . " WHERE uniacid = '{$_W['uniacid']}'");
			$arealimit_areas = unserialize($arealimit3['areas']);
			/*更新xml文件*/
			foreach($arealimit_areas as$key=>$value){
				$arr = explode(";", $value['citys']);
				foreach($arr as $ar){
					$array .= $ar.";";
				}
				
			}
			$arraylimit['citys'] = $array;
			if(!empty($arraylimit)){
				require_once IA_ROOT . "/addons/feng_fightgroups/source/areaxml.class.php";
				$updatexml = new areaxml();
				$updatexml->createxml($arraylimit);
			}
			message('更新限制省份成功！', $this -> createWebUrl('area', array('op' => 'limit')), 'success');
		} 
}elseif($operation=='yunfei'){
		$dispatch = pdo_fetch("SELECT * FROM " . tablename('tg_dispatch') . " WHERE uniacid = '{$_W['uniacid']}'");
		if (!empty($dispatch)) {
			$dispatch_areas = unserialize($dispatch['areas']);
		} 
		$areafile = IA_ROOT . "/addons/feng_fightgroups/static/area/areas";
		$areas = json_decode(@file_get_contents($areafile), true);
		if (!is_array($areas)) {
			require_once IA_ROOT . "/addons/feng_fightgroups/static/area/xml2json.php";
			$file = IA_ROOT . "/addons/feng_fightgroups/static/area/Area.xml";
			$content = file_get_contents($file);
			$json = xml2json :: transformXmlStringToJson($content);
			$areas = json_decode($json, true);
			file_put_contents($areafile, $json);
		} 
		if (checksubmit('submit')) {
			$areas = array();
			$randoms = $_GPC['random'];
			$dispatch2 = pdo_fetch("SELECT * FROM " . tablename('tg_dispatch') . " WHERE uniacid = '{$_W['uniacid']}'");
			if (is_array($randoms)) {
				foreach($randoms as $random) {
					$areas[] = array('citys' => $_GPC['citys'][$random], 'firstprice' => $_GPC['firstprice'][$random], 'firstweight' => $_GPC['firstweight'][$random], 'secondprice' => $_GPC['secondprice'][$random], 'secondweight' => $_GPC['secondweight'][$random]);
				} 
			} 
			$data = array('uniacid' => $_W['uniacid'], 'displayorder' => intval($_GPC['displayorder']), 'dispatchtype' => intval($_GPC['dispatchtype']), 'dispatchname' => trim($_GPC['dispatchname']), 'express' => trim($_GPC['express']), 'firstprice' => trim($_GPC['default_firstprice']), 'firstweight' => trim($_GPC['default_firstweight']), 'secondprice' => trim($_GPC['default_secondprice']), 'secondweight' => trim($_GPC['default_secondweight']), 'areas' => iserializer($areas), 'carriers' => iserializer($carriers), 'enabled' => intval($_GPC['enabled']));
			if (!empty($dispatch2['id'])) {
				pdo_update('tg_dispatch', $data, array('id' => $dispatch2['id']));
			} else {
				pdo_insert('tg_dispatch', $data);
				$id = pdo_insertid();
			} 
			message('更新配送区域运费成功！', $this -> createWebUrl('area', array('op' => 'yunfei')), 'success');
		} 
		
}else if ($operation == 'tpl') {
	$limit = $_GPC['limit'];
	$random = random(16);
	ob_clean();
	ob_start();
	if($limit=='limit'){
		include $this -> template('web/arealimit');
	}else{
		include $this -> template('web/dispatch');
	}
	$contents = ob_get_contents();
	ob_clean();
	die(json_encode(array('random' => $random, 'html' => $contents)));
}
include $this -> template('web/area');
