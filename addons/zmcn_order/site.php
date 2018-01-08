<?php

//decode by QQ:89197986 https://www.admincn.cc/
defined('IN_IA') or exit('Access Denied');
define('DS', DIRECTORY_SEPARATOR);
$zmfile = IA_ROOT . DS . 'addons' . DS . 'zmcn_sms' . DS . 'inc' . DS . 'send.php';
if (is_file($zmfile)) {
	include_once $zmfile;
}
if ($config['setting']['development']) {
	define('ZMUIVS', TIMESTAMP);
} else {
	define('ZMUIVS', 'V201706083');
}
class Zmcn_orderModuleSite extends WeModuleSite
{
	private $tb_user = 'zmcn_cer_user';
	private $tb_cers = 'zmcn_cer_cers';
	private $tb_clas = 'zmcn_cer_class';
	private $tb_goods = 'zmcn_goods';
	private $tb_goodsclass = 'zmcn_goods_class';
	private $tb_cart = 'zmcn_order_cart';
	private $tb_order = 'zmcn_order_order';
	private $tb_ordergoods = 'zmcn_order_order_goods';
	private $tb_printer = 'zmcn_order_Printer';
	private $tb_agstock = 'zmcn_stock_agent';
	private $tb_agstockh = 'zmcn_stock_history';
	private $tb_payh = 'zmcn_pay_history';
	private $tb_account = 'zmcn_account';
	private $tb_spsy = 'zmcn_fw_basy';
	private $tb_commission = 'zmcn_commission_history';
	private $tb_goodsoption = "zmcn_goods_option";
	private $tb_dispatch = "zmcn_dispatch";
	private $tb_notice = 'zmcn_notice';
	private $tb_staff = 'zmcn_staff';
	public function __construct()
	{
		global $_W, $agent;
		setting_load('userapps:m');
		$_W['zm_o_st'] = array('已取消', '待付款', '待查账', '待发货', '已发货', '在途中', '已签收', '已确认', '退件', '已退货');
		$agent = array();
	}
	private function getmobiledl($ist = array())
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		if (empty($ist['mstp'])) {
			$ist['mstp'] = 'err';
		}
		$ist['pop'] = (int) $ist['pop'];
		load()->model('mc');
		if (empty($ist['iswx'])) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			if (strpos($user_agent, 'MicroMessenger') === false) {
				$this->zm_message(array('zt' => 1, 'ts' => '本页面仅支持微信访问!非微信浏览器禁止浏览!！', 'pop' => $ist['pop']), '', $ist['mstp']);
			}
		}
		$ttime = date('Ymd', TIMESTAMP - 10800);
		load()->classs('weixin.account');
		$_W['access_token'] = WeAccount::token();
		if ($ist['mstp'] == 'ajax') {
			$this->checkAuth();
		}
		$openid = $_W['openid'];
		$agent = array();
		if (empty($openid)) {
			$openid = $_GPC['openid'];
			$key = md5($openid . $ttime . $_W['access_token']);
			if ($key != $_GPC['k']) {
				$this->zm_message(array('zt' => 1, 'ts' => '非法链接!', 'pop' => $ist['pop']), '', $ist['mstp']);
			}
		}
		if (empty($openid)) {
			$this->zm_message(array('zt' => 1, 'ts' => '01你还没关注我们公众号！', 'pop' => $ist['pop']), '', $ist['mstp']);
		}
		$fans = mc_fansinfo($openid);
		if (!is_array($fans)) {
			$this->zm_message(array('zt' => 1, 'ts' => '02你还没关注我们公众号！', 'pop' => $ist['pop']), '', $ist['mstp']);
		}
		$agent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE openid=:openid AND uniacid=:uniacid', array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
		if ($agent['id']) {
			$agent['is_agent'] = 1;
			$agent['is_staff'] = 0;
			$agent['is_office'] = 0;
			$agent['role'] = 'agent';
		} elseif ($openid == $settings['sys']['openid']) {
		} else {
			if ($ist['js'] == 'agent') {
				$this->zm_message(array('zt' => 1, 'ts' => '你没有权限操作！', 'pop' => $ist['pop']), '', $ist['mstp']);
			}
			$agent['is_agent'] = 0;
			$agent['is_staff'] = '-1';
			$agent['is_office'] = 0;
			$agent['role'] = 'consumer';
			if (pdo_tableexists($this->tb_staff)) {
				$staff = array();
				$staff = pdo_fetch('SELECT * FROM ' . tablename($this->tb_staff) . " WHERE uniacid=:uniacid AND openid=:openid AND status='1'", array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
				if (is_serialized($staff['set'])) {
					$staff['set'] = iunserializer($staff['set']);
				} else {
					$staff['set'] = array();
				}
				if ($staff['id']) {
					$agent['role'] = 'staff';
					if ($staff['agentid']) {
						$agent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND uniacid=:uniacid', array(':id' => $staff['agentid'], ':uniacid' => $_W['uniacid']));
						if ($agent['openid']) {
							$openid = $agent['openid'];
						}
						$agent['role'] = 'ag_staff';
						$agent['is_office'] = 0;
					} else {
					}
					$agent['is_agent'] = 0;
					$agent['is_staff'] = 1;
					$agent['1staff'] = $staff;
				}
			}
		}
		if (empty($ist['isdl'])) {
			if ($agent['is_office'] == 0) {
				if (empty($agent['id'])) {
					$this->zm_message(array('zt' => 1, 'ts' => '你还没登记或没绑定微信，请和上级经销商联系！', 'pop' => $ist['pop']), url('entry', array('do' => 'login', 'm' => 'zmcn_cer', 'openid' => $openid, 'fid' => $_GPC['fid'], 'mb' => $_GPC['mb']), true), $ist['mstp']);
				}
				if ($agent['type'] == '1') {
					$this->zm_message(array('zt' => 1, 'ts' => '你的申请未能通过授权。请和上级经销商联系！', 'pop' => $ist['pop']), '', $ist['mstp']);
				}
				if ($agent['type'] == '2') {
					$this->zm_message(array('zt' => 1, 'ts' => '你已经提交过授权申请。请耐心等待我们的审核！', 'pop' => $ist['pop']), '', $ist['mstp']);
				}
				if ($agent['type'] == '0') {
					$this->zm_message(array('zt' => 1, 'ts' => '你的授权已被取消。请和上级经销商联系！', 'pop' => $ist['pop']), '', $ist['mstp']);
				}
				if ($agent['cer_end_time'] < TIMESTAMP) {
					$this->zm_message(array('zt' => 1, 'ts' => '你的授权已过期。请和上级经销商联系！', 'pop' => $ist['pop']), '', $ist['mstp']);
				}
			}
			if ($agent['is_staff']) {
				$oop = trim($_GPC['op']);
				$top = array('fks' => 'fk', 'efk' => 'fk', 'efks' => 'fk', 'del' => 'qx', 'sfh' => 'fh');
				if ($top[$oop]) {
					$oop = $top[$oop];
				}
				$odo = trim($_GPC['do']);
				$tdo = array('myinorder' => 'orderedit_xq', 'myinorderlis' => 'orderedit_xq', 'myoutorder' => 'ordereditout_xq', 'myoutorderlis' => 'ordereditout_xq');
				if ($tdo[$odo]) {
					$odo = $tdo[$odo];
				}
				$sqid = 'order_' . $odo . (empty($oop) ? '' : '_' . $oop);
				$st = array('order_orderedit_fk', 'order_orderedit_sh', 'order_orderedit_xq', 'order_orderedit_qx', 'order_ordereditout_xq', 'order_ordereditout_gaij', 'order_ordereditout_dk', 'order_ordereditout_dh', 'order_ordereditout_xiadan', 'order_ordereditout_dy', 'order_ordereditout_fh', 'order_ordereditout_qx', 'fanli_saoqr');
				if (in_array($sqid, $st) && !empty($staff['set'])) {
					if (empty($staff['set'][$sqid])) {
						$this->zm_message(array('zt' => 1, 'ts' => '你没有权限操作！', 'pop' => $ist['pop']), '', $ist['mstp']);
					}
				}
			}
		}
		if (!empty($settings['sys']['siteid']) && $ist['pop'] == 0 && $ist['mstp'] == 'err') {
			$_W['zmcn']['navs3'] = pdo_fetchall('SELECT * FROM ' . tablename('site_nav') . " WHERE uniacid = '" . $_W['uniacid'] . "' AND multiid=:multiid AND section=3 ORDER BY displayorder DESC", array(':multiid' => (int) $settings['sys']['siteid']), 'id');
			$_W['zmcn']['navs4'] = pdo_fetchall('SELECT * FROM ' . tablename('site_nav') . " WHERE uniacid = '" . $_W['uniacid'] . "' AND multiid=:multiid AND section=4 ORDER BY displayorder DESC", array(':multiid' => (int) $settings['sys']['siteid']), 'id');
			foreach ($_W['zmcn']['navs3'] as $nav) {
				if (is_serialized($nav['css'])) {
					$_W['zmcn']['navs3'][$nav['id']]['css'] = iunserializer($nav['css']);
				} else {
					$_W['zmcn']['navs3'][$nav['id']]['css'] = array();
				}
			}
			foreach ($_W['zmcn']['navs4'] as $nav) {
				if (is_serialized($nav['css'])) {
					$_W['zmcn']['navs4'][$nav['id']]['css'] = iunserializer($nav['css']);
				} else {
					$_W['zmcn']['navs4'][$nav['id']]['css'] = array();
				}
			}
		}
	}
	private function checkAuth()
	{
		global $_W;
		if (empty($_W['member']['uid'])) {
			$fan = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid']));
			if (!empty($fan)) {
				$fanid = $fan['fanid'];
			} else {
				$post = array('uniacid' => $_W['uniacid'], 'updatetime' => time(), 'openid' => $_W['openid'], 'follow' => 0);
				pdo_insert('mc_mapping_fans', $post);
				$fanid = pdo_insertid();
			}
			if (empty($fan['uid'])) {
				pdo_insert('mc_members', array('uniacid' => $_W['uniacid']));
				$uid = pdo_insertid();
				$_W['member']['uid'] = $uid;
				$_W['fans']['uid'] = $uid;
				pdo_update('mc_mapping_fans', array('uid' => $uid), array('fanid' => $fanid));
			} else {
				$_W['member']['uid'] = $fan['uid'];
				$_W['fans']['uid'] = $fan['uid'];
			}
		}
	}
	public function doMobileIndex()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$id = intval($_GPC['id']);
		$this->getmobiledl();
		$abc = 1;
		$page = array($id => array('weui-tab__bd-item--active', 'weui-bar__item--on'));
		$agentlevel = $this->zm_agentlevel($agent['level']);
		$myteamnew = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND fid=:fid AND `type`=2', array(':uniacid' => $_W['uniacid'], ':fid' => $agent['id']));
		if (pdo_tableexists($this->tb_notice)) {
			$notices = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_notice) . " WHERE uniacid=:uniacid AND `status`>0 AND (`type`=0 OR `type`=1 AND (levels=',,' AND " . (int) $agent['id'] . ">0 OR levels LIKE :levels)) ORDER BY status DESC,displayorder DESC,lasttime DESC", array(':uniacid' => $_W['uniacid'], ':levels' => '%,' . (int) $agentlevel['id'] . ',%'), 'id');
		}
		if (empty($settings['sys']['dhurl'])) {
			$settings['sys']['dhurl'] = url('activity/goods');
		}
		$inod = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND agentid=:agentid AND status>0', array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id']));
		$outod = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND fagentid=:fagentid AND status>0', array(':uniacid' => $_W['uniacid'], ':fagentid' => $agent['id']));
		$myteam = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND fid=:fid', array(':uniacid' => $_W['uniacid'], ':fid' => $agent['id']));
		$member = mc_fetch($fans['uid'], array('credit1', 'credit2', 'groupid'));
		$sd1 = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND agentid=:agentid AND status=1', array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id']));
		$sd2 = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND fagentid=:fagentid AND status=2', array(':uniacid' => $_W['uniacid'], ':fagentid' => $agent['id']));
		$sd3 = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND fagentid=:fagentid AND status=3', array(':uniacid' => $_W['uniacid'], ':fagentid' => $agent['id']));
		$today = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$outodm = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND fagentid=:fagentid AND status>=4 AND exptime>=:exptime', array(':uniacid' => $_W['uniacid'], ':fagentid' => $agent['id'], ':exptime' => $today));
		$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$outodd = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND fagentid=:fagentid AND status>=4 AND exptime>=:exptime', array(':uniacid' => $_W['uniacid'], ':fagentid' => $agent['id'], ':exptime' => $today));
		if (!in_array('fw', $_W['setting']['userapps:m'])) {
			$abc = 0;
		}
		$today = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$inconm = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND fid=:fid AND  addtime>=:addtime', array(':uniacid' => $_W['uniacid'], ':fid' => $agent['id'], ':addtime' => $today));
		$jianjf = (int) pdo_fetchcolumn("SELECT sum(num) FROM " . tablename('mc_credits_record') . " WHERE uniacid=:uniacid AND credittype='credit1' AND uid=:uid AND module = 'zmcn_cer'", array(':uniacid' => $_W['uniacid'], ':uid' => $fans['uid']));
		$jianye = (int) pdo_fetchcolumn("SELECT sum(num) FROM " . tablename('mc_credits_record') . " WHERE uniacid=:uniacid AND credittype='credit2' AND uid=:uid AND module = 'zmcn_cer' AND remark=:remark", array(':uniacid' => $_W['uniacid'], ':uid' => $fans['uid'], ':remark' => '证书扫码查询奖励'));
		if ($settings['sys']['siteid']) {
			$_W['zmcn']['navs0'] = pdo_fetchall('SELECT * FROM ' . tablename('site_nav') . " WHERE uniacid = '" . $_W['uniacid'] . "' AND multiid=:multiid AND section=0 ORDER BY displayorder DESC", array(':multiid' => (int) $settings['sys']['siteid']), 'id');
			foreach ($_W['zmcn']['navs0'] as $nav) {
				if (is_serialized($nav['css'])) {
					$_W['zmcn']['navs0'][$nav['id']]['css'] = iunserializer($nav['css']);
				} else {
					$_W['zmcn']['navs0'][$nav['id']]['css'] = array();
				}
			}
			$_W['zmcn']['navs2'] = pdo_fetchall('SELECT * FROM ' . tablename('site_nav') . " WHERE uniacid = '" . $_W['uniacid'] . "' AND multiid=:multiid AND section=2 ORDER BY displayorder DESC", array(':multiid' => (int) $settings['sys']['siteid']), 'id');
			foreach ($_W['zmcn']['navs2'] as $nav) {
				if (is_serialized($nav['css'])) {
					$_W['zmcn']['navs2'][$nav['id']]['css'] = iunserializer($nav['css']);
				} else {
					$_W['zmcn']['navs2'][$nav['id']]['css'] = array();
				}
			}
		}
		include $this->template('index');
	}
	public function doMobileMy()
	{
		global $_GPC;
		$_GPC['id'] = 1;
		$this->doMobileIndex();
	}
	public function doMobilesite()
	{
		global $_GPC;
		$_GPC['id'] = 4;
		$this->doMobileIndex();
	}
	public function doMobileOrder()
	{
		global $_GPC;
		$_GPC['id'] = 2;
		$this->doMobileIndex();
	}
	public function doMobileCart()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		$agentlevel = $this->zm_agentlevel($agent['level']);
		$agentlevel['sets']['fiestyuan'] = (int) $agentlevel['sets']['fiestyuan'];
		if (!empty($agent['level'])) {
			$pric = 'c' . $agent['level'];
		} else {
			$pric = 'productprice';
		}
		if ($_GPC['op'] == 'zj') {
			$zxz = $bjz = $zdz = 'abcdefg';
		} else {
			$zxz = 'd' . $agent['level'];
			$bjz = 'b' . $agent['level'];
			$zdz = 'g' . $agent['level'];
		}
		$tj1 = array(':uniacid' => $_W['uniacid']);
		$tj2 = " AND iscer = 1 ";
		if (!empty($agent['class'])) {
			$tj2 .= " AND ( cerclass='##' OR cerclass LIKE :cerclass ) ";
			$tj1[':cerclass'] = '%#' . $agent['class'] . '#%';
		} else {
			$tj2 .= " AND cerclass='##' ";
		}
		if (!empty($cid)) {
			$tj2 .= " AND zclas LIKE :zclas ";
			$tj1[':zclas'] = $cid . '%';
		}
		if ($_GPC['submit'] == 'sqxd') {
			if (!in_array('fw', $_W['setting']['userapps:m'])) {
				$this->zm_message('系统没开通此功能！', '', 'err');
			}
			$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
			if (is_serialized($list['k'])) {
				$codeset = iunserializer($list['k']);
			} else {
				$codeset = array();
			}
			$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
			include $this->template('cartsao');
		} elseif ($_GPC['submit'] == '进入下单' || $_GPC['submit'] == '下单') {
			$dind = array();
			$zhongj = 0;
			$weight = 0;
			$dss = array();
			$gids = array();
			if (empty($_GPC['count'])) {
				$this->zm_message('你还没有选中商品！', '', 'err');
			}
			foreach ($_GPC['count'] as $gidop => $gito) {
				$gidop = explode('_', $gidop . '_');
				$gids['id'][] = (int) $gidop[0];
				$gids['op'][] = (int) $gidop[1];
				$gids['to'][] = (int) $gito;
			}
			array_unique($gids['id']);
			array_unique($gids['op']);
			if (array_sum($gids['to']) < 1) {
				$this->zm_message('请选择商品后再下单！', '', 'err');
			}
			$sql = 'SELECT COUNT(*) FROM ' . tablename($this->tb_order) . " WHERE uniacid=:uniacid AND agentid=:agentid AND cktime>:cktime AND status > 4 ";
			$issc = (int) pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id'], ':cktime' => (int) $agent['fiesttime']));
			$tj2 .= " AND id IN (" . implode(',', $gids['id']) . ") ";
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid " . $tj2 . " ", $tj1, 'id');
			$tj2 = " AND id IN (" . implode(',', $gids['op']) . ") ";
			$options = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goodsoption) . " WHERE uniacid=:uniacid " . $tj2 . " ", array(':uniacid' => $_W['uniacid']), 'id');
			foreach ($_GPC['count'] as $gidop => $sul) {
				$sul = (int) $sul;
				$gidop = explode('_', $gidop . '_');
				$gid = (int) $gidop[0];
				$gop = (int) $gidop[1];
				if (!empty($sul) && !empty($goodss[$gid]['id'])) {
					$goods = $goodss[$gid];
					$goods['prices'] = iunserializer($goods['prices']);
					$npric = $pric == 'productprice' ? $goods['productprice'] : $goods['prices'][$pric];
					$npric = empty($npric) ? $goods['productprice'] : $npric;
					if (empty($goods['isspec'])) {
						$sul = max($sul, intval($goods['prices'][$zxz]));
					}
					if ($goods['prices'][$zdz]) {
						$sul = min($sul, intval($goods['prices'][$zdz]));
					}
					$dind[] = array('id' => $gid, 'name' => empty($goods['name']) ? $goods['title'] : $goods['name'], 'price' => $npric, 'sl' => $sul, 'optionname' => $options[$gop]['title'], 'optionid' => $gop);
					$dss[] = array('i' => $gid, 'o' => $gop, 's' => $sul);
					$zhongj += $npric * $sul;
					$weight += $goods['weight2'] * $sul;
				}
			}
			if (empty($issc)) {
				if ((int) $agentlevel['sets']['fiestyuan'] > $zhongj) {
					$this->zm_message('首次下单货款要在' . $agentlevel['sets']['fiestyuan'] . '元以上！', '', 'err');
				}
			} else {
				if ((int) $agentlevel['sets']['everyyuan'] > $zhongj) {
					$this->zm_message('每次下单货款要在' . $agentlevel['sets']['everyyuan'] . '元以上！', '', 'err');
				}
			}
			$slc = array_map('end', $dss);
			$fagent = array();
			$fagent = $this->getdinh($agent['fid']);
			setting_load('zmcn:kuaidi');
			$dss = base64_encode(iserializer($dss));
			$profile = fans_search($_W['fans']['from_user'], array('resideprovince', 'residecity', 'residedist', 'address', 'realname', 'mobile'));
			if (in_array('location', $_W['setting']['userapps:m'])) {
				$_W['account']['location'] = 1;
			}
			$row = pdo_fetch("SELECT * FROM " . tablename('mc_member_address') . " WHERE isdefault = 1 and uid = :uid limit 1", array(':uid' => $fans['uid']));
			$express = $this->getexpresslits(array('province' => $row['province']), $weight, $fagent['id']);
			include $this->template('confirm');
		} elseif ($_GPC['submit'] == '进入建单' || $_GPC['submit'] == '建单') {
			$pric = 'productprice';
			$dind = array();
			$zhongj = 0;
			$weight = 0;
			$dss = array();
			if (empty($_GPC['count'])) {
				$this->zm_message('你还没有选中商品！', '', 'err');
			}
			foreach ($_GPC['count'] as $gidop => $gito) {
				$gidop = explode('_', $gidop . '_');
				$gids['id'][] = (int) $gidop[0];
				$gids['op'][] = (int) $gidop[1];
				$gids['to'][] = (int) $gito;
			}
			array_unique($gids['id']);
			array_unique($gids['op']);
			if (array_sum($gids['to']) < 1) {
				$this->zm_message('请选择商品后再下单！', '', 'err');
			}
			$tj2 .= " AND id IN (" . implode(',', $gids['id']) . ") ";
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid " . $tj2 . " ", $tj1, 'id');
			$tj2 = " AND id IN (" . implode(',', $gids['op']) . ") ";
			$options = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goodsoption) . " WHERE uniacid=:uniacid " . $tj2 . " ", array(':uniacid' => $_W['uniacid']), 'id');
			foreach ($_GPC['count'] as $gidop => $sul) {
				$sul = (int) $sul;
				$gidop = explode('_', $gidop . '_');
				$gid = (int) $gidop[0];
				$gop = (int) $gidop[1];
				if (!empty($sul) && !empty($goodss[$gid]['id'])) {
					$goods = $goodss[$gid];
					$goods['prices'] = iunserializer($goods['prices']);
					$npric = $pric == 'productprice' ? $goods['productprice'] : $goods['prices'][$pric];
					$npric = empty($npric) ? $goods['productprice'] : $npric;
					if ($npric >= 0) {
						$dind[] = array('id' => $gid, 'name' => empty($goods['name']) ? $goods['title'] : $goods['name'], 'price' => $npric, 'sl' => $sul, 'optionname' => $options[$gop]['title'], 'optionid' => $gop);
						$dss[] = array('i' => $gid, 'o' => $gop, 's' => $sul);
						$zhongj += $npric * $sul;
						$weight += $goods['weight2'] * $sul;
					}
				}
			}
			$slc = array_map('end', $dss);
			$dagent = array();
			$fwme = '';
			$dagent = pdo_fetchall("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND fid=:fid", array(':fid' => $agent['id'], ':uniacid' => $_W['uniacid']));
			setting_load('zmcn:kuaidi');
			$dss = base64_encode(iserializer($dss));
			if (!empty($_GPC['tiaomu'])) {
				$fwme = base64_encode(iserializer(explode("\n", $_GPC['tiaomu'])));
			}
			$row = pdo_fetch("SELECT * FROM " . tablename('mc_member_address') . " WHERE isdefault = 1 and uid = :uid limit 1", array(':uid' => $fans['uid']));
			$express = $this->getexpresslits(array('province' => $row['province']), $weight, $agent['id']);
			include $this->template('confirmout');
		} elseif ($_GPC['submit'] == 'sqxd1') {
			$zhongj = 0;
			$dagent = array();
			$dagent = pdo_fetchall("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND fid=:fid", array(':fid' => $agent['id'], ':uniacid' => $_W['uniacid']));
			setting_load('zmcn:kuaidi');
			$row = pdo_fetch("SELECT * FROM " . tablename('mc_member_address') . " WHERE isdefault = 1 and uid = :uid limit 1", array(':uid' => $fans['uid']));
			include $this->template('confirmsao');
		} elseif ($_GPC['act'] == 'okdd') {
			$addid = intval($_GPC['addressid']);
			$sql = 'SELECT * FROM ' . tablename('mc_member_address') . ' WHERE `id` = :id AND `uid` = :uid';
			$row = pdo_fetch($sql, array(':id' => $addid, ':uid' => $fans['uid']));
			if (empty($row['id'])) {
				$this->zm_message('请选择收货人资料！', '', 'err');
			}
			$dss = base64_decode($_GPC['ddd']);
			if (empty($dss)) {
				$this->zm_message('订单数据有误！', '', 'err');
			}
			$dss = iunserializer($dss);
			$oder = $_GPC['order'];
			$fagent = array();
			$fagent = $this->getdinh($agent['fid']);
			if (!empty($oder['fagentid']) && $oder['fagentid'] != $fagent['id']) {
				$this->zm_message('上级对像失败，请重新定货！', '', 'err');
			}
			setting_load('zmcn:kuaidi');
			$oder['orderno'] = $this->getorderno();
			$oder['uniacid'] = $_W['uniacid'];
			$oder['agentid'] = $agent['id'];
			$oder['openid'] = $openid;
			$oder['sendfee'] = $oder['sendprice'] = (int) $oder['sendprice'];
			$oder['status'] = '1';
			$oder['expcom'] = $_W['setting']['zmcn:kuaidi'][$oder['expid']];
			$oder['province'] = $row['province'];
			$oder['city'] = $row['city'];
			$oder['district'] = $row['district'];
			$oder['address'] = $row['address'];
			$oder['tel'] = $row['mobile'];
			$oder['consignee'] = $row['username'];
			$oder['ordertime'] = TIMESTAMP;
			$oder['addtime'] = TIMESTAMP;
			$oder['lasttime'] = TIMESTAMP;
			pdo_insert($this->tb_order, $oder);
			$oder['id'] = pdo_insertid();
			$zhongj = 0;
			$weight = 0;
			$slhj = 0;
			$idc = array_map('reset', $dss);
			$slc = array_map('end', $dss);
			$ops = array_map(function ($a) {
				return $a['o'];
			}, $dss);
			array_unique($idc);
			$tj2 .= " AND id IN (" . implode(',', $idc) . ") ";
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid " . $tj2 . " ", $tj1, 'id');
			array_unique($ops);
			$tj2 = " AND id IN (" . implode(',', $ops) . ") ";
			$options = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goodsoption) . " WHERE uniacid=:uniacid " . $tj2 . " ", array(':uniacid' => $_W['uniacid']), 'id');
			foreach ($dss as $go) {
				$sul = (int) $go['s'];
				$gopid = (int) $go['o'];
				$goods = $goodss[$go['i']];
				if (!empty($sul) && !empty($goods['id'])) {
					$goods['prices'] = iunserializer($goods['prices']);
					$npric = $pric == 'productprice' ? $goods['productprice'] : $goods['prices'][$pric];
					$npric = empty($npric) ? $goods['productprice'] : $npric;
					if (empty($goods['isspec'])) {
						$sul = max($sul, intval($goods['prices'][$zxz]));
					}
					if ($goods['prices'][$zdz]) {
						$sul = min($sul, intval($goods['prices'][$zdz]));
					}
					if ($npric >= 0) {
						$hd = array('yjid' => $oder['fagentid'] == $agent['gid'] ? 0 : $agent['gid'], 'yja' => $goods['prices']['ya' . $agent['level']], 'yjb' => $goods['prices']['yb' . $agent['level']], 'yjc' => $goods['prices']['yc' . $agent['level']], 'jf' => (int) $goods['prices']['f' . $agent['level']], 'cl' => (int) $goods['prices']['t' . $agent['level']]);
						$da = array('uniacid' => $_W['uniacid'], 'orderid' => $oder['id'], 'goodsid' => $goods['id'], 'goodsname' => empty($goods['name']) ? $goods['title'] : $goods['name'], 'price' => $npric, 'total' => $sul, 'optionid' => $gopid, 'addtime' => TIMESTAMP, 'hd' => iserializer($hd));
						if ($gopid) {
							$da['optionname'] = $options[$gopid]['title'];
						} else {
							$da['optionname'] = $goods['spec'];
						}
						pdo_insert($this->tb_ordergoods, $da);
						$da['id'] = pdo_insertid();
						pdo_query("DELETE FROM " . tablename($this->tb_cart) . " WHERE uniacid = '" . $_W['uniacid'] . "' AND agentid = '" . $agent['id'] . "' AND goodsid = '" . $goods['id'] . "' AND optionid = '" . $gopid . "'");
						$zhongj += $npric * $sul;
						$weight += $goods['weight2'] * $sul;
						$slhj += $sul;
						$oder['ogoods'][$da['id']] = $da;
					}
				}
			}
			$da = array('goodsprice' => $zhongj, 'price' => $zhongj + $oder['sendfee'], 'weight' => $weight, 'com' => $slhj);
			$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid AND zt='1'", array(':agentid' => $fagent['id'], ':uniacid' => $_W['uniacid']));
			if (is_serialized($prin['prinset'])) {
				$prin['prinset'] = iunserializer($prin['prinset']);
			} else {
				$prin['prinset'] = array();
			}
			if (is_serialized($prin['princss'])) {
				$prin['princss'] = iunserializer($prin['princss']);
			} else {
				$prin['princss'] = array();
			}
			if ((int) $prin['laiy'] == '2') {
				$oder['dhf'] = $agent;
				$oder['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $agent['fid']));
				$a = $this->doprint($oder, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $oder['id']));
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => '待付款');
			$tihuan[] = array('title' => '[订单号]', 'value' => $oder['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $da['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $da['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $agent['name'] . ' ' . $agent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $oder['consignee'] . ' ' . $oder['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '自己下单');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", TIMESTAMP));
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $oder['id'] . '&do=myoutorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(21, $fagent['openid'], $tihuan, $url);
			}
			$neir = array('articles' => array(array('title' => urlencode('您的订单已提交，请您尽快与卖方联系付款和发货！'), 'description' => urlencode("订单号：" . $oder['orderno'] . "\n货品数量：" . $da['com'] . "\n订单金额：" . $da['price'] . "\n收货方：" . $oder['consignee'] . " " . $oder['tel'] . "\n订单时间：" . date("Y-m-d H:i:s", TIMESTAMP) . "\n-------------------------\n卖货方资料：\n名称：" . $fagent['name'] . "\n电话：" . $fagent['phone'] . "\n微信：" . $fagent['wechat']), 'url' => $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $oder['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect')));
			if (function_exists('zmcn_sendkfNotice')) {
				$abc = zmcn_sendkfNotice($_W['openid'], 'news', 0, $tihuan, $neir);
				if ($openid != $_W['openid']) {
					$abc = zmcn_sendkfNotice($openid, 'news', 0, $tihuan, $neir);
				}
			}
			$this->zm_message('请将货款转账后在这里上传付款凭证！', $this->createMobileUrl('orderedit', array('op' => 'fk', 'id' => $oder['id'])), 'success', '订单提交成功', '进入确认付款');
		} elseif ($_GPC['act'] == 'ckdd') {
			$addid = intval($_GPC['addressid']);
			if (!empty($addid)) {
				$row = pdo_fetch('SELECT * FROM ' . tablename('mc_member_address') . ' WHERE `id` = :id AND `uid` = :uid', array(':id' => $addid, ':uid' => $fans['uid']));
			}
			$dss = base64_decode($_GPC['ddd']);
			if (empty($dss)) {
				$this->zm_message('订单数据有误！', '', 'err');
			}
			$dss = iunserializer($dss);
			$oder = $_GPC['order'];
			$oder['status'] = (int) $oder['status'];
			$dagent = array();
			if (!empty($oder['agentid'])) {
				$dagent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND fid=:fid AND uniacid=:uniacid', array(':id' => $oder['agentid'], ':fid' => $agent['id'], ':uniacid' => $_W['uniacid']));
				if (empty($dagent['id'])) {
					$this->zm_message('你只能向自己的代理供货！', '', 'err');
				}
				if ($oder['status'] == '7' && empty($settings['sys']['dfwc'])) {
					$this->zm_message('你不能完成下级代理的订单！', '', 'err');
				}
			}
			if (!empty($dagent['level'])) {
				$pric = 'c' . $dagent['level'];
			} else {
				$pric = 'productprice';
			}
			setting_load('zmcn:kuaidi');
			$_W['setting']['zmcn:kuaidi']['my'] = $_GPC['zen'];
			$oder['fagentid'] = $agent['id'];
			$oder['orderno'] = $this->getorderno();
			$oder['uniacid'] = $_W['uniacid'];
			$oder['openid'] = $openid;
			$oder['sendfee'] = $oder['sendprice'] = (int) $oder['sendprice'];
			$oder['expcom'] = $_W['setting']['zmcn:kuaidi'][$oder['expid']];
			$oder['province'] = empty($row['province']) ? $dagent['province'] : $row['province'];
			$oder['city'] = empty($row['city']) ? $dagent['city'] : $row['city'];
			$oder['district'] = empty($row['district']) ? $dagent['district'] : $row['district'];
			$oder['address'] = empty($row['address']) ? $dagent['address'] : $row['address'];
			$oder['tel'] = empty($row['mobile']) ? $dagent['phone'] : $row['mobile'];
			$dagent['user'] = empty($dagent['user']) ? $dagent['name'] : $dagent['user'];
			$oder['consignee'] = empty($row['username']) ? $dagent['user'] : $row['username'];
			if ($oder['status'] >= '2') {
				$oder['paytime'] = TIMESTAMP;
			}
			if ($oder['status'] >= '3') {
				$oder['cktime'] = TIMESTAMP;
			}
			if ($oder['status'] >= '4') {
				$oder['exptime'] = TIMESTAMP;
			}
			if ($oder['status'] >= '7') {
				$oder['oktime'] = TIMESTAMP;
			}
			$oder['ordertime'] = TIMESTAMP;
			$oder['addtime'] = TIMESTAMP;
			$oder['lasttime'] = TIMESTAMP;
			pdo_insert($this->tb_order, $oder);
			$oder['id'] = pdo_insertid();
			$zhongj = 0;
			$weight = 0;
			$slhj = 0;
			$idc = array_map('reset', $dss);
			$slc = array_map('end', $dss);
			$ops = array_map(function ($a) {
				return $a['o'];
			}, $dss);
			array_unique($idc);
			$tj2 .= " AND id IN (" . implode(',', $idc) . ") ";
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid " . $tj2 . " ", $tj1, 'id');
			array_unique($ops);
			$tj2 = " AND id IN (" . implode(',', $ops) . ") ";
			$options = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goodsoption) . " WHERE uniacid=:uniacid " . $tj2 . " ", array(':uniacid' => $_W['uniacid']), 'id');
			foreach ($dss as $go) {
				$sul = (int) $go['s'];
				$goods = $goodss[$go['i']];
				$gopid = (int) $go['o'];
				if (!empty($sul) && !empty($goods['id'])) {
					$goods['prices'] = iunserializer($goods['prices']);
					$npric = $pric == 'productprice' ? $goods['productprice'] : $goods['prices'][$pric];
					$npric = empty($npric) ? $goods['productprice'] : $npric;
					if ($npric >= 0) {
						$hd = array('yjid' => $oder['fagentid'] == $dagent['gid'] ? 0 : $dagent['gid'], 'yja' => $goods['prices']['ya' . $dagent['level']], 'yjb' => $goods['prices']['yb' . $dagent['level']], 'yjc' => $goods['prices']['yc' . $dagent['level']], 'jf' => (int) $goods['prices']['f' . $dagent['level']], 'cl' => (int) $goods['prices']['t' . $dagent['level']]);
						$da = array('uniacid' => $_W['uniacid'], 'orderid' => $oder['id'], 'goodsid' => $goods['id'], 'goodsname' => empty($goods['name']) ? $goods['title'] : $goods['name'], 'price' => $npric, 'total' => $sul, 'optionid' => $gopid, 'addtime' => TIMESTAMP, 'hd' => iserializer($hd));
						if ($gopid) {
							$da['optionname'] = $options[$gopid]['title'];
						} else {
							$da['optionname'] = $goods['spec'];
						}
						pdo_insert($this->tb_ordergoods, $da);
						$da['id'] = pdo_insertid();
						pdo_query("DELETE FROM " . tablename($this->tb_cart) . " WHERE uniacid = '" . $_W['uniacid'] . "' AND agentid = '" . $agent['id'] . "' AND goodsid = '" . $goods['id'] . "' AND optionid = '" . $gopid . "'");
						$zhongj += $npric * $sul;
						$weight += $goods['weight2'] * $sul;
						$slhj += $sul;
						$oder['ogoods'][$da['id']] = $da;
					}
				}
			}
			$da = array('goodsprice' => $zhongj, 'price' => $zhongj + $oder['sendfee'], 'weight' => $weight, 'com' => $slhj);
			$rm = '订单：' . $oder['orderno'];
			if ($oder['status'] >= '2' && !empty($oder['agentid'])) {
				foreach ($oder['ogoods'] as $ogo) {
					$this->getagstock($oder['agentid'], $ogo['goodsid'], $ogo['total'], 2, $ogo['optionid'], 'B' . $rm);
				}
			}
			if ($oder['status'] >= '3') {
				foreach ($oder['ogoods'] as $ogo) {
					$this->getagstock($oder['fagentid'], $ogo['goodsid'], $ogo['total'], 3, $ogo['optionid'], 'A' . $rm);
				}
			}
			if ($oder['status'] >= '4') {
				foreach ($oder['ogoods'] as $ogo) {
					$this->getagstock($oder['fagentid'], $ogo['goodsid'], $ogo['total'], 0, $ogo['optionid'], $rm);
				}
			}
			if ($oder['status'] >= '7' && !empty($oder['agentid'])) {
				foreach ($oder['ogoods'] as $ogo) {
					$this->getagstock($oder['agentid'], $ogo['goodsid'], $ogo['total'], 1, $ogo['optionid'], $rm);
				}
			}
			if (!empty($_GPC['isdy'])) {
				$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid AND zt='1'", array(':agentid' => $agent['id'], ':uniacid' => $_W['uniacid']));
				if (is_serialized($prin['prinset'])) {
					$prin['prinset'] = iunserializer($prin['prinset']);
				} else {
					$prin['prinset'] = array();
				}
				if (is_serialized($prin['princss'])) {
					$prin['princss'] = iunserializer($prin['princss']);
				} else {
					$prin['princss'] = array();
				}
				if (empty($dagent['id'])) {
					$oder['dhf'] = array('name' => $oder['consignee'], 'phone' => $oder['tel']);
				} else {
					$oder['dhf'] = $dagent;
				}
				$oder['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $agent['id']));
				$a = $this->doprint($oder, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $oder['id']));
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => $_W['zm_o_st'][$oder['status']]);
			$tihuan[] = array('title' => '[订单号]', 'value' => $oder['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $da['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $da['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $dagent['name'] . ' ' . $dagent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $oder['consignee'] . ' ' . $oder['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '上级建单');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", TIMESTAMP));
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $oder['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(21, $dagent['openid'], $tihuan, $url);
			}
			if (!empty($_GPC['fwms']) && !empty($dagent['id']) && in_array('fw', $_W['setting']['userapps:m'])) {
				$fwms = iunserializer(base64_decode($_GPC['fwms']));
				$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
				if (is_serialized($list['k'])) {
					$codeset = iunserializer($list['k']);
				} else {
					$codeset = array();
				}
				$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
				foreach ($fwms as $co) {
					if (!empty($co)) {
						$co = str_replace(strtoupper($codeset['m'][1]), "", strtoupper(trim($co))) . "000000000000000000000000000000000000";
						$co = substr($co, 0, $len);
						$batchid = substr($co, 0, (int) $codeset['m'][2]);
						$batch = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_batch') . " WHERE uniacid=:uniacid AND batch=:batch", array(':uniacid' => $_W['uniacid'], ':batch' => $batchid));
						if (!empty($batch['sbox'])) {
							$lensb = strlen($batch['sbox']);
							$lsno = (int) substr($co, 0 - $lensb);
							if (empty($lsno)) {
								$colen = $len - $lensb;
								$co = substr($co, 0, $colen);
							}
							if (!empty($batch['bbox'])) {
								$lenbb = strlen($batch['bbox']);
								$lsno = (int) substr($co, 0 - $lenbb);
								if (empty($lsno)) {
									$colen = $colen - $lenbb;
									$co = substr($co, 0, $colen);
								}
							}
						}
						$co = strtoupper($codeset['m'][1]) . $co;
						$da = array('agna' => $dagent['name'], 'agtel' => $dagent['phone'], 'agwx' => $dagent['wechat']);
						$da = array('uniacid' => $_W['uniacid'], 'batchid' => $batch['id'], 'code' => $co, 'type' => '9', 'param' => iserializer($da), 'una' => $agent['name'], 'uid' => $oder['agentid'], 'Utp' => '3', 'ip' => $_W['clientip'], 'addtime' => TIMESTAMP);
						pdo_insert('zmcn_fw_basy', $da);
					}
				}
			}
			$this->zm_message('订单提交成功！', $this->createMobileUrl('order'), 'success');
		} else {
			$carts = pdo_fetchall("SELECT *,CONCAT(goodsid,'_',optionid) as readid FROM " . tablename($this->tb_cart) . " WHERE uniacid=:uniacid AND agentid=:agentid", array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id']), 'readid');
			if ($_GPC['submit'] == '保存选择' || $_GPC['submit'] == '保存') {
				if (empty($_GPC['count'])) {
					$this->zm_message('你还没有选中商品！', '', 'err');
				}
				foreach ($carts as $gidop => $abc) {
					if (empty($_GPC['count'][$gidop])) {
						pdo_delete($this->tb_cart, array('id' => $abc['id']));
						unset($carts[$gidop]);
					}
				}
				foreach ($_GPC['count'] as $gidop => $sul) {
					$sul = (int) $sul;
					$gidopt = explode('_', $gidop . '_');
					$gid = (int) $gidopt[0];
					$gop = (int) $gidopt[1];
					if ($sul) {
						$dat = array('agentid' => $agent['id'], 'goodsid' => $gid, 'optionid' => $gop, 'isck' => '1', 'openid' => $openid, 'total' => $sul, 'lasttime' => TIMESTAMP);
						if (empty($carts[$gidop]['id'])) {
							$dat['uniacid'] = $_W['uniacid'];
							pdo_insert($this->tb_cart, $dat);
							$dat['id'] = pdo_insertid();
							$dat['readid'] = $gidop;
							$carts[$gidop] = $dat;
						} else {
							pdo_update($this->tb_cart, $dat, array('id' => $carts[$gidop]['id']));
							$carts[$gidop]['total'] = $sul;
							$carts[$gidop]['isck'] = '1';
						}
					}
				}
			}
			$cartops = array();
			foreach ($carts as $gidop => $abc) {
				if (!empty($abc['optionid'])) {
					$cartops[$abc['goodsid']][$gidop] = $abc['total'];
				}
			}
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid " . $tj2 . " ORDER BY zclas DESC,displayorder DESC", $tj1, 'id');
			$bcla = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goodsclass) . " WHERE uniacid=:uniacid AND level='1'", array(':uniacid' => $_W['uniacid']), 'zmid');
			$bcla['0'] = array('id' => 0, 'name' => '未分类');
			include $this->template('cart3');
		}
	}
	public function doMobileAddress()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl(array('mstp' => 'ajax'));
		$operation = $_GPC['op'];
		$id = intval($_GPC['id']);
		if ($operation == 'getadd') {
			$sql = 'SELECT * FROM ' . tablename('mc_member_address') . ' WHERE `id` = :id AND `uid` = :uid';
			$row = pdo_fetch($sql, array(':id' => $id, ':uid' => $fans['uid']));
			$row['ssx'] = $row['province'] . ' ' . $row['city'] . ' ' . $row['district'];
			message($row, '', 'ajax');
		} elseif ($operation == 'baocadd') {
			if (!empty($_GPC['ss'])) {
				$ssx = explode(" ", trim($_GPC['ss']) . '  ');
			} else {
				$ssx = array();
			}
			if (empty($_GPC['na']) || empty($_GPC['sj']) || empty($_GPC['ad'])) {
				message(array('zt' => 1, 'ts' => '请输完善您的资料！'), '', 'ajax');
			}
			$data = array('uniacid' => $_W['uniacid'], 'uid' => $fans['uid'], 'username' => $_GPC['na'], 'mobile' => $_GPC['sj'], 'province' => $ssx[0], 'city' => $ssx[1], 'district' => $ssx[2], 'address' => $_GPC['ad']);
			if (!empty($id)) {
				unset($data['uniacid']);
				unset($data['uid']);
				pdo_update('mc_member_address', $data, array('id' => $id, 'uid' => $fans['uid']));
				message(array('zt' => 0, 'id' => $id, 'ts' => '更新成功！'), '', 'ajax');
			} else {
				pdo_update('mc_member_address', array('isdefault' => 0), array('uniacid' => $_W['uniacid'], 'uid' => $fans['uid']));
				$data['isdefault'] = 1;
				pdo_insert('mc_member_address', $data);
				$id = pdo_insertid();
				if (!empty($id)) {
					message(array('zt' => 0, 'id' => $id, 'ts' => '更新成功！'), '', 'ajax');
				} else {
					message(array('zt' => 1, 'ts' => '请输完善您的资料！'), '', 'ajax');
				}
			}
		} elseif ($operation == 'alladd') {
			$sql = 'SELECT * FROM ' . tablename('mc_member_address') . ' WHERE `uniacid` = :uniacid AND `uid` = :uid';
			$rows = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid'], ':uid' => $fans['uid']));
			include $this->template('addlist');
		} elseif ($operation == 'tiaoma') {
			if (!in_array('fw', $_W['setting']['userapps:m'])) {
				message(array('zt' => 1, 'ts' => '10数据有误！'), '', 'ajax');
			}
			$co = $_GPC['co'];
			$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
			if (is_serialized($list['k'])) {
				$codeset = iunserializer($list['k']);
			} else {
				$codeset = array();
			}
			if (empty($codeset['m'][2])) {
				message(array('zt' => 1, 'ts' => '系统有误！'), '', 'ajax');
			}
			$co = str_replace(strtoupper($codeset['m'][1]), "", strtoupper($co));
			$batchid = substr($co, 0, (int) $codeset['m'][2]);
			$batch = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_batch') . " WHERE uniacid=:uniacid AND batch=:batch", array(':uniacid' => $_W['uniacid'], ':batch' => $batchid));
			if (empty($batch['goid']) || (int) $batch['shopid'] != '88') {
				message(array('zt' => 1, 'ts' => '11不支持扫码录入！'), '', 'ajax');
			}
			$goods = pdo_fetch("SELECT id,name,sthumb,'0' as zt , '1' as total FROM " . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND id=:id ", array(':uniacid' => $_W['uniacid'], ':id' => $batch['goid']));
			if (empty($goods['id'])) {
				message(array('zt' => 1, 'ts' => '12不支持扫码录入！'), '', 'ajax');
			}
			$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
			$colen = strlen($co);
			if ($len == '12' && $colen == '13') {
				$co = substr($co, 0, 12);
				$colen = '12';
			}
			if ($colen > $len) {
			} else {
				if (!empty($batch['sbox'])) {
					if ($colen != $len) {
						$co = substr($co . "000000000000000000000000000000000000", 0, $len);
					}
					$lensb = strlen($batch['sbox']);
					$lsno = (int) substr($co, 0 - $lensb);
					if (empty($lsno)) {
						if (!empty($batch['bbox'])) {
							$lenbb = strlen($batch['bbox']) + $lensb;
							$lsno = (int) substr($co, 0 - $lenbb);
							if (empty($lsno)) {
								$goods['total'] = $batch['sbox'] * $batch['bbox'];
							} else {
								$goods['total'] = $batch['sbox'];
							}
						} else {
							$goods['total'] = $batch['sbox'];
						}
					}
				}
			}
			$goods['id'] = $goods['id'] . '_0';
			message($goods, '', 'ajax');
		} elseif ($operation == 'areachange') {
			$weight = abs(floatval($_GPC['weight']));
			$fagent = intval($_GPC['fagent']);
			$province = trim($_GPC['province']);
			$city = trim($_GPC['city']);
			$express = $this->getexpresslits(array('province' => $province), $weight, $fagent);
			$da = array('zt' => $express['zt'], 'deftexpid' => 'A');
			if ($express['zt'] == '0') {
				$da['html'] = "<select class='weui-select' name='order[expid]' onchange='expchange(this.value)'>";
				foreach ($express['express'] as $key => $value) {
					if ($da[deftexpid] == 'A') {
						$da[deftexpid] = $key;
						$da['html'] .= "<option value='" . $key . "' id='op" . $key . "' data-price='" . $value['expprice'] . "' selected='selected'>" . $value['expna'] . "：￥" . $value['expprice'] . "元</option>";
					} else {
						$da['html'] .= "<option value='" . $key . "' id='op" . $key . "' data-price='" . $value['expprice'] . "'>" . $value['expna'] . "：￥" . $value['expprice'] . "元</option>";
					}
				}
				$da['html'] .= "</select>";
			} else {
				$da['html'] = "您的地区不在配送范围内";
			}
			if ($da[deftexpid] == 'A') {
				$da['zt'] = '1';
			}
			message($da, '', 'ajax');
		} elseif ($operation == 'kuaidi') {
			if (empty($settings['sys']['wlkey'])) {
				message(array('zt' => 1, 'ts' => '此功能还没开放！'), '', 'ajax');
			}
			$expid = trim($_GPC['expid']);
			$expsn = trim($_GPC['expsn']);
			setting_load('zmcn:kuaidi');
			$expna = $_W['setting']['zmcn:kuaidi'][$expid];
			if (empty($expna)) {
				message(array('zt' => 1, 'ts' => '不支持该快递！'), '', 'ajax');
			}
			if ($settings['sys']['wlcustomer']) {
				$postdate = array();
				$postdate['customer'] = trim($settings['sys']['wlcustomer']);
				$postdate['param'] = '{"com":"' . $expid . '","num":"' . $expsn . '"}';
				$postdate['sign'] = strtoupper(MD5($postdate['param'] . trim($settings['sys']['wlkey']) . $postdate['customer']));
				$res = ihttp_post('http://poll.kuaidi100.com/poll/query.do', $postdate);
				$response = $res['content'];
				$result = array();
				if ($response) {
					$result = json_decode($response, true);
				}
				if ($result['message'] != 'ok') {
					message(array('zt' => 1, 'ts' => '无物流信息！' . $result['returncode'] . $result['message']), '', 'ajax');
				}
				$kuaizt = array('在途中', '已揽收', '疑难件', '已签收', '退签', '派件中', '退回', '转单');
				$da = array('zt' => 0, 'state' => $kuaizt[intval($result['state'])], 'data' => $result['data']);
			} else {
				$url = 'http://api.kuaidi100.com/api?id=' . trim($settings['sys']['wlkey']) . '&com=' . $expid . '&nu=' . $expsn . '&show=0&muti=1&order=asc';
				$res = ihttp_get($url);
				$response = $res['content'];
				$result = array();
				if ($response) {
					$result = json_decode($response, true);
				}
				if (empty($result['status'])) {
					message(array('zt' => 1, 'ts' => '无物流信息！'), '', 'ajax');
				}
				$kuaizt = array('在途', '揽件', '疑难', '签收', '退签', '派件', '退回');
				$da = array('zt' => 0, 'state' => $kuaizt[intval($result['state'])], 'data' => $result['data']);
			}
			message($da, '', 'ajax');
		} elseif ($operation == 'qag') {
			$fid = pdo_fetchcolumn('SELECT id FROM ' . tablename($this->tb_user) . ' WHERE openid=:openid AND uniacid=:uniacid', array(':openid' => $openid, ':uniacid' => $_W['uniacid']));
			$dagent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND fid=:fid AND uniacid=:uniacid', array(':id' => $id, ':fid' => $fid, ':uniacid' => $_W['uniacid']));
			if (!empty($dagent['level'])) {
				$pric = 'c' . $dagent['level'];
			} else {
				$pric = 'productprice';
			}
			$dss = base64_decode($_GPC['ddd']);
			if (empty($dss)) {
				message(array('zt' => 1, 'ts' => '订单数据有误！'), '', 'ajax');
			}
			$dss = iunserializer($dss);
			$da = array('add1' => $dagent['name'] . ' ' . $dagent['phone'], 'add2' => $dagent['province'] . ' ' . $dagent['city'] . ' ' . $dagent['district'] . ' ' . $dagent['address']);
			$da['cp'] = "";
			$idc = array_map('reset', $dss);
			$slc = array_map('end', $dss);
			$ops = array_map(function ($a) {
				return $a['o'];
			}, $dss);
			array_unique($idc);
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid = '" . $_W['uniacid'] . "' AND id IN (" . implode(',', $idc) . ")", array(), 'id');
			array_unique($ops);
			$tj2 = " AND id IN (" . implode(',', $ops) . ") ";
			$options = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goodsoption) . " WHERE uniacid=:uniacid " . $tj2 . " ", array(':uniacid' => $_W['uniacid']), 'id');
			$zhongj = 0;
			$weight = 0;
			foreach ($dss as $go) {
				$sul = (int) $go['s'];
				$goods = $goodss[$go['i']];
				$gopid = (int) $go['o'];
				if (!empty($sul) && !empty($goods['id'])) {
					$goods['prices'] = iunserializer($goods['prices']);
					$npric = $pric == 'productprice' ? $goods['productprice'] : $goods['prices'][$pric];
					$npric = empty($npric) ? $goods['productprice'] : $npric;
					if ($npric >= 0) {
						$nn = empty($goods['name']) ? $goods['title'] : $goods['name'];
						$da['cp'] .= "<tr class='f14'><td class='tleft nll'><h5 class='weui-media-box__title'>" . $nn . "<h5>";
						if ($gopid) {
							$da['cp'] .= "<p class='weui-media-box__desc'>" . $options[$gopid]['title'] . "</p>";
						}
						$da['cp'] .= "</td><td>" . $sul . "</td><td class='tright'>" . $npric . "</td></tr>";
						$zhongj += $npric * $sul;
						$weight += $goods['weight2'] * $sul;
					}
				}
			}
			$da['cp'] .= "<tr class='f14'><td colspan='3' class='tright'>货款(不含配送费)：￥<b id='zje'>" . $zhongj . "</b>元</td></tr>";
			$da['zt'] = 0;
			$da['ts'] = '更新成功！';
			$da['goodsprice'] = $zhongj;
			$da['weight'] = $weight;
			message($da, '', 'ajax');
		}
	}
	public function doMobileMyinorder()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		if (empty($_GPC['tb'])) {
			$tb = '888';
		} else {
			$tb = intval($_GPC['tb']);
		}
		$_GPC['id'] = intval($_GPC['id']);
		include $this->template('myinorder');
	}
	public function doMobileMyinorderlis()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		$pindex = max(1, intval($_GPC['ab']));
		$psize = 10;
		$type = intval($_GPC['clid']);
		$tj1 = "";
		$tj2 = array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id']);
		if ($type != '888') {
			$tj1 .= " AND status=:status ";
			$tj2[':status'] = $type;
		}
		$atime = strtotime($_GPC['atime']);
		$btime = strtotime($_GPC['btime']) + 3600 * 24;
		if ($atime > 100000000) {
			$tj1 .= " AND ordertime>=:kst ";
			$tj2[':kst'] = $atime;
		}
		if ($btime > 100000000 && $btime > $atime) {
			$tj1 .= " AND ordertime<:jst ";
			$tj2[':jst'] = $btime;
		}
		$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' AND agentid=:agentid ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
		if (empty($list)) {
			exit(1);
		}
		foreach ($list as &$row) {
			$list[$row['id']]['ogoods'] = pdo_fetchall("SELECT g.id,o.total,o.price,o.goodsname,o.optionname,g.sthumb,g.unit FROM " . tablename($this->tb_ordergoods) . " o left join " . tablename($this->tb_goods) . " g on o.goodsid=g.id " . " WHERE o.orderid='{$row['id']}'");
		}
		include $this->template('myinorder_lis');
	}
	function payResult($params)
	{
		global $_W, $_GPC;
		if ($params['from'] != 'return' || $params['result'] != 'success') {
			$this->zm_message('支付错误, 请与客服联系！', $this->createMobileUrl('myinorder'), 'err');
		}
		$_W['zmcnefk'] = $params;
		$_GPC['op'] = 'efks';
		$_GPC['id'] = $params['tid'];
		$this->doMobileOrderedit();
	}
	public function doMobileOrderedit()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		$operation = $_GPC['op'];
		$id = intval($_GPC['id']);
		$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND id=:id AND agentid=:agentid', array(':uniacid' => $_W['uniacid'], ':id' => $id, ':agentid' => $agent['id']));
		$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid AND zt='1'", array(':agentid' => $order['fagentid'], ':uniacid' => $_W['uniacid']));
		if (is_serialized($prin['prinset'])) {
			$prin['prinset'] = iunserializer($prin['prinset']);
		} else {
			$prin['prinset'] = array();
		}
		if (is_serialized($prin['princss'])) {
			$prin['princss'] = iunserializer($prin['princss']);
		} else {
			$prin['princss'] = array();
		}
		if ($operation == 'fk') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['status'] != '1') {
				$this->zm_message('此订单不是待付款订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			include $this->template('ordereditfk');
		} elseif ($operation == 'fks') {
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			if ($order['status'] != '1') {
				message(array('zt' => 1, 'ts' => '此订单不是待付款订单！'), '', 'ajax');
			}
			$rm = '订单：' . $order['orderno'];
			if (empty($order['fagentid'])) {
				$fagent = array('openid' => $settings['sys']['openid']);
			} else {
				$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['fagentid'], ':uniacid' => $_W['uniacid']));
			}
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myoutorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (!empty($_FILES['file']['tmp_name'])) {
				load()->func('file');
				$upload = file_upload($_FILES['file']);
				if (is_error($upload)) {
					message(array('zt' => 1, 'ts' => '付款凭证上传失败！请重试！'), '', 'ajax');
				}
				$img = array('uniacid' => $_W['uniacid'], 'filename' => '订单_' . $order['orderno'] . '_' . $_FILES['file']['name'], 'attachment' => $upload['path'], 'type' => 1, 'createtime' => TIMESTAMP);
				pdo_insert('core_attachment', $img);
			}
			$da = array('status' => '2', 'payimg' => $upload['path'], 'paytime' => TIMESTAMP, 'lasttime' => TIMESTAMP, 'paytype' => '1');
			if (!empty($_GPC['isye'])) {
				if ($settings['sys']['isye'] != '1') {
					message(array('zt' => 1, 'ts' => '系统不支持余额结算！'), '', 'ajax');
				}
				$userid = mc_openid2uid($openid);
				$fuserid = mc_openid2uid($fagent['openid']);
				if (empty($userid) || empty($fuserid)) {
					message(array('zt' => 1, 'ts' => '双方代理有一方没有绑定微信做不了余额结算！'), '', 'ajax');
				}
				$member = mc_fetch($userid, array('credit2', 'credit3'));
				if (empty($member['credit2']) || $member['credit2'] < $order['price']) {
					message(array('zt' => 1, 'ts' => '您的余额不足支付本次订单！'), '', 'ajax');
				}
				$isok = mc_credit_update($userid, 'credit2', 0 - $order['price'], array($_W['uid'], $rm, 'zmcn_order', 0, 0));
				if ($isok === true) {
					$isok = mc_credit_update($fuserid, 'credit2', $order['price'], array($_W['uid'], $rm, 'zmcn_order', 0, 0));
				}
				if ($isok === true) {
					$da['paytype'] = '4';
				} else {
					$da['paytype'] = '1';
					message(array('zt' => 1, 'ts' => '您的余额不足支付本次订单！'), '', 'ajax');
				}
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock($agent['id'], $ogo['goodsid'], $ogo['total'], 2, $ogo['optionid'], 'C' . $rm);
			}
			if ((int) $prin['laiy'] == '1') {
				$order['dhf'] = $agent;
				$order['status'] = '2';
				$order['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $agent['fid']));
				$a = $this->doprint($order, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $id));
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => $_W['zm_o_st'][$order['status']]);
			$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $agent['name'] . ' ' . $agent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '线下付款');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(22, $fagent['openid'], $tihuan, $url);
			}
			message(array('zt' => 0, 'ts' => '付款操作成功，请将及时联系发货方确认和发货！'), '', 'ajax');
		} elseif ($operation == 'efk') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if (!empty($order['fagentid'])) {
				$this->zm_message('此订单不支持在线支付，请联系上经经销商！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['status'] != '1') {
				$this->zm_message('此订单不是待付款订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if (!CheckMoney($order['price']) || $order['price'] <= 0) {
				$this->zm_message('支付错误, 金额小于0！', $this->createMobileUrl('myinorder'), 'err');
			}
			$title = $agent['name'] . '订单货款';
			$params = array('tid' => $order['id'], 'ordersn' => $order['orderno'], 'title' => substr($title, 0, 60), 'fee' => $order['price'], 'user' => $_W['openid'], 'module' => 'zmcn_order');
			unset($pay['alipay']);
			include $this->template('pay');
		} elseif ($operation == 'efks') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['status'] != '1') {
				$this->zm_message('此订单不是待付款订单,请联系客服！', $this->createMobileUrl('myinorder'), 'err');
			}
			$paytype = array('credit' => '4', 'wechat' => '3', 'alipay' => '3', 'delivery' => '2');
			$actype = array('credit' => '99', 'wechat' => '3', 'alipay' => '4', 'delivery' => '88');
			$params = $_W['zmcnefk'];
			if ($params['fee'] <= 0) {
				$this->zm_message('支付错误, 金额小于0！', $this->createMobileUrl('myinorder'), 'err');
			}
			$da = array('status' => '3', 'payimg' => '', 'paytime' => TIMESTAMP, 'cktime' => TIMESTAMP, 'lasttime' => TIMESTAMP);
			if (!empty($params['is_usecard'])) {
				$cardType = array('1' => '微信卡券', '2' => '系统代金券');
				$order['remark'] .= '系统说明：使用' . $cardType[$params['card_type']] . '支付了' . ($params['fee'] - $params['card_fee']);
				$order['remark'] .= '元，实际支付了' . $params['card_fee'] . '元。';
			}
			$da['price'] = $params['fee'];
			$da['remark'] = $order['remark'];
			$da['paytype'] = $paytype[$params['type']];
			if ($paytype[$params['type']] == '') {
				$da['paytype'] = 3;
				$da['paytype'] = 2;
			}
			if ($da['paytype'] == 2) {
				$da['paytype'] = 2;
			}
			$rm = '订单：' . $order['orderno'];
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock($agent['id'], $ogo['goodsid'], $ogo['total'], 2, $ogo['optionid'], 'G' . $rm);
			}
			if ($da['status'] == '3') {
				foreach ($order['ogoods'] as $ogo) {
					$this->getagstock($order['fagentid'], $ogo['goodsid'], $ogo['total'], 3, $ogo['optionid'], 'H' . $rm);
				}
			}
			$qt = array('ustp' => 0, 'usid' => $order['agentid'], 'odtp' => 0, 'odid' => $order['id']);
			if (empty($params['card_fee'])) {
				$money = $params['fee'];
			} else {
				$money = $params['card_fee'];
			}
			$fkid = (int) $actype[$params['type']];
			$fkid = (int) pdo_fetchcolumn("SELECT id FROM " . tablename($this->tb_account) . " WHERE uniacid='" . $_W['uniacid'] . "' AND zt='1' AND `type`='{$fkid}'");
			$rm = '订单：' . $order['orderno'] . '在线支付';
			$this->getpaybd($fkid, $money, 1, $qt, $rm);
			if ((int) $prin['laiy'] == '1' || (int) $prin['laiy'] == '3' && $da['status'] == '3') {
				$order['dhf'] = $agent;
				$order['status'] = $da['status'];
				$order['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $agent['fid']));
				$a = $this->doprint($order, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $id));
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => $_W['zm_o_st'][$da['status']]);
			$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $agent['name'] . ' ' . $agent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '在线支付');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
			$url = '';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(22, $settings['sys']['openid'], $tihuan, $url);
			}
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=myinorder&m=zmcn_order';
			$this->zm_message('付款操作成功，请等候发货！', $url, 'success');
		} elseif ($operation == 'qx') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['status'] != '1') {
				$this->zm_message('此订单不是待付款订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			pdo_update($this->tb_order, array('status' => '0', 'lasttime' => TIMESTAMP), array('id' => $id));
			if (empty($order['fagentid'])) {
				$fagent = array('openid' => $settings['sys']['openid']);
			} else {
				$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['fagentid'], ':uniacid' => $_W['uniacid']));
			}
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myoutorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => '订单取消');
			$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $agent['name'] . ' ' . $agent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '买方取消');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(22, $fagent['openid'], $tihuan, $url);
			}
			$this->zm_message('操作成功，订单已取消！', $this->createMobileUrl('myinorder'), 'success');
		} elseif ($operation == 'sh') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['status'] < 4 || $order['status'] > 6) {
				$this->zm_message('此订单不是待收货状态，不能确认到货！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['yorder']) {
				$this->zm_message('代发订单，你没有确认收货权限！', $this->createMobileUrl('myinorder'), 'err');
			}
			$fjf = 0;
			$zyja = 0;
			$zyjb = 0;
			$zyjc = 0;
			$rm = '订单：' . $order['orderno'];
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid,hd FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock($agent['id'], $ogo['goodsid'], $ogo['total'], 1, $ogo['optionid'], $rm);
				if (is_serialized($ogo['hd'])) {
					$hd = iunserializer($ogo['hd']);
				} else {
					$hd = array();
				}
				if (!empty($hd['yjid'])) {
					if (!empty($hd['yja']) && CheckMoney($hd['yja']) && $hd['yja'] > 0) {
						$zyja += $hd['yja'] * $ogo['total'];
					}
					if (!empty($hd['yjb']) && CheckMoney($hd['yjb']) && $hd['yjb'] > 0) {
						$zyjb += $hd['yjb'] * $ogo['total'];
					}
					if (!empty($hd['yjc']) && CheckMoney($hd['yjc']) && $hd['yjc'] > 0) {
						$zyjc += $hd['yjc'] * $ogo['total'];
					}
				}
				if (!empty($hd['jf']) && $hd['jf'] > 0 && ($hd['cl'] == '2' || $hd['cl'] == '1' && $order['fagentid'] == '0')) {
					$fjf += (int) $hd['jf'] * $ogo['total'];
				}
			}
			if (!empty($fjf)) {
				mc_credit_update($fans['uid'], 'credit1', $fjf, array($fans['uid'], '订货送积分，' . $rm, 'zmcn_order', 0, 0));
			}
			if (!empty($zyja)) {
				$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $hd['yjid'], ':uniacid' => $_W['uniacid']));
				if (!empty($fagent['id']) && $fagent['type'] == '3' && $fagent['cer_end_time'] > TIMESTAMP) {
					$da = array('uniacid' => $_W['uniacid'], 'agentid' => $fagent['id'], 'bagentid' => $order['agentid'], 'gid' => $order['fagentid'], 'orderid' => $order['id'], 'commission' => $zyja, 'remark' => '①' . $rm, 'isok' => '0', 'dj' => '1', 'addtime' => TIMESTAMP);
					pdo_insert($this->tb_commission, $da);
					if (!empty($zyjb) && !empty($fagent['gid']) && $order['fagentid'] != $fagent['gid']) {
						$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $fagent['gid'], ':uniacid' => $_W['uniacid']));
						if (!empty($fagent['id']) && $fagent['type'] == '3' && $fagent['cer_end_time'] > TIMESTAMP) {
							$da = array('uniacid' => $_W['uniacid'], 'agentid' => $fagent['id'], 'bagentid' => $order['agentid'], 'gid' => $order['fagentid'], 'orderid' => $order['id'], 'commission' => $zyjb, 'remark' => '②' . $rm, 'isok' => '0', 'dj' => '2', 'addtime' => TIMESTAMP);
							pdo_insert($this->tb_commission, $da);
							if (!empty($zyjc) && !empty($fagent['gid']) && $order['fagentid'] != $fagent['gid']) {
								$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $fagent['gid'], ':uniacid' => $_W['uniacid']));
								if (!empty($fagent['id']) && $fagent['type'] == '3' && $fagent['cer_end_time'] > TIMESTAMP) {
									$da = array('uniacid' => $_W['uniacid'], 'agentid' => $fagent['id'], 'bagentid' => $order['agentid'], 'gid' => $order['fagentid'], 'orderid' => $order['id'], 'commission' => $zyjc, 'remark' => '③' . $rm, 'isok' => '0', 'dj' => '3', 'addtime' => TIMESTAMP);
									pdo_insert($this->tb_commission, $da);
								}
							}
						}
					}
				}
			}
			if ($order['isdf']) {
				pdo_update($this->tb_order, array('status' => '7', 'oktime' => TIMESTAMP, 'lasttime' => TIMESTAMP), array('yorder' => $id, 'id' => $id), 'OR');
			} else {
				pdo_update($this->tb_order, array('status' => '7', 'oktime' => TIMESTAMP, 'lasttime' => TIMESTAMP), array('id' => $id));
			}
			$this->zm_message('操作成功，订单已确认到货！', $this->createMobileUrl('myinorder'), 'success');
		} elseif ($operation == 'del') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', $this->createMobileUrl('myinorder'), 'err');
			}
			if ($order['status'] != '0') {
				$this->zm_message('此订单不是取消状态，不能删除！', $this->createMobileUrl('myinorder'), 'err');
			}
			$result = pdo_delete($this->tb_order, array('id' => $id));
			$result = pdo_delete($this->tb_ordergoods, array('orderid' => $id));
			$this->zm_message('操作成功，订单已删除！', $this->createMobileUrl('myinorder'), 'success');
		} elseif ($operation == 'xq') {
			if (empty($_GPC['vs'])) {
				$this->doMobileMyinorder();
				return;
			}
			if (empty($order['id'])) {
				exit('查无此订单！');
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			if ($order['status'] == 5 || $order['status'] == 6) {
				$order['status'] = 4;
			}
			setting_load('zmcn:kuaidi');
			if (empty($order['fagentid'])) {
				$fagent = array('id' => 0, 'name' => '总部', 'phone' => $settings['gs']['kftel']);
			} else {
				$fagent = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['fagentid'], ':uniacid' => $_W['uniacid']));
				if (empty($fagent['id'])) {
					$fagent = array('id' => 0, 'name' => '未知', 'phone' => ‘’);
				}
			}
			include $this->template('orderxq');
		}
	}
	public function doMobileMyoutorder()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		setting_load('zmcn:kuaidi');
		if (empty($_GPC['tb'])) {
			$tb = '888';
		} else {
			$tb = intval($_GPC['tb']);
		}
		$_GPC['id'] = intval($_GPC['id']);
		include $this->template('myoutorder');
	}
	public function doMobileMyoutorderlis()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		$pindex = max(1, intval($_GPC['ab']));
		$psize = 10;
		$type = intval($_GPC['clid']);
		$tj1 = "";
		$tj2 = array(':uniacid' => $_W['uniacid'], ':fagentid' => $agent['id']);
		if ($type != '888') {
			$tj1 .= " AND status=:status ";
			$tj2[':status'] = $type;
		}
		$atime = strtotime($_GPC['atime']);
		$btime = strtotime($_GPC['btime']) + 3600 * 24;
		if ($atime > 100000000) {
			$tj1 .= " AND ordertime>=:kst ";
			$tj2[':kst'] = $atime;
		}
		if ($btime > 100000000 && $btime > $atime) {
			$tj1 .= " AND ordertime<:jst ";
			$tj2[':jst'] = $btime;
		}
		$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' AND fagentid=:fagentid ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
		if (empty($list)) {
			exit(1);
		}
		foreach ($list as &$row) {
			$list[$row['id']]['ogoods'] = pdo_fetchall("SELECT g.id,o.total,o.price,o.goodsname,o.optionname,g.sthumb,g.unit FROM " . tablename($this->tb_ordergoods) . " o left join " . tablename($this->tb_goods) . " g on o.goodsid=g.id " . " WHERE o.orderid='{$row['id']}'");
		}
		if (in_array('fw', $_W['setting']['userapps:m'])) {
			$issm = 1;
		}
		include $this->template('myoutorder_lis');
	}
	public function doMobileOrdereditout()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl();
		$operation = $_GPC['op'];
		$id = intval($_GPC['id']);
		$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND id=:id AND fagentid=:fagentid', array(':uniacid' => $_W['uniacid'], ':id' => $id, ':fagentid' => $agent['id']));
		$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid AND zt='1'", array(':agentid' => $agent['id'], ':uniacid' => $_W['uniacid']));
		if (is_serialized($prin['prinset'])) {
			$prin['prinset'] = iunserializer($prin['prinset']);
		} else {
			$prin['prinset'] = array();
		}
		if (is_serialized($prin['princss'])) {
			$prin['princss'] = iunserializer($prin['princss']);
		} else {
			$prin['princss'] = array();
		}
		if ($operation == 'qx') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', '', 'err');
			}
			if ($order['status'] != '1') {
				$this->zm_message('此订单不是待付款订单！', '', 'err');
			}
			if (TIMESTAMP - 86400 < $order['addtime']) {
				$this->zm_message('你不能取消状态24小时内的订单！', '', 'err');
			}
			pdo_update($this->tb_order, array('status' => '0', 'lasttime' => TIMESTAMP), array('id' => $id));
			$this->zm_message('操作成功，订单已取消！', '', 'success');
		} elseif ($operation == 'fh') {
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			if ($order['status'] != '2' && $order['status'] != '3') {
				message(array('zt' => 1, 'ts' => '此订单不是待发货订单！'), '', 'ajax');
			}
			if ($order['isdf']) {
				message(array('zt' => 1, 'ts' => '此订单已经转由他人代发！'), '', 'ajax');
			}
			$rm = '订单：' . $order['orderno'];
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock($agent['id'], $ogo['goodsid'], $ogo['total'], 0, $ogo['optionid'], $rm);
			}
			setting_load('zmcn:kuaidi');
			$_W['setting']['zmcn:kuaidi']['my'] = $_GPC['zen'];
			if ($order['yorder'] > 0) {
				pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['nt'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['eid']], 'exptime' => TIMESTAMP, 'lasttime' => TIMESTAMP), array('yorder' => $order['yorder'], 'id' => $order['yorder']), 'OR');
			} else {
				pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['nt'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['eid']], 'exptime' => TIMESTAMP, 'lasttime' => TIMESTAMP), array('id' => $id));
			}
			$dagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => '订单发货');
			$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $dagent['name'] . ' ' . $dagent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '卖方发货');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(22, $dagent['openid'], $tihuan, $url);
			}
			message(array('zt' => 0, 'ts' => '发货操作成功！'), '', 'ajax');
		} elseif ($operation == 'sfh') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', '', 'err');
			}
			if ($order['status'] != '2' && $order['status'] != '3') {
				$this->zm_message('此订单不是待发货订单！', '', 'err');
			}
			if (!in_array('fw', $_W['setting']['userapps:m'])) {
				$this->zm_message('系统没开通此功能！', '', 'err');
			}
			if ($order['isdf']) {
				$this->zm_message('此订单已经转由他人代发！', '', 'err');
			}
			$rm = '订单：' . $order['orderno'];
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			setting_load('zmcn:kuaidi');
			$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
			if (is_serialized($list['k'])) {
				$codeset = iunserializer($list['k']);
			} else {
				$codeset = array();
			}
			$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
			if (checksubmit()) {
				$_W['setting']['zmcn:kuaidi']['my'] = $_GPC['mykd'];
				if ($order['yorder']) {
					pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['sendtype'], 'expsn' => $_GPC['expsn'], 'expid' => $_GPC['expid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['expid']], 'exptime' => TIMESTAMP, 'lasttime' => TIMESTAMP), array('yorder' => $order['yorder'], 'id' => $order['yorder']), 'OR');
					$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE id=:id AND uniacid=:uniacid', array(':id' => $order['yorder'], ':uniacid' => $_W['uniacid']));
				} else {
					pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['sendtype'], 'expsn' => $_GPC['expsn'], 'expid' => $_GPC['expid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['expid']], 'exptime' => TIMESTAMP, 'lasttime' => TIMESTAMP), array('id' => $id));
				}
				$dagent = array();
				if ($order['agentid']) {
					$dagent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND uniacid=:uniacid', array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
				}
				if (!empty($_GPC['tiaomu']) && !empty($dagent['id'])) {
					$fwms = explode("\n", $_GPC['tiaomu']);
					foreach ($fwms as $co) {
						if (!empty($co)) {
							$co = str_replace(strtoupper($codeset['m'][1]), "", strtoupper(trim($co))) . "000000000000000000000000000000000000";
							$co = substr($co, 0, $len);
							$batchid = substr($co, 0, (int) $codeset['m'][2]);
							$batch = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_batch') . " WHERE uniacid=:uniacid AND batch=:batch", array(':uniacid' => $_W['uniacid'], ':batch' => $batchid));
							if (!empty($batch['sbox'])) {
								$lensb = strlen($batch['sbox']);
								$lsno = (int) substr($co, 0 - $lensb);
								if (empty($lsno)) {
									$colen = $len - $lensb;
									$co = substr($co, 0, $colen);
								}
								if (!empty($batch['bbox'])) {
									$lenbb = strlen($batch['bbox']);
									$lsno = (int) substr($co, 0 - $lenbb);
									if (empty($lsno)) {
										$colen = $colen - $lenbb;
										$co = substr($co, 0, $colen);
									}
								}
							}
							$co = strtoupper($codeset['m'][1]) . $co;
							$da = array('agna' => $dagent['name'], 'agtel' => $dagent['phone'], 'agwx' => $dagent['wechat']);
							$da = array('uniacid' => $_W['uniacid'], 'batchid' => $batch['id'], 'code' => trim($co), 'type' => '9', 'param' => iserializer($da), 'una' => $agent['name'], 'uid' => $order['agentid'], 'Utp' => '3', 'ip' => $_W['clientip'], 'addtime' => TIMESTAMP);
							pdo_insert('zmcn_fw_basy', $da);
						}
					}
				}
				$tihuan = array();
				$tihuan[] = array('title' => '[订单状态]', 'value' => '订单发货');
				$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
				$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
				$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
				$tihuan[] = array('title' => '[订货方]', 'value' => $dagent['name'] . ' ' . $dagent['phone']);
				$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
				$tihuan[] = array('title' => '[订单类型]', 'value' => '卖方发货');
				$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
				$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
				if (function_exists('zmcn_sendTplNotice')) {
					zmcn_sendTplNotice(22, $dagent['openid'], $tihuan, $url);
				}
				$this->zm_message('发货操作成功！', $this->createMobileUrl('myoutorder', array('tm' => TIMESTAMP)), 'success');
			}
			include $this->template('myoutorder.sao');
		} elseif ($operation == 'del') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', '', 'err');
			}
			if ($order['status'] != '0') {
				$this->zm_message('此订单不是取消状态，不能删除！', '', 'err');
			}
			$result = pdo_delete($this->tb_order, array('id' => $id));
			$result = pdo_delete($this->tb_ordergoods, array('orderid' => $id));
			$this->zm_message('操作成功，订单已删除！', '', 'success');
		} elseif ($operation == 'xiadan') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', '', 'err');
			}
			if ($order['status'] != '3') {
				$this->zm_message('此订单不在状态，不能复制！', '', 'err');
			}
			$ddn = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND agentid=:agentid AND yorder=:yorder', array(':uniacid' => $_W['uniacid'], ':yorder' => $id, ':agentid' => $agent['id']));
			if ($ddn['id']) {
				$this->zm_message('代发订单已经存在，不能重复下单！', '', 'err');
			}
			$ood = $order;
			$fagent = $this->getdinh($agent['fid']);
			unset($ood['id']);
			$ood['agentid'] = $agent['id'];
			$ood['fagentid'] = $fagent['id'];
			$ood['orderno'] = $this->getorderno();
			$ood['openid'] = $openid;
			$ood['sendfee'] = $ood['isdf'] = '0';
			$ood['status'] = '1';
			$ood['ordertime'] = $ood['addtime'] = $ood['lasttime'] = TIMESTAMP;
			$ood['paytime'] = $ood['cktime'] = $ood['exptime'] = $ood['oktime'] = '0';
			if ((int) $ood['yorder'] == '0') {
				$ood['yorder'] = $id;
			}
			pdo_insert($this->tb_order, $ood);
			$ood['id'] = pdo_insertid();
			pdo_update($this->tb_order, array('isdf' => $ood['id'], 'lasttime' => TIMESTAMP), array('id' => $id));
			$pric = 'c' . $agent['level'];
			$zhongj = 0;
			$weight = $ood['weight'];
			$dss = pdo_fetchall("SELECT o.goodsid,o.optionid,o.total,o.goodsname,o.optionname,g.prices FROM " . tablename($this->tb_ordergoods) . " o left join " . tablename($this->tb_goods) . " g on o.goodsid=g.id " . " WHERE o.orderid='{$id}'");
			foreach ($dss as $go) {
				$goprices = iunserializer($go['prices']);
				unset($go['prices']);
				$npric = $goprices[$pric];
				if ($npric >= 0) {
					$hd = array('yjid' => $ood['fagentid'] == $agent['gid'] ? 0 : $agent['gid'], 'yja' => $goprices['ya' . $agent['level']], 'yjb' => $goprices['yb' . $agent['level']], 'yjc' => $goprices['yc' . $agent['level']], 'jf' => (int) $goprices['f' . $agent['level']], 'cl' => (int) $goprices['t' . $agent['level']]);
					$da = array('uniacid' => $_W['uniacid'], 'orderid' => $ood['id'], 'goodsid' => $go['goodsid'], 'goodsname' => $go['goodsname'], 'price' => $npric, 'total' => $go['total'], 'optionid' => $go['optionid'], 'optionname' => $go['optionname'], 'addtime' => TIMESTAMP, 'hd' => iserializer($hd));
					pdo_insert($this->tb_ordergoods, $da);
					$zhongj += $npric * $go['total'];
				}
			}
			$da = array('goodsprice' => $zhongj, 'price' => $zhongj + $ood['sendprice']);
			$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid AND zt='1'", array(':agentid' => $ood['fagentid'], ':uniacid' => $_W['uniacid']));
			if (is_serialized($prin['prinset'])) {
				$prin['prinset'] = iunserializer($prin['prinset']);
			} else {
				$prin['prinset'] = array();
			}
			if (is_serialized($prin['princss'])) {
				$prin['princss'] = iunserializer($prin['princss']);
			} else {
				$prin['princss'] = array();
			}
			if ((int) $prin['laiy'] == '2') {
				$ood['dhf'] = $agent;
				$ood['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $ood['fagentid']));
				$ood['ogoods'] = $dss;
				$a = $this->doprint($ood, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $ood['id']));
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => '待付款');
			$tihuan[] = array('title' => '[订单号]', 'value' => $ood['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $ood['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $da['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $agent['name'] . ' ' . $agent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $ood['consignee'] . ' ' . $ood['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '订单转发');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", TIMESTAMP));
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $ood['id'] . '&do=myoutorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(21, $fagent['openid'], $tihuan, $url);
			}
			$neir = array('articles' => array(array('title' => urlencode('您的订单已提交，请您尽快与卖方联系付款和发货！'), 'description' => urlencode("订单号：" . $ood['orderno'] . "\n货品数量：" . $ood['com'] . "\n订单金额：" . $da['price'] . "\n收货方：" . $ood['consignee'] . " " . $ood['tel'] . "\n订单时间：" . date("Y-m-d H:i:s", TIMESTAMP) . "\n-------------------------\n卖货方资料：\n名称：" . $fagent['name'] . "\n电话：" . $fagent['phone'] . "\n微信：" . $fagent['wechat']), 'url' => $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $ood['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect')));
			if (function_exists('zmcn_sendkfNotice')) {
				$abc = zmcn_sendkfNotice($openid, 'news', 0, $tihuan, $neir);
			}
			$this->zm_message('请将货款转账后在这里上传付款凭证！', $this->createMobileUrl('orderedit', array('op' => 'fk', 'id' => $ood['id'])), 'success', '订单提交成功', '进入确认付款');
		} elseif ($operation == 'dk') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', '', 'err');
			}
			if ($order['status'] != '2') {
				$this->zm_message('此订单不是待查款订单！', '', 'err');
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock($agent['id'], $ogo['goodsid'], $ogo['total'], 3, $ogo['optionid'], 'D' . $rm);
			}
			$da = array('status' => '3', 'cktime' => TIMESTAMP, 'lasttime' => TIMESTAMP);
			if (empty($order['paytime'])) {
				$da['paytime'] = TIMESTAMP;
			}
			if ((int) $prin['laiy'] == '3') {
				$order['status'] = '3';
				$order['dhf'] = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => $order['agentid']));
				$order['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $agent['id']));
				$a = $this->doprint($order, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $id));
			$this->zm_message('操作成功，订单已核对到款！', '', 'success');
		} elseif ($operation == 'dh') {
			if (empty($order['id'])) {
				$this->zm_message('查无此订单！', '', 'err');
			}
			if ($order['status'] != '2') {
				$this->zm_message('此订单不是待查款订单！', '', 'err');
			}
			$rm = '订单：' . $order['orderno'];
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			if ($order['agentid']) {
				foreach ($order['ogoods'] as $ogo) {
					$this->getagstock($order['agentid'], $ogo['goodsid'], $ogo['total'], 4, $ogo['optionid'], $rm);
				}
			}
			pdo_update($this->tb_order, array('status' => '1', 'lasttime' => TIMESTAMP), array('id' => $id));
			$this->zm_message('操作成功，订单已打回！', '', 'success');
		} elseif ($operation == 'dy') {
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			if (empty($prin['id'])) {
				message(array('zt' => 1, 'ts' => '您没有可用打印机！'), '', 'ajax');
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			$order['dhf'] = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => $order['agentid']));
			$order['aguqr'] = pdo_fetchcolumn("SELECT url FROM " . tablename('qrcode') . " WHERE uniacid = '{$_W['uniacid']}' AND scene_str=:scene_str", array(':scene_str' => 'ZMCN_CER_ID_' . $agent['id']));
			$a = $this->doprint($order, $prin);
			if ($a == FALSE) {
				message(array('zt' => 1, 'ts' => '打印出现未知错误！'), '', 'ajax');
			}
			if (!empty($a['responseCode'])) {
				message(array('zt' => 1, 'ts' => $a['msg']), '', 'ajax');
			}
			pdo_update($this->tb_order, array('pinstatus +=' => 1, 'lasttime' => TIMESTAMP), array('id' => $id));
			message(array('zt' => 0, 'ts' => '打印操作成功！'), '', 'ajax');
		} elseif ($operation == 'gaij') {
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			if ($order['status'] != '1') {
				$this->zm_message('此订单不是改价状态，不能改价！', '', 'err');
			}
			$order['ogoods'] = pdo_fetchall("SELECT id,goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			$dagent = array();
			if (!empty($order['agentid'])) {
				$dagent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND uniacid=:uniacid', array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
			}
			if (checksubmit()) {
				foreach ($_GPC['to'] as $toid => $ogo) {
					$_GPC['to'][$toid] = (int) $ogo;
				}
				$com = array_sum($_GPC['to']);
				if ($com < 1) {
					$this->zm_message('订单不能没有商品，如果全不要请取消订单！', '', 'err');
				}
				$sum = 0;
				foreach ($order['ogoods'] as $ogo) {
					$total = $_GPC['to'][$ogo['id']];
					if ($total) {
						$price = number_format($_GPC['pr'][$ogo['id']], 2, ".", "");
						pdo_update($this->tb_ordergoods, array('price' => $price, 'total' => $total), array('id' => $ogo['id']));
						$sum += $total * $price;
					} else {
						pdo_delete($this->tb_ordergoods, array('id' => $ogo['id']));
					}
				}
				$goodsprice = $sum * 1;
				$sendfee = number_format($_GPC['sendfee'], 2, ".", "");
				$youhje = number_format($_GPC['youhje'], 2, ".", "");
				$price = $sum - $youhje + $sendfee;
				$da = array('goodsprice' => $goodsprice, 'price' => $price, 'sendfee' => $sendfee, 'sendprice' => $sendfee, 'youhje' => $youhje, 'com' => $com, 'lasttime' => TIMESTAMP);
				pdo_update($this->tb_order, $da, array('id' => $order['id']));
				$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
				if (function_exists('zmcn_sendTplNotice')) {
					zmcn_sendTplNotice(22, $dagent['openid'], $tihuan, $url);
				}
				$this->zm_message('改价操作成功！', '', 'success');
			}
			$goids = array_map(function ($a) {
				return $a['goodsid'];
			}, $order['ogoods']);
			array_unique($goids);
			$tj2 = " AND id IN (" . implode(',', $goids) . ") ";
			$goodss = pdo_fetchall('SELECT id,thumb,sthumb FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid " . $tj2 . " ", array(':uniacid' => $_W['uniacid']), 'id');
			include $this->template('ordergj');
		} elseif ($operation == 'xq') {
			if (empty($_GPC['vs'])) {
				$this->doMobileMyoutorder();
				return;
			}
			if (empty($order['id'])) {
				if ($openid == $settings['sys']['openid']) {
					$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND id=:id AND fagentid=:fagentid', array(':uniacid' => $_W['uniacid'], ':id' => $id, ':fagentid' => 0));
				} else {
					exit('查无此订单！');
				}
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			if ($order['status'] == 5 || $order['status'] == 6) {
				$order['status'] = 4;
			}
			setting_load('zmcn:kuaidi');
			$dagent = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
			include $this->template('orderxq');
		}
	}
	public function doMobileMyprinter()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl(array('js' => 'agent'));
		$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid", array(':agentid' => $agent['id'], ':uniacid' => $_W['uniacid']));
		if (is_serialized($prin['prinset'])) {
			$prin['prinset'] = iunserializer($prin['prinset']);
		} else {
			$prin['prinset'] = array();
		}
		if (is_serialized($prin['princss'])) {
			$prin['princss'] = iunserializer($prin['princss']);
		} else {
			$prin['princss'] = array();
		}
		if (checksubmit()) {
			$dat = $_GPC['prin'];
			if ($dat['prinserver'] == 'no') {
				unset($dat['prinserver']);
			} else {
				$_GPC['sb']['sn'] = trim($_GPC['sb']['sn']) ? trim($_GPC['sb']['sn']) : $this->zm_message('设备编码不能为空', '', 'err');
				$_GPC['sb']['key'] = trim($_GPC['sb']['key']) ? trim($_GPC['sb']['key']) : $this->zm_message('设备密钥不能为空', '', 'err');
				$dat['prinset'] = iserializer($_GPC['sb']);
			}
			$dat['zt'] = (int) $dat['zt'];
			$dat['princss'] = iserializer($_GPC['css']);
			if (empty($prin['id'])) {
				$dat['uniacid'] = $_W['uniacid'];
				$dat['agentid'] = $agent['id'];
				$dat['title'] = '代理商' . $agent['name'] . '打印机';
				$dat['addtime'] = TIMESTAMP;
				pdo_insert($this->tb_printer, $dat);
			} else {
				pdo_update($this->tb_printer, $dat, array('id' => $prin['id']));
			}
			$this->zm_message('打印机资料保存成功！', '', 'success');
		}
		include $this->template('myprinter');
	}
	public function doMobilesql()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl(array('js' => 'agent'));
		if (checksubmit()) {
			$goid = (int) $_GPC['goodid'];
			$atime = strtotime($_GPC['atime']);
			$btime = strtotime($_GPC['btime']) + 3600 * 24;
			$goods = pdo_fetch("SELECT id,name,description,sthumb FROM " . tablename($this->tb_goods) . " WHERE id='{$goid}'");
			$kin = (int) pdo_fetchcolumn("select sum(sl) from " . tablename($this->tb_agstockh) . " WHERE uniacid = :uniacid AND agentid=:agentid AND goodsid=:goodsid AND zt='1' AND addtime>=:atime AND addtime<:btime", array(':agentid' => $agent['id'], ':uniacid' => $_W['uniacid'], ':goodsid' => $goid, ':atime' => $atime, ':btime' => $btime));
			$kout = (int) pdo_fetchcolumn("select sum(sl) from " . tablename($this->tb_agstockh) . " WHERE uniacid = :uniacid AND agentid=:agentid AND goodsid=:goodsid AND zt='0' AND addtime>=:atime AND addtime<:btime", array(':agentid' => $agent['id'], ':uniacid' => $_W['uniacid'], ':goodsid' => $goid, ':atime' => $atime, ':btime' => $btime));
			include $this->template('sql.ind');
		} else {
			$list = pdo_fetchall("select goodsid,sum(stock) as st from " . tablename($this->tb_agstock) . " WHERE uniacid = :uniacid AND agentid=:agentid group by goodsid ORDER BY id DESC", array(':agentid' => $agent['id'], ':uniacid' => $_W['uniacid']), 'goodsid');
			foreach ($list as $sgo) {
				$list[$sgo['goodsid']]['name'] = pdo_fetchcolumn("SELECT name FROM " . tablename($this->tb_goods) . " WHERE id = :id", array(':id' => $sgo['goodsid']));
			}
			for ($i = 1; $i < 7; $i++) {
				$qs = mktime(0, 0, 0, date("m") - 6 + $i, 1, date("Y"));
				$jz = mktime(0, 0, 0, date("m") - 5 + $i, 1, date("Y"));
				$tj1 = " AND status>=:status AND paytime>=:qs AND paytime<:jz ";
				$tj2 = array(':uniacid' => $_W['uniacid'], ':status' => 3, ':agentid' => $agent['id'], ':qs' => $qs, ':jz' => $jz);
				$ac['xs'][$i] = (int) pdo_fetchcolumn('SELECT sum(goodsprice) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND fagentid=:agentid ' . $tj1, $tj2);
				$ac['jh'][$i] = (int) pdo_fetchcolumn('SELECT sum(goodsprice) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND agentid=:agentid ' . $tj1, $tj2);
				$ac['na'][$i] = date('Ym', $jz - 10);
			}
			include $this->template('sql');
		}
	}
	public function doMobilesqllis()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl(array('js' => 'agent'));
		$pindex = max(1, intval($_GPC['ab']));
		$psize = 10;
		$goid = (int) $_GPC['gid'];
		$atime = (int) $_GPC['ada'];
		$btime = (int) $_GPC['bda'];
		$io = (int) $_GPC['io'];
		$tj1 = "";
		$tj2 = array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id'], ':goodsid' => $goid, ':atime' => $atime, ':btime' => $btime);
		if ($io != '3') {
			$tj1 .= " AND zt=:zt ";
			$tj2[':zt'] = $io;
		}
		$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_agstockh) . ' WHERE uniacid=:uniacid ' . $tj1 . ' AND agentid=:agentid AND goodsid=:goodsid AND addtime>=:atime AND addtime<:btime ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
		if (empty($list)) {
			exit(1);
		}
		include $this->template('sql.lis');
	}
	public function doMobilesqlorder()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$op = (int) $_GPC['op'];
		$this->getmobiledl(array('js' => 'agent'));
		$ac = array();
		$timetp = array('ordertime', 'paytime', 'exptime', 'oktime', 'cktime');
		$io = array('收', '收', '付', '收', '收');
		$timeip = (int) $_GPC['timetp'];
		$status = '3';
		if ($op == '1') {
			$title = '12个月内销售月报';
			for ($i = 13; $i > 0; $i--) {
				$qs = mktime(0, 0, 0, date("m") - 13 + $i, 1, date("Y"));
				$jz = mktime(0, 0, 0, date("m") - 12 + $i, 1, date("Y"));
				$tj1 = " AND status>=:status AND fagentid=:fagentid AND " . $timetp[$timeip] . ">=:qs AND " . $timetp[$timeip] . "<:jz ";
				$tj2 = array(':uniacid' => $_W['uniacid'], ':status' => $status, ':fagentid' => $agent['id'], ':qs' => $qs, ':jz' => $jz);
				$ac[$i] = pdo_fetch('SELECT COUNT(*) as a , sum(com) as com ,sum(price) as price ,sum(goodsprice) as goodsprice  FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
				$ac[$i]['title'] = date('y年m月', $jz - 10);
				$qs = (int) $jz;
			}
		} elseif ($op == '2') {
			$title = '12个月内进货月报';
			for ($i = 13; $i > 0; $i--) {
				$qs = mktime(0, 0, 0, date("m") - 13 + $i, 1, date("Y"));
				$jz = mktime(0, 0, 0, date("m") - 12 + $i, 1, date("Y"));
				$tj1 = " AND status>=:status AND agentid=:agentid AND " . $timetp[$timeip] . ">=:qs AND " . $timetp[$timeip] . "<:jz ";
				$tj2 = array(':uniacid' => $_W['uniacid'], ':status' => $status, ':agentid' => $agent['id'], ':qs' => $qs, ':jz' => $jz);
				$ac[$i] = pdo_fetch('SELECT COUNT(*) as a , sum(com) as com ,sum(price) as price ,sum(goodsprice) as goodsprice  FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
				$ac[$i]['title'] = date('y年m月', $jz - 10);
				$qs = (int) $jz;
			}
		} elseif ($op == '3') {
			$jz = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
			$title = '30天销售日报';
			for ($i = 32; $i > 0; $i--) {
				$qs = $jz - 86400;
				$tj1 = " AND status>=:status AND fagentid=:fagentid AND " . $timetp[$timeip] . ">=:qs AND " . $timetp[$timeip] . "<:jz ";
				$tj2 = array(':uniacid' => $_W['uniacid'], ':status' => $status, ':fagentid' => $agent['id'], ':qs' => $qs, ':jz' => $jz);
				$ac[$i] = pdo_fetch('SELECT COUNT(*) as a , sum(com) as com ,sum(price) as price ,sum(goodsprice) as goodsprice  FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
				$ac[$i]['title'] = date('m月d日', $jz - 10);
				$jz = (int) $qs;
			}
		}
		include $this->template('sql.order');
	}
	public function doMobileYongjing()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl(array('js' => 'agent'));
		$id = (int) $_GPC['id'];
		if (!empty($id)) {
			$comm = pdo_fetch('SELECT * FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid AND (agentid=:agentid OR gid=:gid) AND id=:id', array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id'], ':gid' => $agent['id'], ':id' => $id));
			if (empty($comm['id'])) {
				message(array('zt' => 1, 'ts' => '查无此笔交易！'), '', 'ajax');
			}
		}
		if ($_GPC['op'] == 'dk') {
			if ($comm['agentid'] != $agent['id'] || $comm['isok'] != '1') {
				message(array('zt' => 1, 'ts' => '交易状态不对！'), '', 'ajax');
			}
			pdo_update($this->tb_commission, array('isok' => 2, 'oktime' => TIMESTAMP), array('id' => $id));
			message(array('zt' => 0, 'ts' => '操作成功，已核对到款！！'), '', 'ajax');
		} elseif ($_GPC['op'] == 'yf' && $settings['sys']['isye'] == '1') {
			if ($comm['gid'] != $agent['id'] || $comm['isok'] != '0') {
				message(array('zt' => 1, 'ts' => '交易状态不对！'), '', 'ajax');
			}
			$dagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $comm['agentid'], ':uniacid' => $_W['uniacid']));
			$duserid = mc_openid2uid($dagent['openid']);
			$userid = mc_openid2uid($openid);
			if (empty($userid) || empty($duserid)) {
				message(array('zt' => 1, 'ts' => '双方代理有一方没有绑定微信做不了余额结算！'), '', 'ajax');
			}
			$member = mc_fetch($userid, array('credit2', 'credit3'));
			if (empty($member['credit2']) || $member['credit2'] < $comm['commission']) {
				message(array('zt' => 1, 'ts' => '您的余额不足支付本次订单！'), '', 'ajax');
			}
			$isok = mc_credit_update($userid, 'credit2', 0 - $comm['commission'], array($_W['uid'], $comm['remark'], 'zmcn_order', 0, 0));
			if ($isok === true) {
				$isok = mc_credit_update($duserid, 'credit2', $comm['commission'], array($_W['uid'], $comm['remark'], 'zmcn_order', 0, 0));
			}
			if ($isok === true) {
				pdo_update($this->tb_commission, array('isok' => 1, 'jstime' => TIMESTAMP, 'jsje' => $comm['commission']), array('id' => $id));
				message(array('zt' => 0, 'ts' => '余额结算操作成功！！'), '', 'ajax');
			} else {
				message(array('zt' => 1, 'ts' => '出现错误，余额结算不成功！'), '', 'ajax');
			}
		} elseif ($_GPC['op'] == 'hk') {
			if ($comm['gid'] != $agent['id'] || $comm['isok'] != '0') {
				message(array('zt' => 1, 'ts' => '交易状态不对！'), '', 'ajax');
			}
			pdo_update($this->tb_commission, array('isok' => 1, 'jstime' => TIMESTAMP, 'jsje' => $comm['commission']), array('id' => $id));
			message(array('zt' => 0, 'ts' => '操作成功，线下汇款！！'), '', 'ajax');
		} elseif ($_GPC['op'] == 'xq') {
			if ($comm['gid']) {
				$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $comm['gid'], ':uniacid' => $_W['uniacid']));
			} else {
				$fagent = array('id' => 0, 'name' => '总部', 'phone' => $settings['gs']['kftel'], 'openid' => $settings['sys']['openid']);
			}
			$dagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $comm['agentid'], ':uniacid' => $_W['uniacid']));
			if ($comm['bagentid']) {
				$bagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $comm['bagentid'], ':uniacid' => $_W['uniacid']));
			} else {
				$bagent = array('id' => 0, 'name' => '非代理');
			}
			if ($comm['orderid']) {
				$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $comm['orderid']));
				$order['ogoods'] = pdo_fetchall("SELECT id,goodsid,total,goodsname,optionname,hd FROM " . tablename($this->tb_ordergoods) . " WHERE orderid=:orderid", array(':orderid' => $comm['orderid']), 'id');
				$yjmm = array('xzcsdfwsdefs', 'yja', 'yjb', 'yjc');
				$yjmm = $yjmm[$comm['dj']];
				foreach ($order['ogoods'] as &$sgo) {
					if (is_serialized($sgo['hd'])) {
						$hd = iunserializer($sgo['hd']);
					} else {
						$hd = array();
					}
					if (!empty($hd[$yjmm]) && CheckMoney($hd[$yjmm]) && $hd[$yjmm] > 0) {
						$sgo['yj'] = $hd[$yjmm] * $sgo['total'];
					} else {
						$sgo['yj'] = 0;
					}
				}
			} else {
			}
			include $this->template('yongjing.xq');
			exit;
		}
		$noin = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid AND agentid=:agentid AND isok<2', array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id']));
		$noout = (int) pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid AND gid=:gid AND isok=0', array(':uniacid' => $_W['uniacid'], ':gid' => $agent['id']));
		$allin = (int) pdo_fetchcolumn('SELECT SUM(commission) FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid AND agentid=:agentid', array(':uniacid' => $_W['uniacid'], ':agentid' => $agent['id']));
		$allout = (int) pdo_fetchcolumn('SELECT SUM(commission) FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid AND gid=:gid', array(':uniacid' => $_W['uniacid'], ':gid' => $agent['id']));
		include $this->template('yongjing');
	}
	public function doMobileYongjinglis()
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$this->getmobiledl(array('js' => 'agent'));
		$pindex = max(1, intval($_GPC['ab']));
		$psize = 10;
		$io = (int) $_GPC['io'];
		$tj1 = "";
		$tj2 = array(':uniacid' => $_W['uniacid']);
		if ($io == '1') {
			$tj1 .= " AND agentid=:agentid AND isok<2 ";
			$tj2[':agentid'] = $agent['id'];
		} elseif ($io == '2') {
			$tj1 .= " AND gid=:gid AND isok=0 ";
			$tj2[':gid'] = $agent['id'];
		} elseif ($io == '3') {
			$tj1 .= " AND agentid=:agentid ";
			$tj2[':agentid'] = $agent['id'];
		} elseif ($io == '4') {
			$tj1 .= " AND gid=:gid ";
			$tj2[':gid'] = $agent['id'];
		} else {
			$tj1 .= " AND (gid=:gid OR agentid=:agentid) ";
			$tj2[':gid'] = $agent['id'];
			$tj2[':agentid'] = $agent['id'];
		}
		$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
		if (empty($list)) {
			exit(1);
		}
		foreach ($list as &$comm) {
			if ($comm['bagentid']) {
				$comm['bagent'] = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $comm['bagentid'], ':uniacid' => $_W['uniacid']));
			}
		}
		include $this->template('yongjing.lis');
	}
	public function getdinh($fid)
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		$zhongbu = array('id' => 0, 'name' => '总部', 'phone' => $settings['gs']['kftel'], 'openid' => $settings['sys']['openid']);
		$setfa = (int) $settings['sys']['dhfa'];
		if (!empty($settings['gs']['ywid'])) {
			$zhongbu = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $settings['gs']['ywid'], ':uniacid' => $_W['uniacid']));
			if (empty($zhongbu['id'])) {
				$zhongbu = array('id' => 0, 'name' => '总部', 'phone' => $settings['gs']['kftel']);
			}
		}
		if (empty($setfa) || empty($fid)) {
			return $zhongbu;
		}
		$qz = pdo_fetchall('SELECT `id`,`supplier` FROM ' . tablename($this->tb_clas) . " WHERE uniacid=:uniacid AND id IN (" . (int) $agent['class'] . "," . (int) $agent['level'] . ")", array(':uniacid' => $_W['uniacid']));
		foreach ($qz as $cc) {
			if ($cc['supplier'] == '1') {
				return $zhongbu;
			}
		}
		$fagent = array();
		$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $fid, ':uniacid' => $_W['uniacid']));
		if (empty($fagent['id'])) {
			return $zhongbu;
		}
		if ($setfa == '1') {
			return $fagent;
		}
		if ($fagent['type'] == '3' && $fagent['cer_end_time'] > TIMESTAMP) {
			return $fagent;
		}
		if ($setfa == '2' || empty($fagent['fid'])) {
			return $zhongbu;
		}
		$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $fagent['fid'], ':uniacid' => $_W['uniacid']));
		if (empty($fagent['id'])) {
			return $zhongbu;
		}
		if ($fagent['type'] == '3' && $fagent['cer_end_time'] > TIMESTAMP) {
			return $fagent;
		}
		if ($setfa == '3' || empty($fagent['fid'])) {
			return $zhongbu;
		}
		$fagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $fagent['fid'], ':uniacid' => $_W['uniacid']));
		if (empty($fagent['id'])) {
			return $zhongbu;
		}
		if ($fagent['type'] == '3' && $fagent['cer_end_time'] > TIMESTAMP) {
			return $fagent;
		}
		return $zhongbu;
	}
	public function getorderno()
	{
		a:
		$no = 'FD' . date("Ymd") . substr('00000' . (TIMESTAMP - mktime(0, 0, 0, date("m"), date("d"), date("Y"))), -5) . mt_rand(10000, 99999);
		$row = pdo_fetchcolumn("SELECT id FROM " . tablename($this->tb_order) . " WHERE orderno = :orderno and uniacid = :uniacid", array(':uniacid' => $_W['uniacid'], ':orderno' => $no));
		if (!empty($row)) {
			goto a;
		}
		return $no;
	}
	public function zm_agentlevel($levelid)
	{
		global $_W;
		$levelid = (int) $levelid;
		if (!empty($levelid)) {
			$agentlevel = pdo_fetch('SELECT * FROM ' . tablename($this->tb_clas) . " WHERE uniacid=:uniacid AND type=2 AND id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $levelid));
			if (empty($agentlevel['id'])) {
				$agentlevel = array('title' => '还没分等级');
			}
		} else {
			$agentlevel = array('title' => '还没分等级');
		}
		if (is_serialized($agentlevel['sets'])) {
			$agentlevel['sets'] = iunserializer($agentlevel['sets']);
		} else {
			$agentlevel['sets'] = array();
		}
		return $agentlevel;
	}
	public function getagstock($uid, $gid, $sl, $zt = 0, $oid = 0, $rm = '', $tm = TIMESTAMP)
	{
		global $_W, $agent, $fans;
		$sto = array();
		$slk = array('stock -=', 'stock +=', 'yuin +=', 'yuout +=');
		$sl = (int) $sl;
		$zt = (int) $zt;
		if (empty($gid) || empty($sl)) {
			return 0;
		}
		if ($sl < 1) {
			return 0;
		}
		$sto = pdo_fetch('SELECT id,stock,yuin,yuout FROM ' . tablename($this->tb_agstock) . " WHERE uniacid=:uniacid AND agentid=:agentid AND goodsid=:goodsid AND optionid=:optionid", array(':uniacid' => $_W['uniacid'], ':agentid' => (int) $uid, ':goodsid' => (int) $gid, ':optionid' => (int) $oid));
		if (empty($sto['id'])) {
			$slk = array('stock', 'stock', 'yuin', 'yuout', 'yuin', 'yuout');
			$dat = array('uniacid' => $_W['uniacid'], 'agentid' => (int) $uid, 'goodsid' => (int) $gid, 'optionid' => (int) $oid);
			if (empty($zt)) {
				$dat['stock'] = 0 - $sl;
			} else {
				$dat[$slk[$zt]] = $sl;
			}
			$bd = pdo_insert($this->tb_agstock, $dat);
		} else {
			$slk = array('stock -=', 'stock +=', 'yuin +=', 'yuout +=', 'yuin -=', 'yuout -=');
			$dat = array($slk[$zt] => $sl);
			if (empty($zt)) {
				$dat['yuout -='] = $sl;
			} elseif ($zt == '1') {
				$dat['yuin -='] = $sl;
			}
			$bd = pdo_update($this->tb_agstock, $dat, array('id' => $sto['id']));
			$dat['stock'] = pdo_getcolumn($this->tb_agstock, array('id' => $sto['id']), 'stock');
		}
		if (!empty($bd) && $zt < '2') {
			if (empty($agent['id']) && !empty($fans['uid'])) {
				$agent['id'] = $fans['uid'];
			}
			$dat = array('uniacid' => $_W['uniacid'], 'agentid' => (int) $uid, 'goodsid' => (int) $gid, 'optionid' => (int) $oid, 'zt' => $zt, 'sl' => $sl, 'stock' => (int) $dat['stock'], 'cid' => $agent['id'], 'addtime' => $tm, 'remark' => $rm);
			$bd = pdo_insert($this->tb_agstockh, $dat);
		}
		return 1;
	}
	public function getpaybd($fkid, $money = 0, $zt = 1, $qt = array(), $rm = '')
	{
		global $_W, $_GPC, $agent, $fans;
		$fkid = (int) $fkid;
		$zt = (int) $zt;
		if (empty($fkid) || empty($money)) {
			return 0;
		}
		if (empty($agent['id']) && !empty($fans['uid'])) {
			$cid = $fans['uid'];
			$ctp = '0';
		} else {
			$cid = $agent['id'];
			$ctp = '1';
		}
		$fkfs = (int) $_GPC['fk'];
		if (!empty($_GPC['tm'])) {
			$paytime = strtotime($_GPC['tm']);
		}
		if (empty($paytime) || $paytime < 100000000) {
			$paytime = TIMESTAMP;
		}
		$account = pdo_fetch('SELECT * FROM ' . tablename($this->tb_account) . " WHERE uniacid=:uniacid AND id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $fkid));
		if (empty($account['id'])) {
			return 0;
		}
		$dat = array('lasttime' => TIMESTAMP);
		if (empty($zt)) {
			$dat['money -='] = $money;
			$account['money'] -= $money;
		} else {
			$dat['money +='] = $money;
			$account['money'] += $money;
		}
		$bd = pdo_update($this->tb_account, $dat, array('id' => $account['id']));
		$dat = array('uniacid' => $_W['uniacid'], 'ustp' => $qt['ustp'], 'usid' => $qt['usid'], 'odtp' => $qt['odtp'], 'odid' => $qt['odid'], 'fkfs' => $fkfs, 'fkid' => $account['id'], 'zt' => $zt, 'money' => $money, 'balance' => $account['money'], 'ctp' => $ctp, 'cid' => $cid, 'paytime' => $paytime, 'addtime' => TIMESTAMP, 'remark' => $_GPC['bz'] . $rm);
		if (empty($qt['Project'])) {
			if ($fkfs == '88') {
				$dat['Project'] = 1;
			}
		} else {
			$dat['Project'] = $qt['Project'];
		}
		$bd = pdo_insert($this->tb_payh, $dat);
		return 1;
	}
	public function getexpresslits($area = array(), $weight = 0, $fagid = 0, $expid = '')
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		$out = array('zt' => 1, 'ts' => '没有记录', 'express' => array());
		$out['deftprice'] = 'A';
		if (empty($settings['sys']['wlfa']) || $settings['sys']['wlfa'] == '1' && !empty($fagid)) {
			$out['zt'] = '88';
			return $out;
		}
		if (empty($area['province'])) {
			return $out;
		}
		if ($settings['sys']['wlfa'] == '2') {
			$fagid = '0';
		}
		$tj1 = '';
		$tj2 = array(':uniacid' => $_W['uniacid'], ':agentid' => (int) $fagid, ':province' => str_replace('市', '', $area['province']));
		if ($area['city']) {
			$tj1 = " AND ( city='' OR  city LIKE '%" . $area['city'] . "%' ) ";
		} else {
			$tj1 = " AND city='' ";
		}
		$dispatch = pdo_fetch('SELECT * FROM ' . tablename($this->tb_dispatch) . " WHERE uniacid=:uniacid AND agentid=:agentid AND province=:province " . $tj1 . " ORDER BY jd DESC", $tj2);
		if (is_serialized($dispatch['express'])) {
			$dispatch['express'] = iunserializer($dispatch['express']);
		}
		foreach ($dispatch['express'] as $spid => $spe) {
			$out['zt'] = '0';
			$spe['secondweight'] = floatval($spe['secondweight']);
			if ($spe['free']) {
				$out['express'][$spid] = array('expna' => $spe['expna'], 'expcom' => $spe['expcom'], 'expprice' => 0);
			} elseif ($spe['secondweight']) {
				$expressmoney = floatval($spe['firstprice']) + ceil((floatval($weight) - floatval($spe['firstweight'])) / $spe['secondweight']) * floatval($spe['secondprice']);
				$out['express'][$spid] = array('expna' => $spe['expna'], 'expcom' => $spe['expcom'], 'expprice' => max(round($expressmoney), round($spe['minprice'])));
			} else {
				$out['express'][$spid] = array('expna' => $spe['expna'], 'expcom' => $spe['expcom'], 'expprice' => max(round($spe['firstprice']), round($spe['minprice'])));
			}
		}
		if ($expid) {
			$expprice = (int) $out[$expid]['expprice'];
		} else {
			return $out;
		}
	}
	public function doWebOrder()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		$id = intval($_GPC['id']);
		$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid='0' AND zt='1'", array(':uniacid' => $_W['uniacid']));
		if (is_serialized($prin['prinset'])) {
			$prin['prinset'] = iunserializer($prin['prinset']);
		} else {
			$prin['prinset'] = array();
		}
		if (is_serialized($prin['princss'])) {
			$prin['princss'] = iunserializer($prin['princss']);
		} else {
			$prin['princss'] = array();
		}
		if (!empty($id)) {
			$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . " WHERE uniacid=:uniacid AND id=:id AND fagentid='0'", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if (empty($order['id'])) {
				message('未找到指定订单');
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			$order['dhf'] = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => $order['agentid']));
		}
		if ($_GPC['op'] == 'ud') {
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND iscer = '1' ", array(':uniacid' => $_W['uniacid']), 'id');
			$accounts = pdo_fetchall("SELECT id,title FROM " . tablename($this->tb_account) . " WHERE uniacid='{$_W['uniacid']}' AND zt='1' AND `type`<7");
			setting_load('zmcn:kuaidi');
			if ($_GPC['acc'] == 'add') {
				$order = $_GPC['order'];
				$dagent = array();
				$times = $_GPC['time'];
				if (!empty($order['agentid'])) {
					$dagent = pdo_fetch('SELECT * FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND uniacid=:uniacid', array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
					if (empty($dagent['id'])) {
						message('代理商资料出错！', '', 'err');
					}
				}
				$goids = $_GPC['isck'];
				$gpids = $_GPC['gopid'];
				if (empty($goids)) {
					message('订单数据出错：没有商品！', '', 'err');
				}
				if (!empty($dagent['level'])) {
					$pric = 'c' . $dagent['level'];
				} else {
					$pric = 'productprice';
				}
				if (!empty($dagent['level'])) {
					$pric = 'c' . $dagent['level'];
				} else {
					$pric = 'productprice';
				}
				$_W['setting']['zmcn:kuaidi']['my'] = $_GPC['mykd'];
				$order['fagentid'] = 0;
				$order['orderno'] = $this->getorderno();
				$order['uniacid'] = $_W['uniacid'];
				$order['openid'] = $dagent['openid'];
				$order['status'] = (int) $order['status'];
				$order['sendfee'] = $order['sendprice'] = (int) $order['sendfee'];
				$order['expcom'] = $_W['setting']['zmcn:kuaidi'][$order['expid']];
				$order['province'] = empty($order['province']) ? $dagent['province'] : $order['province'];
				$order['city'] = empty($order['city']) ? $dagent['city'] : $order['city'];
				$order['district'] = empty($order['district']) ? $dagent['district'] : $order['district'];
				$order['address'] = empty($order['address']) ? $dagent['address'] : $order['address'];
				$order['tel'] = empty($order['tel']) ? $dagent['phone'] : $order['tel'];
				$dagent['user'] = empty($dagent['user']) ? $dagent['name'] : $dagent['user'];
				$order['consignee'] = empty($order['consignee']) ? $dagent['user'] : $order['consignee'];
				if ($order['status'] >= '2') {
					$order['paytime'] = strtotime($times['paytime']);
				}
				if ($order['status'] >= '3') {
					$order['cktime'] = strtotime($times['paytime']);
				}
				if ($order['status'] >= '4') {
					$order['exptime'] = strtotime($times['exptime']);
				}
				if ($order['status'] >= '7') {
					$order['oktime'] = TIMESTAMP;
				}
				$order['ordertime'] = strtotime($times['ordertime']);
				$order['addtime'] = TIMESTAMP;
				$order['lasttime'] = TIMESTAMP;
				pdo_insert($this->tb_order, $order);
				$order['id'] = pdo_insertid();
				$zhongj = 0;
				$weight = 0;
				$slhj = 0;
				foreach ($goids as $i => $goid) {
					$gpid = $gpids[$i];
					$sul = (int) $_GPC['count'][$goid . '_' . $gpid];
					$gna = $_GPC['gna'][$goid . '_' . $gpid];
					$gop = $_GPC['gop'][$goid . '_' . $gpid];
					$gpr = $_GPC['gpr'][$goid . '_' . $gpid];
					if (!empty($sul) && $gpr > 0) {
						$goods_prices = iunserializer($goodss[$goid]['prices']);
						$hd = array('yjid' => $dagent['gid'], 'yja' => $goods_prices['ya' . $dagent['level']], 'yjb' => $goods_prices['yb' . $dagent['level']], 'yjc' => $goods_prices['yc' . $dagent['level']], 'jf' => (int) $goods_prices['f' . $dagent['level']], 'cl' => (int) $goods_prices['t' . $dagent['level']]);
						$da = array('uniacid' => $_W['uniacid'], 'orderid' => $order['id'], 'goodsid' => $goid, 'goodsname' => $gna, 'price' => $gpr, 'total' => $sul, 'addtime' => TIMESTAMP, 'hd' => iserializer($hd));
						$da['optionname'] = $gop;
						$da['optionid'] = $gpid;
						pdo_insert($this->tb_ordergoods, $da);
						$zhongj += $gpr * $sul;
						$weight += $goodss[$goid]['weight2'] * $sul;
						$order['ogoods'][$goid . '_' . $gpid] = $da;
						$slhj += $sul;
					}
				}
				$da = array('goodsprice' => $zhongj, 'weight' => $weight, 'com' => $slhj);
				$rm = '订单：' . $order['orderno'];
				if ($order['status'] >= '2' && !empty($order['agentid'])) {
					foreach ($order['ogoods'] as $ogo) {
						$this->getagstock($order['agentid'], $ogo['goodsid'], $ogo['total'], 2, $ogo['optionid'], 'E' . $rm);
					}
				}
				if ($order['status'] >= '3') {
					foreach ($order['ogoods'] as $ogo) {
						$this->getagstock($order['fagentid'], $ogo['goodsid'], $ogo['total'], 3, $ogo['optionid'], 'F' . $rm);
					}
				}
				if ($order['status'] >= '4') {
					foreach ($order['ogoods'] as $ogo) {
						$this->getagstock($order['fagentid'], $ogo['goodsid'], $ogo['total'], 0, $ogo['optionid'], $rm);
					}
				}
				if ($order['status'] >= '7' && !empty($order['agentid'])) {
					foreach ($order['ogoods'] as $ogo) {
						$this->getagstock($order['agentid'], $ogo['goodsid'], $ogo['total'], 1, $ogo['optionid'], $rm);
					}
				}
				if ($order['status'] >= '3') {
					$_GPC['fk'] = $order['paytype'];
					$_GPC['tm'] = $times['paytime'];
					$qt = array('ustp' => 0, 'usid' => $order['agentid'], 'odtp' => 0, 'odid' => $order['id']);
					$fkid = (int) $_GPC['fkid'];
					$this->getpaybd($fkid, $order['price'], 1, $qt, $rm);
				}
				if (!empty($_GPC['isdy'])) {
					if (empty($dagent['id'])) {
						$order['dhf'] = array('name' => $order['consignee'], 'phone' => $order['tel']);
					} else {
						$order['dhf'] = $dagent;
					}
					$a = $this->doprint($order, $prin);
					$da['pinstatus +='] = 1;
				}
				pdo_update($this->tb_order, $da, array('id' => $order['id']));
				$tihuan = array();
				$tihuan[] = array('title' => '[订单状态]', 'value' => $_W['zm_o_st'][$order['status']]);
				$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
				$tihuan[] = array('title' => '[货品数量]', 'value' => $da['com']);
				$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
				$tihuan[] = array('title' => '[订货方]', 'value' => $dagent['name'] . ' ' . $dagent['phone']);
				$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
				$tihuan[] = array('title' => '[订单类型]', 'value' => '总部建单');
				$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
				$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
				if (function_exists('zmcn_sendTplNotice')) {
					zmcn_sendTplNotice(21, $dagent['openid'], $tihuan, $url);
				}
				message('订单提交成功！', '', 'success');
			}
			include $this->template('order.add');
		} elseif ($_GPC['op'] == 'ag') {
			$sgg = pdo_fetch("SELECT id,name,phone,province,city,district,address FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => intval($_GPC['uid'])));
			$sgg['zt'] = 0;
			message($sgg, '', 'ajax');
		} elseif ($_GPC['op'] == 'go') {
			$dagent = pdo_fetch('SELECT id,level FROM ' . tablename($this->tb_user) . ' WHERE id=:id AND uniacid=:uniacid', array(':id' => intval($_GPC['uid']), ':uniacid' => $_W['uniacid']));
			if (!empty($dagent['level'])) {
				$pric = 'c' . $dagent['level'];
			} else {
				$pric = 'productprice';
			}
			if (strtoupper($_GPC['gid']) == 'TM') {
				$goods = pdo_fetch('SELECT id,prices,spec,name,title,productprice,isspec FROM ' . tablename($this->tb_goods) . " WHERE uniacid = '" . $_W['uniacid'] . "' AND `tmcode`=:tmcode", array(':tmcode' => base_convert($_GPC['gtm'], 10, 10)));
			} else {
				$goods = pdo_fetch('SELECT id,prices,spec,name,title,productprice,isspec FROM ' . tablename($this->tb_goods) . " WHERE uniacid = '" . $_W['uniacid'] . "' AND id=:id ", array(':id' => intval($_GPC['gid'])));
			}
			$goods['options'] = '';
			if (!empty($goods['isspec'])) {
				$options = pdo_fetchall('SELECT id,title FROM ' . tablename($this->tb_goodsoption) . ' WHERE goodsid=:goodsid', array(':goodsid' => $goods['id']));
				foreach ($options as $go) {
					$goods['options'] .= "<option value='" . $go['title'] . "' data-optionid='" . $go['id'] . "'>" . $go['title'] . "</option>";
				}
			}
			$goods['name'] = '';
			$goods['prices'] = iunserializer($goods['prices']);
			$npric = $pric == 'productprice' ? $goods['productprice'] : $goods['prices'][$pric];
			$goods['prices'] = empty($npric) ? $goods['productprice'] : $npric;
			$goods['name'] = empty($goods['name']) ? $goods['title'] : $goods['name'];
			$goods['zt'] = 0;
			message($goods, '', 'ajax');
		} elseif ($_GPC['op'] == 'xq') {
			$order['ogoods'] = pdo_fetchall("SELECT g.id,o.total,o.price,o.goodsname,o.optionname,o.optionid,g.sthumb,g.unit FROM " . tablename($this->tb_ordergoods) . " o left join " . tablename($this->tb_goods) . " g on o.goodsid=g.id " . " WHERE o.orderid='{$order['id']}'");
			include $this->template('order.xq');
		} elseif ($_GPC['op'] == 'dy') {
			if (empty($prin['id'])) {
				message(array('zt' => 1, 'ts' => '您没有可用打印机！'), '', 'ajax');
			}
			$a = $this->doprint($order, $prin);
			if ($a == FALSE) {
				message(array('zt' => 1, 'ts' => '打印出现未知错误！'), '', 'ajax');
			}
			if (!empty($a['responseCode'])) {
				message(array('zt' => 1, 'ts' => $a['msg']), '', 'ajax');
			}
			pdo_update($this->tb_order, array('pinstatus +=' => 1, 'lasttime' => TIMESTAMP), array('id' => $id));
			message(array('zt' => 0, 'ts' => '打印操作成功！'), '', 'ajax');
		} elseif ($_GPC['op'] == 'dk') {
			if ($order['status'] == '2' || $order['status'] == '1') {
			} else {
				message(array('zt' => 1, 'ts' => '此订单不是待查款订单！'), '', 'ajax');
			}
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock(0, $ogo['goodsid'], $ogo['total'], 3, $ogo['optionid'], 'G' . $rm);
			}
			$qt = array('ustp' => 0, 'usid' => $order['agentid'], 'odtp' => 0, 'odid' => $order['id']);
			if (CheckMoney($_GPC['je'])) {
				$money = $_GPC['je'];
			} else {
				$money = $order['price'];
			}
			$fkid = (int) $_GPC['zh'];
			$rm = '订单：' . $order['orderno'];
			$this->getpaybd($fkid, $money, 1, $qt, $rm);
			$da = array('status' => '3', 'paytype' => (int) $_GPC['fk'], 'cktime' => TIMESTAMP, 'lasttime' => TIMESTAMP);
			if (empty($order['paytime'])) {
				$da['paytime'] = TIMESTAMP;
			}
			if ((int) $prin['laiy'] == '3') {
				$order['status'] = '3';
				$a = $this->doprint($order, $prin);
				$da['pinstatus +='] = 1;
			}
			pdo_update($this->tb_order, $da, array('id' => $id));
			message(array('zt' => 0, 'ts' => '操作成功，订单已核对到款！'), '', 'ajax');
		} elseif ($_GPC['op'] == 'fh') {
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			if ($order['status'] != '2' && $order['status'] != '3') {
				message(array('zt' => 1, 'ts' => '此订单不是待发货订单！'), '', 'ajax');
			}
			$rm = '订单：' . $order['orderno'];
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock(0, $ogo['goodsid'], $ogo['total'], 0, $ogo['optionid'], $rm);
			}
			setting_load('zmcn:kuaidi');
			$_W['setting']['zmcn:kuaidi']['my'] = $_GPC['my'];
			if (!empty($_GPC['tm'])) {
				$ntime = strtotime($_GPC['tm']);
			}
			if (empty($ntime) || $ntime < 100000000) {
				$ntime = TIMESTAMP;
			}
			if ($order['yorder'] > 0) {
				pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['fs'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['eid']], 'exptime' => $ntime, 'lasttime' => TIMESTAMP), array('yorder' => $order['yorder'], 'id' => $order['yorder']), 'OR');
			} else {
				pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['fs'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['eid']], 'exptime' => $ntime, 'lasttime' => TIMESTAMP), array('id' => $id));
			}
			$dagent = array();
			$dagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
			if (!empty($_GPC['sm']) && !empty($dagent['id']) && in_array('fw', $_W['setting']['userapps:m'])) {
				$fwms = explode("\n", $_GPC['sm'] . "\n");
				$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
				if (is_serialized($list['k'])) {
					$codeset = iunserializer($list['k']);
				} else {
					$codeset = array();
				}
				$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
				foreach ($fwms as $co) {
					$co = trim($co);
					if (!empty($co)) {
						$co = str_replace(strtoupper($codeset['m'][1]), "", strtoupper($co)) . "000000000000000000000000000000000000";
						$co = substr($co, 0, $len);
						$batchid = substr($co, 0, (int) $codeset['m'][2]);
						$batch = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_batch') . " WHERE uniacid=:uniacid AND batch=:batch", array(':uniacid' => $_W['uniacid'], ':batch' => $batchid));
						if (!empty($batch['sbox'])) {
							$lensb = strlen($batch['sbox']);
							$lsno = (int) substr($co, 0 - $lensb);
							if (empty($lsno)) {
								$colen = $len - $lensb;
								$co = substr($co, 0, $colen);
							}
							if (!empty($batch['bbox'])) {
								$lenbb = strlen($batch['bbox']);
								$lsno = (int) substr($co, 0 - $lenbb);
								if (empty($lsno)) {
									$colen = $colen - $lenbb;
									$co = substr($co, 0, $colen);
								}
							}
						}
						$co = strtoupper($codeset['m'][1]) . $co;
						$da = array('agna' => $dagent['name'], 'agtel' => $dagent['phone'], 'agwx' => $dagent['wechat']);
						$da = array('uniacid' => $_W['uniacid'], 'batchid' => $batch['id'], 'code' => $co, 'type' => '9', 'param' => iserializer($da), 'una' => '', 'uid' => $order['agentid'], 'Utp' => '3', 'ip' => $_W['clientip'], 'addtime' => TIMESTAMP);
						pdo_insert('zmcn_fw_basy', $da);
					}
				}
			}
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => '订单发货');
			$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $dagent['name'] . ' ' . $dagent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '总部发货');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(22, $dagent['openid'], $tihuan, $url);
			}
			message(array('zt' => 0, 'ts' => '发货操作成功！'), '', 'ajax');
		} elseif ($_GPC['op'] == 'qx') {
			if ($order['status'] != '1') {
				message('此订单不是待付款订单！', '', 'error');
			}
			if (TIMESTAMP - 86400 < $order['addtime']) {
				message('你不能取消状态24小时内的订单！', '', 'error');
			}
			pdo_update($this->tb_order, array('status' => '0', 'lasttime' => TIMESTAMP), array('id' => $id));
			message('操作成功，订单已取消！', '', 'success');
		} elseif ($_GPC['op'] == 'del') {
			if ($order['status'] != '0') {
				message('此订单不是取消状态，不能删除！', '', 'error');
			}
			$result = pdo_delete($this->tb_order, array('id' => $id));
			$result = pdo_delete($this->tb_ordergoods, array('orderid' => $id));
			message('操作成功，订单已删除！', '', 'success');
		} elseif ($_GPC['op'] == 'dh') {
			if ($order['status'] != '2') {
				message(array('zt' => 1, 'ts' => '此订单不是待查款订单！'), '', 'ajax');
			}
			pdo_update($this->tb_order, array('status' => '1', 'lasttime' => TIMESTAMP), array('id' => $id));
			$rm = '订单：' . $order['orderno'];
			if ($order['agentid']) {
				foreach ($order['ogoods'] as $ogo) {
					$this->getagstock($order['agentid'], $ogo['goodsid'], $ogo['total'], 4, $ogo['optionid'], 'G' . $rm);
				}
			}
			message('操作成功，订单已打回！', '', 'success');
		} else {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 10;
			$type = intval($_GPC['clid']);
			$tj1 = "";
			$tj2 = array(':uniacid' => $_W['uniacid']);
			if ($type != '888' && $_GPC['clid'] != '') {
				$tj1 .= " AND status=:status ";
				$tj2[':status'] = $type;
			}
			if (!empty($settings['gs']['ywid'])) {
				$tj1 .= " AND fagentid IN (0," . (int) $settings['gs']['ywid'] . ") ";
			} else {
				$tj1 .= " AND fagentid=:fagentid ";
				$tj2[':fagentid'] = 0;
			}
			$tj = $_GPC['search'];
			if ($tj['tel'] != '') {
				$tj1 .= ' AND tel=:tel ';
				$tj2[':tel'] = $tj['tel'];
			}
			if ($tj['expid'] != '') {
				$tj1 .= ' AND expid=:expid ';
				$tj2[':expid'] = $tj['expid'];
			}
			if ($tj['agentid'] != '' && $tj['agentid'] != '-1') {
				$tj1 .= ' AND agentid=:agentid ';
				$tj2[':agentid'] = $tj['agentid'];
			}
			if ($tj['province'] != '' && $tj['province'] != '省/直辖市') {
				$tj1 .= ' AND province=:province ';
				$tj2[':province'] = $tj['province'];
			}
			if ($tj['city'] != '' && $tj['city'] != '市') {
				$tj1 .= ' AND city=:city ';
				$tj2[':city'] = $tj['city'];
			}
			if ($tj['district'] != '' && $tj['district'] != '区/县') {
				$tj1 .= ' AND district=:district ';
				$tj2[':district'] = $tj['district'];
			}
			if (!empty($tj['shday'])) {
				$timetp = array('ordertime', 'paytime', 'exptime', 'oktime', 'cktime');
				$stime = empty($tj['shday']['start']) ? strtotime('-1 month') : strtotime($tj['shday']['start']);
				$etime = empty($tj['shday']['end']) ? TIMESTAMP : strtotime($tj['shday']['end']) + 86399;
				$tj1 .= " AND " . $timetp[(int) $tj['timetp']] . ">=:stime AND " . $timetp[(int) $tj['timetp']] . "<=:etime ";
				$tj2[':stime'] = $stime;
				$tj2[':etime'] = $etime;
			}
			if ($tj['expsn'] != '') {
				$tj1 .= ' AND expsn LIKE :expsn ';
				$tj2[':expsn'] = "{$tj['expsn']}%";
			}
			if ($_GPC['submit'] == '2') {
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '订单导出时间') . date('Ymd_His') . '.csv');
				header('Cache-Control: max-age=0');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				$fp = fopen('php://output', 'a');
				$title = array('№', '订单号', '下单日期', '订货人', '卖货人', '订单量', '货款', '订单金额', '订单状态', '收货人', '收货电话', '省', '市', '县', '地址', '付款方式', '付款日期', '送货方式', '物流公司', '物流单号', '发货日期', '完成日期');
				foreach ($title as $i => $one) {
					$head[$i] = iconv('utf-8', 'gbk', $one);
				}
				fputcsv($fp, $head);
				$sql = 'SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				$tpid = array();
				foreach ($listall as $value) {
					if ($value['agentid'] && !in_array($value['agentid'], $tpid)) {
						$tpid[] = $value['agentid'];
					}
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				$fkfs = array('0' => '现金', '1' => '转账', '2' => '代收', '3' => '在线支付', '4' => '预存款支付', '88' => '期初');
				$sh8 = array('现买', '发货', '自提');
				foreach ($listall as $value) {
					$tmp_value = array($value['id'], $value['orderno'], date('Y-m-d', $value['ordertime']), empty($value['agentid']) ? '散客' : $agents[$value['agentid']]['name'], '总部', $value['com'], $value['goodsprice'], $value['price'], $_W['zm_o_st'][$value['status']], $value['consignee'], $value['tel'], $value['province'], $value['city'], $value['district'], $value['address'], $fkfs[$value['paytype']], empty($value['paytime']) ? '' : date('Y-m-d', $value['paytime']), $sh8[$value['sendtype']], $value['expcom'], $value['expsn'], empty($value['exptime']) ? '' : date('Y-m-d', $value['exptime']), empty($value['oktime']) ? '' : date('Y-m-d', $value['oktime']));
					foreach ($tmp_value as $j => $v) {
						$tmp_value[$j] = iconv('utf-8', 'gbk', $v);
					}
					fputcsv($fp, $tmp_value);
				}
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				exit;
			}
			$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
			$pager = pagination($total, $pindex, $psize);
			foreach ($list as $sgo) {
				$list[$sgo['id']]['dagent'] = array();
				$list[$sgo['id']]['dagent'] = pdo_fetch("SELECT name,phone FROM " . tablename($this->tb_user) . " WHERE id = :id", array(':id' => $sgo['agentid']));
			}
			$accounts = pdo_fetchall("SELECT id,title FROM " . tablename($this->tb_account) . " WHERE uniacid='{$_W['uniacid']}' AND zt='1' AND `type`<7 ");
			setting_load('zmcn:kuaidi');
			if (in_array('fw', $_W['setting']['userapps:m'])) {
				$code = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
				if (is_serialized($code['k'])) {
					$codeset = iunserializer($code['k']);
				} else {
					$codeset = array();
				}
				$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
			}
			include $this->template('order.list');
		}
	}
	public function doWebGuangkong()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		$id = intval($_GPC['id']);
		$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid='0' AND zt='1'", array(':uniacid' => $_W['uniacid']));
		if (is_serialized($prin['prinset'])) {
			$prin['prinset'] = iunserializer($prin['prinset']);
		} else {
			$prin['prinset'] = array();
		}
		if (is_serialized($prin['princss'])) {
			$prin['princss'] = iunserializer($prin['princss']);
		} else {
			$prin['princss'] = array();
		}
		if (!empty($id)) {
			$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . " WHERE uniacid=:uniacid AND id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if (empty($order['id'])) {
				message('未找到指定订单');
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			$order['dhf'] = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => $order['agentid']));
		}
		if ($_GPC['op'] == 'ud') {
		} elseif ($_GPC['op'] == 'xq') {
			$order['ogoods'] = pdo_fetchall("SELECT g.id,o.total,o.price,o.goodsname,o.optionname,o.optionid,g.sthumb,g.unit FROM " . tablename($this->tb_ordergoods) . " o left join " . tablename($this->tb_goods) . " g on o.goodsid=g.id " . " WHERE o.orderid='{$order['id']}'");
			include $this->template('order.xq');
		} elseif ($_GPC['op'] == 'del') {
			if ($order['status'] != '0') {
				message('此订单不是取消状态，不能删除！', '', 'error');
			}
			$result = pdo_delete($this->tb_order, array('id' => $id));
			$result = pdo_delete($this->tb_ordergoods, array('orderid' => $id));
			message('操作成功，订单已删除！', '', 'success');
		} else {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 10;
			$type = intval($_GPC['clid']);
			$tj1 = "";
			$tj2 = array(':uniacid' => $_W['uniacid']);
			$agent = array();
			if ($type != '888' && $_GPC['clid'] != '') {
				$tj1 .= " AND status=:status ";
				$tj2[':status'] = $type;
			} else {
				$tj1 .= " AND status>0 ";
			}
			if (!empty($_GPC['orid'])) {
				$fid = intval($_GPC['orid']);
				if ($fid > 0) {
					$agent = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => $fid));
					if ($_GPC['inup'] == '1') {
						$tj1 .= " AND agentid=:agentid ";
						$tj2[':agentid'] = $fid;
					} elseif ($_GPC['inup'] == '2') {
						$tj1 .= " AND fagentid=:fagentid ";
						$tj2[':fagentid'] = $fid;
					} else {
						$tj1 .= " AND (fagentid=:fagentid OR agentid=:agentid) ";
						$tj2[':fagentid'] = $fid;
						$tj2[':agentid'] = $fid;
					}
				} elseif (!empty($settings['gs']['ywid'])) {
					$tj1 .= " AND fagentid IN (0," . (int) $settings['gs']['ywid'] . ") ";
				} else {
					$tj1 .= " AND fagentid=:fagentid ";
					$tj2[':fagentid'] = 0;
				}
			}
			if (!empty($_GPC['shday'])) {
				$timetp = array('ordertime', 'paytime', 'exptime', 'oktime', 'cktime');
				$stime = empty($_GPC['shday']['start']) ? strtotime('-1 month') : strtotime($_GPC['shday']['start']);
				$etime = empty($_GPC['shday']['end']) ? TIMESTAMP : strtotime($_GPC['shday']['end']) + 86399;
				$tj1 .= " AND " . $timetp[(int) $_GPC['timetp']] . ">=:stime AND " . $timetp[(int) $_GPC['timetp']] . "<=:etime ";
				$tj2[':stime'] = $stime;
				$tj2[':etime'] = $etime;
			}
			if ($_GPC['submit'] == '2') {
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '订单导出时间') . date('Ymd_His') . '.csv');
				header('Cache-Control: max-age=0');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				$fp = fopen('php://output', 'a');
				$title = array('№', '订单号', '下单日期', '订货人', '卖货人', '订单量', '货款', '订单金额', '订单状态', '收货人', '收货电话', '省', '市', '县', '地址', '付款方式', '付款日期', '送货方式', '物流公司', '物流单号', '发货日期', '完成日期');
				foreach ($title as $i => $one) {
					$head[$i] = iconv('utf-8', 'gbk', $one);
				}
				fputcsv($fp, $head);
				$sql = 'SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				$tpid = array();
				foreach ($listall as $value) {
					if ($value['agentid'] && !in_array($value['agentid'], $tpid)) {
						$tpid[] = $value['agentid'];
					}
					if ($value['fagentid'] && !in_array($value['fagentid'], $tpid)) {
						$tpid[] = $value['fagentid'];
					}
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				$fkfs = array('0' => '现金', '1' => '转账', '2' => '代收', '3' => '在线支付', '4' => '预存款支付', '88' => '期初');
				$sh8 = array('现买', '发货', '自提');
				foreach ($listall as $value) {
					$tmp_value = array($value['id'], $value['orderno'], date('Y-m-d', $value['ordertime']), empty($value['agentid']) ? '散客' : $agents[$value['agentid']]['name'], empty($value['fagentid']) ? '总部' : $agents[$value['fagentid']]['name'], $value['com'], $value['goodsprice'], $value['price'], $_W['zm_o_st'][$value['status']], $value['consignee'], $value['tel'], $value['province'], $value['city'], $value['district'], $value['address'], $fkfs[$value['paytype']], empty($value['paytime']) ? '' : date('Y-m-d', $value['paytime']), $sh8[$value['sendtype']], $value['expcom'], $value['expsn'], empty($value['exptime']) ? '' : date('Y-m-d', $value['exptime']), empty($value['oktime']) ? '' : date('Y-m-d', $value['oktime']));
					foreach ($tmp_value as $j => $v) {
						$tmp_value[$j] = iconv('utf-8', 'gbk', $v);
					}
					fputcsv($fp, $tmp_value);
				}
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				exit;
			}
			if ($_GPC['submit'] == '20') {
				$title = array('№', '订单号', '下单日期', '订货人', '卖货人', '订单量', '货款', '订单金额', '订单状态', '收货人', '收货电话', '省', '市', '县', '地址', '付款方式', '付款日期', '送货方式', '物流公司', '物流单号', '发货日期', '完成日期');
				$arraydata[] = iconv("UTF-8", "GBK//IGNORE", implode("\t", $title));
				$sql = 'SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				$tpid = array();
				foreach ($listall as $value) {
					if ($value['agentid'] && !in_array($value['agentid'], $tpid)) {
						$tpid[] = $value['agentid'];
					}
					if ($value['fagentid'] && !in_array($value['fagentid'], $tpid)) {
						$tpid[] = $value['fagentid'];
					}
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				$fkfs = array('0' => '现金', '1' => '转账', '2' => '代收', '3' => '在线支付', '4' => '预存款支付', '88' => '期初');
				$sh8 = array('现买', '发货', '自提');
				foreach ($listall as $value) {
					$tmp_value = array($value['id'], $value['orderno'], date('Y-m-d', $value['ordertime']), empty($value['agentid']) ? '散客' : $agents[$value['agentid']]['name'], empty($value['fagentid']) ? '总部' : $agents[$value['fagentid']]['name'], $value['com'], $value['goodsprice'], $value['price'], $_W['zm_o_st'][$value['status']], $value['consignee'], $value['tel'], $value['province'], $value['city'], $value['district'], $value['address'], $fkfs[$value['paytype']], empty($value['paytime']) ? '' : date('Y-m-d', $value['paytime']), $sh8[$value['sendtype']], $value['expcom'], $value['expsn'], empty($value['exptime']) ? '' : date('Y-m-d', $value['exptime']), empty($value['oktime']) ? '' : date('Y-m-d', $value['oktime']));
					$arraydata[] = iconv("UTF-8", "GBK//IGNORE", implode("\t", $tmp_value));
				}
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '订单导出时间') . date('Ymd_His') . '.xls');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo implode("\t\n", $arraydata);
				exit;
			}
			$agent['id'] = empty($agent['id']) ? -1 : $agent['id'];
			$sql = 'SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
			$list = pdo_fetchall($sql, $tj2, 'id');
			$total = pdo_fetch('SELECT COUNT(*) as a , sum(com) as com ,sum(price) as price ,sum(goodsprice) as goodsprice  FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
			$pager = pagination($total['a'], $pindex, $psize);
			foreach ($list as $sgo) {
				if (!empty($sgo['agentid'])) {
					$list[$sgo['id']]['dagent'] = pdo_fetch("SELECT name,phone FROM " . tablename($this->tb_user) . " WHERE id = :id", array(':id' => $sgo['agentid']));
				} else {
					$list[$sgo['id']]['dagent'] = array('name' => '客户');
				}
				if (!empty($sgo['fagentid'])) {
					$list[$sgo['id']]['fagent'] = pdo_fetch("SELECT name,phone FROM " . tablename($this->tb_user) . " WHERE id = :id", array(':id' => $sgo['fagentid']));
				} else {
					$list[$sgo['id']]['fagent'] = array('name' => '总部');
				}
			}
			include $this->template('order.gklist');
		}
	}
	public function doWebFahuo()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		$id = intval(substr($_GPC['oid'], 2, 10));
		if (empty($id)) {
			$id = intval($_GPC['id']);
		}
		$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid='0' AND zt='1'", array(':uniacid' => $_W['uniacid']));
		if (is_serialized($prin['prinset'])) {
			$prin['prinset'] = iunserializer($prin['prinset']);
		} else {
			$prin['prinset'] = array();
		}
		if (is_serialized($prin['princss'])) {
			$prin['princss'] = iunserializer($prin['princss']);
		} else {
			$prin['princss'] = array();
		}
		setting_load('zmcn:kuaidi');
		if (!empty($id)) {
			$order = pdo_fetch('SELECT * FROM ' . tablename($this->tb_order) . " WHERE uniacid=:uniacid AND id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			$order['ogoods'] = pdo_fetchall("SELECT goodsid,total,price,goodsname,optionname,optionid FROM " . tablename($this->tb_ordergoods) . " WHERE orderid='{$order['id']}'");
			$order['dhf'] = pdo_fetch("SELECT id,name,phone FROM " . tablename($this->tb_user) . " WHERE id=:id", array(':id' => $order['agentid']));
		}
		if ($_GPC['op'] == 'ud') {
			include $this->template('order.fh');
		} elseif ($_GPC['op'] == 'lb') {
			include $this->template('order.fhlist.mx');
		} elseif ($_GPC['op'] == 'fh') {
			if (empty($order['id'])) {
				message(array('zt' => 1, 'ts' => '查无此订单！'), '', 'ajax');
			}
			if ($order['status'] != '2' && $order['status'] != '3') {
				message(array('zt' => 1, 'ts' => '此订单不是待发货订单！'), '', 'ajax');
			}
			$rm = '订单：' . $order['orderno'];
			foreach ($order['ogoods'] as $ogo) {
				$this->getagstock(0, $ogo['goodsid'], $ogo['total'], 0, $ogo['optionid'], $rm);
			}
			$_W['setting']['zmcn:kuaidi']['my'] = $_GPC['mykd'];
			if (!empty($_GPC['etm'])) {
				$ntime = strtotime($_GPC['etm']);
			}
			if (empty($ntime) || $ntime < 100000000) {
				$ntime = TIMESTAMP;
			}
			pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['stp'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['exid']], 'exptime' => $ntime, 'lasttime' => TIMESTAMP), array('id' => $order['id']));
			if ($order['yorder'] > 0) {
				pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['stp'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['exid']], 'exptime' => $ntime, 'lasttime' => TIMESTAMP), array('yorder' => $order['yorder'], 'id' => $order['yorder']), 'OR');
			} else {
				pdo_update($this->tb_order, array('status' => '4', 'sendtype' => $_GPC['stp'], 'expsn' => $_GPC['esn'], 'expid' => $_GPC['eid'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['exid']], 'exptime' => $ntime, 'lasttime' => TIMESTAMP), array('id' => $order['id']));
			}
			$dagent = array();
			$dagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $order['agentid'], ':uniacid' => $_W['uniacid']));
			if (!empty($_GPC['sm']) && !empty($dagent['id']) && in_array('fw', $_W['setting']['userapps:m'])) {
				$fwms = explode("\n", $_GPC['sm'] . "\n");
				$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
				if (is_serialized($list['k'])) {
					$codeset = iunserializer($list['k']);
				} else {
					$codeset = array();
				}
				$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
				foreach ($fwms as $co) {
					$co = trim($co);
					if (!empty($co)) {
						$co = str_replace(strtoupper($codeset['m'][1]), "", strtoupper($co)) . "000000000000000000000000000000000000";
						$co = substr($co, 0, $len);
						$batchid = substr($co, 0, (int) $codeset['m'][2]);
						$batch = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_batch') . " WHERE uniacid=:uniacid AND batch=:batch", array(':uniacid' => $_W['uniacid'], ':batch' => $batchid));
						if (!empty($batch['sbox'])) {
							$lensb = strlen($batch['sbox']);
							$lsno = (int) substr($co, 0 - $lensb);
							if (empty($lsno)) {
								$colen = $len - $lensb;
								$co = substr($co, 0, $colen);
							}
							if (!empty($batch['bbox'])) {
								$lenbb = strlen($batch['bbox']);
								$lsno = (int) substr($co, 0 - $lenbb);
								if (empty($lsno)) {
									$colen = $colen - $lenbb;
									$co = substr($co, 0, $colen);
								}
							}
						}
						$co = strtoupper($codeset['m'][1]) . $co;
						$da = array('agna' => $dagent['name'], 'agtel' => $dagent['phone'], 'agwx' => $dagent['wechat']);
						$da = array('uniacid' => $_W['uniacid'], 'batchid' => $batch['id'], 'code' => $co, 'type' => '9', 'param' => iserializer($da), 'una' => '', 'uid' => $order['agentid'], 'Utp' => '3', 'ip' => $_W['clientip'], 'addtime' => TIMESTAMP);
						pdo_insert('zmcn_fw_basy', $da);
					}
				}
			}
			$tihuan = array();
			$tihuan[] = array('title' => '[订单状态]', 'value' => '订单发货');
			$tihuan[] = array('title' => '[订单号]', 'value' => $order['orderno']);
			$tihuan[] = array('title' => '[货品数量]', 'value' => $order['com']);
			$tihuan[] = array('title' => '[订单金额]', 'value' => $order['price']);
			$tihuan[] = array('title' => '[订货方]', 'value' => $dagent['name'] . ' ' . $dagent['phone']);
			$tihuan[] = array('title' => '[收货方]', 'value' => $order['consignee'] . ' ' . $order['tel']);
			$tihuan[] = array('title' => '[订单类型]', 'value' => '总部发货');
			$tihuan[] = array('title' => '[订单时间]', 'value' => date("Y-m-d H:i:s", $order['ordertime']));
			$url = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&id=' . $order['id'] . '&do=myinorder&m=zmcn_order&wxref=mp.weixin.qq.com#wechat_redirect';
			if (function_exists('zmcn_sendTplNotice')) {
				zmcn_sendTplNotice(22, $dagent['openid'], $tihuan, $url);
			}
			$ts = array('zt' => 0, 'ts' => '发货操作成功！', 'orderno' => $order['orderno'], 'consignee' => $order['consignee'], 'expcom' => $_W['setting']['zmcn:kuaidi'][$_GPC['exid']]);
			message($ts, '', 'ajax');
		} else {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 10;
			$tj1 = "";
			$tj2 = array(':uniacid' => $_W['uniacid'], ':status' => '3');
			if (!empty($settings['gs']['ywid'])) {
				$tj1 .= " AND fagentid IN (0," . (int) $settings['gs']['ywid'] . ") ";
			} else {
				$tj1 .= " AND fagentid=:fagentid ";
				$tj2[':fagentid'] = 0;
			}
			if (in_array('fw', $_W['setting']['userapps:m'])) {
				$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
				if (is_serialized($list['k'])) {
					$codeset = iunserializer($list['k']);
				} else {
					$codeset = array();
				}
				$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
			}
			if ($_GPC['submit'] == '2') {
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '订单导出时间') . date('Ymd_His') . '.csv');
				header('Cache-Control: max-age=0');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				$fp = fopen('php://output', 'a');
				$title = array('№', '订单号', '下单日期', '订货人', '卖货人', '订单量', '货款', '订单金额', '订单状态', '收货人', '收货电话', '省', '市', '县', '地址', '付款方式', '付款日期', '送货方式', '物流公司', '物流单号', '发货日期', '完成日期');
				foreach ($title as $i => $one) {
					$head[$i] = iconv('utf-8', 'gbk', $one);
				}
				fputcsv($fp, $head);
				$sql = 'SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND status=:status ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				$tpid = array();
				foreach ($listall as $value) {
					if ($value['agentid'] && !in_array($value['agentid'], $tpid)) {
						$tpid[] = $value['agentid'];
					}
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				$fkfs = array('0' => '现金', '1' => '转账', '2' => '代收', '3' => '在线支付', '4' => '预存款支付', '88' => '期初');
				$sh8 = array('现买', '发货', '自提');
				foreach ($listall as $value) {
					$tmp_value = array($value['id'], $value['orderno'], date('Y-m-d', $value['ordertime']), empty($value['agentid']) ? '散客' : $agents[$value['agentid']]['name'], '总部', $value['com'], $value['goodsprice'], $value['price'], $_W['zm_o_st'][$value['status']], $value['consignee'], $value['tel'], $value['province'], $value['city'], $value['district'], $value['address'], $fkfs[$value['paytype']], empty($value['paytime']) ? '' : date('Y-m-d', $value['paytime']), $sh8[$value['sendtype']], $value['expcom'], $value['expsn'], empty($value['exptime']) ? '' : date('Y-m-d', $value['exptime']), empty($value['oktime']) ? '' : date('Y-m-d', $value['oktime']));
					foreach ($tmp_value as $j => $v) {
						$tmp_value[$j] = iconv('utf-8', 'gbk', $v);
					}
					fputcsv($fp, $tmp_value);
				}
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				exit;
			}
			if ($_GPC['submit'] == '3') {
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '订单导出时间') . date('Ymd_His') . '.csv');
				header('Cache-Control: max-age=0');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				$fp = fopen('php://output', 'a');
				$title = array('№', '订单号', '下单日期', '订货人', '卖货人', '订单量', '货款', '订单金额', '订单状态', '收货人', '收货电话', '省', '市', '县', '地址', '付款方式', '付款日期', '送货方式', '物流公司', '物流单号', '发货日期', '完成日期', '商品名称', '商品型号', '订货量', '单价');
				foreach ($title as $i => $one) {
					$head[$i] = iconv('utf-8', 'gbk', $one);
				}
				fputcsv($fp, $head);
				$sql = 'SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND status=:status ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				$tpid = array();
				$odid = array();
				foreach ($listall as $value) {
					if ($value['agentid'] && !in_array($value['agentid'], $tpid)) {
						$tpid[] = $value['agentid'];
					}
					$odid[] = $value['id'];
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				if (count($odid) > 0) {
					$goodss = pdo_fetchall('SELECT `orderid`,`total`,`goodsname`,`optionname`,`price` FROM ' . tablename($this->tb_ordergoods) . ' WHERE uniacid=:uniacid AND orderid IN (-1,' . implode(',', $odid) . ')', array(':uniacid' => $_W['uniacid']));
				} else {
					exit;
				}
				$fkfs = array('0' => '现金', '1' => '转账', '2' => '代收', '3' => '在线支付', '4' => '预存款支付', '88' => '期初');
				$sh8 = array('现买', '发货', '自提');
				foreach ($goodss as $gos) {
					$value = $listall[$gos['orderid']];
					$tmp_value = array($value['id'], $value['orderno'], date('Y-m-d', $value['ordertime']), empty($value['agentid']) ? '散客' : $agents[$value['agentid']]['name'], '总部', $value['com'], $value['goodsprice'], $value['price'], $_W['zm_o_st'][$value['status']], $value['consignee'], $value['tel'], $value['province'], $value['city'], $value['district'], $value['address'], $fkfs[$value['paytype']], empty($value['paytime']) ? '' : date('Y-m-d', $value['paytime']), $sh8[$value['sendtype']], $value['expcom'], $value['expsn'], empty($value['exptime']) ? '' : date('Y-m-d', $value['exptime']), empty($value['oktime']) ? '' : date('Y-m-d', $value['oktime']), $gos['goodsname'], $gos['optionname'], $gos['total'], $gos['price']);
					foreach ($tmp_value as $j => $v) {
						$tmp_value[$j] = iconv('utf-8', 'gbk', $v);
					}
					fputcsv($fp, $tmp_value);
				}
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				exit;
			}
			$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND status=:status ' . $tj1 . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_order) . ' WHERE uniacid=:uniacid AND status=:status ' . $tj1, $tj2);
			$pager = pagination($total, $pindex, $psize);
			foreach ($list as $sgo) {
				$list[$sgo['id']]['dagent'] = pdo_fetch("SELECT name,phone FROM " . tablename($this->tb_user) . " WHERE id = :id", array(':id' => $sgo['agentid']));
			}
			$accounts = pdo_fetchall("SELECT id,title FROM " . tablename($this->tb_account) . " WHERE uniacid='{$_W['uniacid']}' AND zt='1'  AND `type`<7");
			include $this->template('order.fhlist');
		}
	}
	public function doWebTiaoma()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		if (!in_array('fw', $_W['setting']['userapps:m'])) {
			message(array('zt' => 1, 'ts' => '10数据有误！'), '', 'ajax');
		}
		$co = $_GPC['co'];
		$list = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_codeset') . " WHERE uniacid=" . $_W['uniacid']);
		if (is_serialized($list['k'])) {
			$codeset = iunserializer($list['k']);
		} else {
			$codeset = array();
		}
		if (empty($codeset['m'][2])) {
			message(array('zt' => 1, 'ts' => '系统有误！'), '', 'ajax');
		}
		$co = str_replace(strtoupper($codeset['m'][1]), "", strtoupper($co));
		$batchid = substr($co, 0, (int) $codeset['m'][2]);
		$batch = pdo_fetch("SELECT * FROM " . tablename('zmcn_fw_batch') . " WHERE uniacid=:uniacid AND batch=:batch", array(':uniacid' => $_W['uniacid'], ':batch' => $batchid));
		if (empty($batch['goid']) || (int) $batch['shopid'] != '88') {
			message(array('zt' => 1, 'ts' => '11不支持扫码录入！'), '', 'ajax');
		}
		$goods = pdo_fetch("SELECT id,name,sthumb,'0' as zt , '1' as total FROM " . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND id=:id ", array(':uniacid' => $_W['uniacid'], ':id' => $batch['goid']));
		if (empty($goods['id'])) {
			message(array('zt' => 1, 'ts' => '12不支持扫码录入！'), '', 'ajax');
		}
		$len = (int) $codeset['m'][2] + (int) $codeset['m'][3];
		$colen = strlen($co);
		if ($len == '12' && $colen == '13') {
			$co = substr($co, 0, 12);
			$colen = '12';
		}
		if ($colen > $len) {
		} else {
			if (!empty($batch['sbox'])) {
				if ($colen != $len) {
					$co = substr($co . "000000000000000000000000000000000000", 0, $len);
				}
				$lensb = strlen($batch['sbox']);
				$lsno = (int) substr($co, 0 - $lensb);
				if (empty($lsno)) {
					if (!empty($batch['bbox'])) {
						$lenbb = strlen($batch['bbox']) + $lensb;
						$lsno = (int) substr($co, 0 - $lenbb);
						if (empty($lsno)) {
							$goods['total'] = $batch['sbox'] * $batch['bbox'];
						} else {
							$goods['total'] = $batch['sbox'];
						}
					} else {
						$goods['total'] = $batch['sbox'];
					}
				}
			}
		}
		message($goods, '', 'ajax');
	}
	public function doWebStock()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		if ($_GPC['op'] == 'in') {
			$goodss = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND iscer = '1' ", array(':uniacid' => $_W['uniacid']), 'id');
			$accounts = pdo_fetchall("SELECT id,title FROM " . tablename($this->tb_account) . " WHERE uniacid='{$_W['uniacid']}' AND zt='1'  AND `type`<3");
			include $this->template('stock.inout');
		} elseif ($_GPC['op'] == 'op') {
			$good = pdo_fetch('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => (int) $_GPC['goid']));
			if (empty($good['id'])) {
				exit('查无此货！');
			}
			$opt = '';
			if ($good['isspec']) {
				$options = pdo_fetchall('SELECT id,title FROM ' . tablename($this->tb_goodsoption) . ' WHERE goodsid=:goodsid', array(':goodsid' => $good['id']));
				foreach ($options as $go) {
					$opt .= "<option value='" . $go['title'] . "' data-optionid='" . $go['id'] . "'>" . $go['title'] . "</option>";
				}
			}
			exit($opt);
		} elseif ($_GPC['op'] == 'io') {
			$good = pdo_fetch('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => (int) $_GPC['goid']));
			if (empty($good['id'])) {
				message(array('zt' => 1, 'ts' => '查无此货！'), '', 'ajax');
			}
			$_GPC['total'] = (int) $_GPC['total'];
			if ($_GPC['total'] < 1) {
				message(array('zt' => 1, 'ts' => '数量不对！'), '', 'ajax');
			}
			if (!empty($_GPC['iotm'])) {
				$iotm = strtotime($_GPC['iotm']);
			}
			if (empty($iotm) || $iotm < 100000000) {
				$iotm = TIMESTAMP;
			}
			if ($_GPC['iotp'] != '1' && $_GPC['iotp'] != '0') {
				message(array('zt' => 1, 'ts' => '出入库类型不对！'), '', 'ajax');
			}
			if (empty($_GPC['iorem'])) {
				$iorem = array('其它出库', '商品入库', '盘点校正', '退货入库');
				$_GPC['iorem'] = $iorem[$_GPC['iot']];
			}
			$this->getagstock(0, $good['id'], $_GPC['total'], $_GPC['iotp'], $_GPC['gopid'], $_GPC['iorem'], $iotm);
			$aa = array('出库', '入库');
			message(array('zt' => 0, 'ts' => $aa[$_GPC['iotp']] . '成功'), '', 'ajax');
		} elseif ($_GPC['op'] == 'jz') {
			$account = pdo_fetch('SELECT * FROM ' . tablename($this->tb_account) . " WHERE uniacid=:uniacid AND id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => (int) $_GPC['fkid']));
			if (empty($account['id'])) {
				message(array('zt' => 1, 'ts' => '无此账户！'), '', 'ajax');
			}
			if (CheckMoney($_GPC['sho'])) {
				$money = $_GPC['sho'];
			} else {
				message(array('zt' => 1, 'ts' => '金额不对！'), '', 'ajax');
			}
			if ($money < 0) {
				message(array('zt' => 1, 'ts' => '金额不对！'), '', 'ajax');
			}
			if (!empty($_GPC['iotm'])) {
				$iotm = strtotime($_GPC['iotm']);
			}
			if (empty($iotm) || $iotm < 100000000) {
				$iotm = TIMESTAMP;
			}
			if ($_GPC['iotp'] != '1' && $_GPC['iotp'] != '0') {
				message(array('zt' => 1, 'ts' => '收付款类型不对！'), '', 'ajax');
			}
			$qt = array('ustp' => 1, 'usid' => 0, 'odtp' => 1, 'odid' => 0);
			if ($_GPC['iot'] == '1') {
				$qt['Project'] = 2;
			} elseif ($_GPC['iot'] == '0') {
				$qt['Project'] = 3;
			} elseif ($_GPC['iot'] == '3') {
				$qt['Project'] = 4;
			}
			$this->getpaybd($account['id'], $money, $_GPC['iotp'], $qt, $_GPC['iorem']);
			$aa = array('支出', '入账');
			message(array('zt' => 0, 'ts' => $aa[$_GPC['iotp']] . '成功'), '', 'ajax');
		} elseif ($_GPC['op'] == 'ls') {
			$id = max(0, intval($_GPC['id']));
			$opid = max(0, intval($_GPC['opid']));
			$pindex = max(1, intval($_GPC['page']));
			$psize = 12;
			$good = pdo_fetch('SELECT * FROM ' . tablename($this->tb_goods) . " WHERE uniacid=:uniacid AND id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if (empty($good['id'])) {
				message('查无此货！', '', 'error');
			}
			if ($opid) {
				$option = pdo_fetch("SELECT id,title FROM " . tablename($this->tb_goodsoption) . " WHERE id = :id AND goodsid=:goodsid", array(':id' => $opid, ':goodsid' => $id));
			}
			$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_agstockh) . " WHERE agentid='0' AND goodsid=:goodsid AND optionid=:optionid LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':goodsid' => $id, ':optionid' => $opid), 'id');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_agstockh) . " WHERE uniacid='{$_W['uniacid']}' AND agentid='0' AND goodsid=:goodsid AND optionid=:optionid ", array(':goodsid' => $id, ':optionid' => $opid));
			$pager = pagination($total, $pindex, $psize);
			$permission = pdo_fetchall("SELECT id, uid, role FROM " . tablename('uni_account_users') . " WHERE uniacid = '" . $_W['uniacid'] . "' and role != :role  ORDER BY uid ASC, role DESC", array(':role' => 'clerk'), 'uid');
			if (!empty($permission)) {
				$member = pdo_fetchall("SELECT realname, uid FROM " . tablename('users_profile') . " WHERE uid IN (" . implode(',', array_keys($permission)) . "," . $_W['config']['setting']['founder'] . ")", array(), 'uid');
			}
			$uids = array();
			foreach ($permission as $v) {
				$uids[] = $v['uid'];
			}
			include $this->template('stock.lius');
		} else {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 12;
			$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_agstock) . " WHERE uniacid='{$_W['uniacid']}' AND agentid='0' ORDER BY goodsid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(), 'id');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_agstock) . " WHERE uniacid='{$_W['uniacid']}' AND agentid='0'");
			$pager = pagination($total, $pindex, $psize);
			foreach ($list as $sto) {
				$list[$sto['id']]['good'] = pdo_fetch("SELECT name,tmcode,spec,unit,sthumb FROM " . tablename($this->tb_goods) . " WHERE id = :id", array(':id' => $sto['goodsid']));
				$list[$sto['id']]['optionname'] = pdo_fetchcolumn("SELECT title FROM " . tablename($this->tb_goodsoption) . " WHERE id = :id", array(':id' => $sto['optionid']));
			}
			include $this->template('stock.list');
		}
	}
	public function doWebPay()
	{
		global $_W, $_GPC;
		if (checksubmit()) {
			$data = $_GPC['caid'];
			$cla = $_GPC['cla'];
			$ncla = $_GPC['ncla'];
			$rm = '期初建账，账户余额';
			for ($i = 0; $i < count($data); $i++) {
				if (!empty($data[$i])) {
					$a = $data[$i];
					pdo_update($this->tb_account, $cla[$a], array('id' => $a));
				}
			}
			for ($i = 0; $i < count($ncla['title']); $i++) {
				if (!empty($ncla['title'][$i]) && CheckMoney($ncla['money'][$i])) {
					pdo_insert($this->tb_account, array('uniacid' => $_W['uniacid'], 'title' => $ncla['title'][$i], 'remark' => $ncla['remark'][$i], 'addtime' => TIMESTAMP, 'type' => (int) $ncla['type'][$i], 'zt' => (int) $ncla['zt'][$i]));
					$acid = pdo_insertid();
					if ($ncla['money'][$i] > 0) {
						$_GPC['fk'] = '88';
						$qt = array('ustp' => '88', 'usid' => '0', 'odtp' => '88', 'odid' => '0');
						$this->getpaybd($acid, $ncla['money'][$i], 2, $qt, $rm);
					}
				}
			}
			message('账户信息保存成功', $this->createWebUrl('pay'), 'success');
		}
		if ($_GPC['op'] == 'delete') {
			$id = intval($_GPC['id']);
			$clas = pdo_fetch('SELECT id,money FROM ' . tablename($this->tb_account) . " WHERE uniacid=:uniacid AND id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if (empty($clas['id'])) {
				message('未找到指定分类');
			}
			if ($clas['money'] > 0) {
				message('该账户不能删除');
			}
			$result = pdo_delete($this->tb_account, array('id' => $id, 'uniacid' => $_W['uniacid']));
			if (intval($result) == 1) {
				message('删除账户.', $this->createWebUrl('pay'), 'success');
			} else {
				message('删除账户.');
			}
		}
		$clas = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_account) . " WHERE uniacid=:uniacid ORDER BY id DESC", array(':uniacid' => $_W['uniacid']), 'id');
		if ($_GPC['op'] == 'jl') {
			$id = intval($_GPC['id']);
			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;
			$tj1 = "";
			$tpid = array('0' => array(), '1' => array());
			$tj2 = array(':uniacid' => $_W['uniacid']);
			if (!empty($id)) {
				$tj1 .= " AND fkid=:fkid ";
				$tj2[':fkid'] = $id;
			}
			$fkfs = array('0' => '现金', '1' => '转账', '2' => '代收', '3' => '在线支付', '4' => '预存款支付', '88' => '期初');
			$zt = array('支出', '收入', '期初');
			$Project = array('销售收款', '期初', '进货付款', '其它收款', '销后退款', '销售提成');
			$tj = $_GPC['search'];
			if ($tj['usid'] != '' && $tj['usid'] != '-1') {
				$tj1 .= ' AND usid=:usid ';
				$tj2[':usid'] = $tj['usid'];
			}
			if ($tj['fkfs'] != '') {
				$tj1 .= ' AND fkfs=:fkfs ';
				$tj2[':fkfs'] = (int) $tj['fkfs'];
			}
			if ($tj['zt'] != '') {
				$tj1 .= ' AND zt=:zt ';
				$tj2[':zt'] = (int) $tj['zt'];
			}
			if ($tj['Project'] != '') {
				$tj1 .= ' AND Project=:Project ';
				$tj2[':Project'] = (int) $tj['Project'];
			}
			if (!empty($tj['shday'])) {
				$timetp = array('paytime', 'addtime');
				$stime = empty($tj['shday']['start']) ? strtotime('-1 month') : strtotime($tj['shday']['start']);
				$etime = empty($tj['shday']['end']) ? TIMESTAMP : strtotime($tj['shday']['end']) + 86399;
				$tj1 .= " AND " . $timetp[(int) $tj['timetp']] . ">=:stime AND " . $timetp[(int) $tj['timetp']] . "<=:etime ";
				$tj2[':stime'] = $stime;
				$tj2[':etime'] = $etime;
			}
			if ($_GPC['submit'] == '2') {
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '订单导出时间') . date('Ymd_His') . '.csv');
				header('Cache-Control: max-age=0');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				$fp = fopen('php://output', 'a');
				$title = array('№', '对象', '方式', '类型', '日期', '项目', '摘要', '收入', '支出', '余额');
				foreach ($title as $i => $one) {
					$head[$i] = iconv('utf-8', 'gbk', $one);
				}
				fputcsv($fp, $head);
				$sql = 'SELECT * FROM ' . tablename($this->tb_payh) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				foreach ($listall as $value) {
					if ($value['usid'] && !in_array($value['usid'], $tpid[$value['ustp']])) {
						$tpid[$value['ustp']][] = $value['usid'];
					}
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid['0']) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				foreach ($listall as $value) {
					$tmp_value = array($value['id'], empty($value['usid']) ? '未知' : $agents[$value['usid']]['name'], $fkfs[$value['fkfs']], $zt[$value['zt']], empty($value['paytime']) ? '' : date('Y-m-d', $value['paytime']), $Project[$value['Project']], $value['remark'], empty($value['zt']) ? 0 : $value['money'], empty($value['zt']) ? $value['money'] : 0, $value['balance']);
					foreach ($tmp_value as $j => $v) {
						$tmp_value[$j] = iconv('utf-8', 'gbk', $v);
					}
					fputcsv($fp, $tmp_value);
				}
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				exit;
			}
			$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_payh) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_payh) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
			$pager = pagination($total, $pindex, $psize);
			foreach ($list as $value) {
				if ($value['usid'] && !in_array($value['usid'], $tpid[$value['ustp']])) {
					$tpid[$value['ustp']][] = $value['usid'];
				}
			}
			if (count($tpid['0']) > 0) {
				$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid['0']) . ')', array(':uniacid' => $_W['uniacid']), 'id');
			} else {
				$agents = array();
			}
			include $this->template('account.h');
		} else {
			include $this->template('account');
		}
	}
	public function doWebYongjing()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		$id = (int) $_GPC['id'];
		if (!empty($id)) {
			$yjjl = pdo_fetch('SELECT * FROM ' . tablename($this->tb_commission) . " WHERE uniacid=:uniacid AND id=:id AND gid='0'", array(':uniacid' => $_W['uniacid'], ':id' => $id));
			if (empty($yjjl['id'])) {
				message('未找到指定记录');
			}
			$order = pdo_fetchall("SELECT * FROM " . tablename($this->tb_order) . " WHERE id='{$yjjl['orderid']}'");
			$dagent = pdo_fetch("SELECT * FROM " . tablename($this->tb_user) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => $yjjl['agentid'], ':uniacid' => $_W['uniacid']));
		}
		if ($_GPC['op'] == 'qx') {
			if (!empty($yjjl['isok'])) {
				message(array('zt' => 1, 'ts' => '该款项不是待结算款项！'), '', 'ajax');
			}
			pdo_update($this->tb_commission, array('isok' => '3', 'oktime' => TIMESTAMP), array('id' => $id));
			message('操作成功，订单已取消！', '', 'success');
		} elseif ($_GPC['op'] == 'js') {
			if (!empty($_GPC['tm'])) {
				$ntime = strtotime($_GPC['tm']);
			}
			if (empty($ntime) || $ntime < 100000000) {
				$ntime = TIMESTAMP;
			}
			if (!empty($yjjl['isok'])) {
				message(array('zt' => 1, 'ts' => '该款项不是待结算款项！'), '', 'ajax');
			}
			$rm = '佣金提现：' . $yjjl['remark'] . $_GPC['bz'];
			if (empty($_GPC['je']) || !CheckMoney($_GPC['je']) || $_GPC['je'] <= 0) {
				message(array('zt' => 1, 'ts' => '请输入结算金额！'), '', 'ajax');
			}
			$je = $_GPC['je'];
			if ($_GPC['js'] == '1') {
				if (empty($settings['red']['rootca']) || empty($settings['red']['apicert']) || empty($settings['red']['apikey'])) {
					message(array('zt' => 1, 'ts' => '你没有上传证书，不能用微信支付！'), '', 'ajax');
				}
				if (empty($dagent['openid'])) {
					message(array('zt' => 1, 'ts' => '该代理没有绑定微信做不了微信支付！'), '', 'ajax');
				}
				$fkid = (int) pdo_fetchcolumn("SELECT id FROM " . tablename($this->tb_account) . " WHERE uniacid='" . $_W['uniacid'] . "' AND zt='1' AND `type`='3'");
				if (empty($fkid)) {
					$fkid = (int) pdo_fetchcolumn('SELECT id FROM ' . tablename($this->tb_account) . " WHERE uniacid=:uniacid AND id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => (int) $_GPC['zh']));
				}
				if (empty($fkid)) {
					message(array('zt' => 1, 'ts' => '无此账户！'), '', 'ajax');
				}
				$qt = array('ustp' => 0, 'usid' => $yjjl['agentid'], 'odtp' => 2, 'odid' => $yjjl['id'], 'Project' => 5);
				$res = zmcn_sendWeixinPacket($dagent['openid'], $je * 100, $rm);
				if ($res == "OK") {
					$_GPC['fk'] = 3;
					$this->getpaybd($fkid, $je, 0, $qt, $rm);
					pdo_update($this->tb_commission, array('isok' => '1', 'jstime' => $ntime, 'jsje' => $je), array('id' => $id));
					message(array('zt' => 0, 'ts' => '提现操作成功！'), '', 'ajax');
				} else {
					message(array('zt' => 1, 'ts' => $res), '', 'ajax');
				}
			} elseif ($_GPC['js'] == '2') {
				$fkid = (int) pdo_fetchcolumn('SELECT id FROM ' . tablename($this->tb_account) . " WHERE uniacid=:uniacid AND id =:id ", array(':uniacid' => $_W['uniacid'], ':id' => (int) $_GPC['zh']));
				if (empty($fkid)) {
					message(array('zt' => 1, 'ts' => '无此账户！'), '', 'ajax');
				}
				$qt = array('ustp' => 0, 'usid' => $yjjl['agentid'], 'odtp' => 2, 'odid' => $yjjl['id'], 'Project' => 5);
				$this->getpaybd($fkid, $je, 0, $qt, $rm);
				pdo_update($this->tb_commission, array('isok' => '1', 'jstime' => $ntime, 'jsje' => $je), array('id' => $id));
				message(array('zt' => 0, 'ts' => '提现操作成功！'), '', 'ajax');
			} else {
				if (empty($dagent['openid'])) {
					message(array('zt' => 1, 'ts' => '1该代理没有绑定微信做不了余额结算！'), '', 'ajax');
				}
				load()->model('mc');
				$userid = mc_openid2uid($dagent['openid']);
				if (empty($userid)) {
					message(array('zt' => 1, 'ts' => '2该代理资料不全做不了余额结算！'), '', 'ajax');
				}
				$isok = mc_credit_update($userid, 'credit2', $je, array($_W['uid'], $rm, 'zmcn_order', 0, 0));
				if ($isok === true) {
					pdo_update($this->tb_commission, array('isok' => '1', 'jstime' => $ntime, 'jsje' => $je), array('id' => $id));
					message(array('zt' => 0, 'ts' => '提现操作成功！'), '', 'ajax');
				} else {
					message(array('zt' => 1, 'ts' => '提现操作不成功！'), '', 'ajax');
				}
			}
		} else {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 10;
			$io = (int) $_GPC['io'];
			$tj1 = "";
			$tj2 = array(':uniacid' => $_W['uniacid']);
			if ($io == '1') {
				$tj1 .= " AND gid=0 AND isok=0 ";
			} elseif ($io == '2') {
				$tj1 .= " AND gid=0 AND isok=1 ";
			} elseif ($io == '3') {
				$tj1 .= " AND gid=0 AND isok=2 ";
			} else {
				$tj1 .= " AND gid=0 ";
			}
			$tj = $_GPC['search'];
			if ($tj['agentid'] != '' && $tj['agentid'] != '-1') {
				$tj1 .= ' AND agentid=:agentid ';
				$tj2[':agentid'] = $tj['agentid'];
			}
			if (!empty($tj['shday'])) {
				$timetp = array('addtime', 'addtime', 'jstime', 'oktime');
				$stime = empty($tj['shday']['start']) ? strtotime('-1 month') : strtotime($tj['shday']['start']);
				$etime = empty($tj['shday']['end']) ? TIMESTAMP : strtotime($tj['shday']['end']) + 86399;
				$tj1 .= " AND " . $timetp[(int) $tj['timetp']] . ">=:stime AND " . $timetp[(int) $tj['timetp']] . "<=:etime ";
				$tj2[':stime'] = $stime;
				$tj2[':etime'] = $etime;
			}
			if ($_GPC['submit'] == '2') {
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/vnd.ms-execl");
				header("Content-Type: application/force-download");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=" . iconv("UTF-8", "GB2312//IGNORE", '提成导出时间') . date('Ymd_His') . '.csv');
				header('Cache-Control: max-age=0');
				header("Content-Transfer-Encoding: binary");
				header("Pragma: no-cache");
				header("Expires: 0");
				$fp = fopen('php://output', 'a');
				$title = array('№', '收款人', '付款人', '关系人', '提成金额', '产生日期', '摘要', '状态', '结算日期', '结算金额', '完成日期');
				foreach ($title as $i => $one) {
					$head[$i] = iconv('utf-8', 'gbk', $one);
				}
				fputcsv($fp, $head);
				$sql = 'SELECT * FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC';
				$listall = pdo_fetchall($sql, $tj2, 'id');
				$tpid = array();
				foreach ($listall as $value) {
					if ($value['agentid'] && !in_array($value['agentid'], $tpid)) {
						$tpid[] = $value['agentid'];
					}
					if ($value['gid'] && !in_array($value['gid'], $tpid)) {
						$tpid[] = $value['gid'];
					}
					if ($value['bagentid'] && !in_array($value['bagentid'], $tpid)) {
						$tpid[] = $value['bagentid'];
					}
				}
				if (count($tpid) > 0) {
					$agents = pdo_fetchall('SELECT `id`,`name`,`phone` FROM ' . tablename($this->tb_user) . ' WHERE uniacid=:uniacid AND id IN (-1,' . implode(',', $tpid) . ')', array(':uniacid' => $_W['uniacid']), 'id');
				} else {
					$agents = array();
				}
				$sh8 = array('待结算', '已付款', '已完成', '取消');
				foreach ($listall as $value) {
					$tmp_value = array($value['id'], empty($value['agentid']) ? '未知' : $agents[$value['agentid']]['name'], empty($value['gid']) ? '总部' : $agents[$value['gid']]['name'], empty($value['fagentid']) ? '未知' : $agents[$value['fagentid']]['name'], $value['commission'], empty($value['addtime']) ? '' : date('Y-m-d', $value['addtime']), $value['remark'], $sh8[$value['isok']], empty($value['jstime']) ? '' : date('Y-m-d', $value['jstime']), $value['jsje'], empty($value['oktime']) ? '' : date('Y-m-d', $value['oktime']));
					foreach ($tmp_value as $j => $v) {
						$tmp_value[$j] = iconv('utf-8', 'gbk', $v);
					}
					fputcsv($fp, $tmp_value);
				}
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				fputcsv($fp, array(''));
				exit;
			}
			$list = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid ' . $tj1 . ' ORDER BY id DESC LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $tj2, 'id');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_commission) . ' WHERE uniacid=:uniacid ' . $tj1, $tj2);
			$pager = pagination($total, $pindex, $psize);
			foreach ($list as $sto) {
				$list[$sto['id']]['dag'] = pdo_fetch("SELECT name,phone FROM " . tablename($this->tb_user) . " WHERE id = :id", array(':id' => $sto['agentid']));
			}
			$accounts = pdo_fetchall("SELECT id,title FROM " . tablename($this->tb_account) . " WHERE uniacid='{$_W['uniacid']}' AND zt='1' AND `type`<7 ");
			include $this->template('yongjing.lis');
		}
	}
	public function doWebDispatch()
	{
		global $_W, $_GPC, $settings;
		$settings = $this->module['config'];
		$id = (int) $_GPC['id'];
		if ($_GPC['op'] == 'ud') {
			$dispatch = array();
			$dispatch = pdo_fetch("SELECT * FROM " . tablename($this->tb_dispatch) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => (int) $_GPC['id'], ':uniacid' => $_W['uniacid']));
			if (is_serialized($dispatch['express'])) {
				$dispatch['express'] = iunserializer($dispatch['express']);
			} else {
				$dispatch['express']['pickup'] = array('free' => '1', 'expna' => '上门提货');
				$dispatch['express']['freightcollect'] = array('free' => '1', 'expna' => '运费到付');
			}
			if (checksubmit()) {
				$dat = $_GPC['dispatch'];
				$dat['zt'] = (int) $dat['zt'];
				$dat['express'] = iserializer($_GPC['express']);
				if (empty($dispatch['id'])) {
					if (empty($dat['province'])) {
						message('没有配送区域！', '', 'error');
					}
					$dat['uniacid'] = $_W['uniacid'];
					pdo_insert($this->tb_dispatch, $dat);
				} else {
					$abc = pdo_update($this->tb_dispatch, $dat, array('id' => $dispatch['id']));
				}
				message('配送区域保存成功！', '', 'success');
			}
			$areaArray = array('北京', '天津', '河北省', '山西省', '内蒙古自治区', '辽宁省', '吉林省', '黑龙江省', '上海', '江苏省', '浙江省', '安徽省', '福建省', '江西省', '山东省', '河南省', '湖北省', '湖南省', '广东省', '广西壮族自治区', '海南省', '重庆', '四川省', '贵州省', '云南省', '西藏自治区', '陕西省', '甘肃省', '青海省', '宁夏回族自治区', '新疆维吾尔自治区', '台湾省', '香港特别行政区', '澳门特别行政区', '海外');
			$adexpress = array('pickup' => '上门提货', 'freightcollect' => '运费到付', 'buynow' => '现买');
			$dispatchs = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_dispatch) . " WHERE uniacid=:uniacid AND agentid=0 ORDER BY id DESC", array(':uniacid' => $_W['uniacid']), 'id');
			$provinceok = array();
			$cityok = array();
			foreach ($dispatchs as $sto) {
				if (empty($sto['city'])) {
					$provinceok[] = $sto['province'];
				} else {
					$citys = explode(',', $sto['city']);
					foreach ($citys as $cit) {
						if ($cit) {
							$cityok[] = $cit;
						}
					}
				}
			}
			$areano = array_diff($areaArray, $provinceok);
			setting_load('zmcn:kuaidi');
			$adexpress = $adexpress + $_W['setting']['zmcn:kuaidi'];
			include $this->template('dispatch.edit');
		} elseif ($_GPC['op'] == 'addexp') {
			$expid = trim($_GPC['expid']);
			$adexpress = array('pickup' => '上门提货', 'freightcollect' => '运费到付', 'buynow' => '现买');
			setting_load('zmcn:kuaidi');
			$adexpress = $adexpress + $_W['setting']['zmcn:kuaidi'];
			$expna = $adexpress[$expid];
			if (empty($expna)) {
				exit;
			}
			include $this->template('dispatch.addexp');
		} elseif ($_GPC['op'] == 'del') {
			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('未找到指定配送区域', '', 'error');
			}
			$result = pdo_delete($this->tb_dispatch, array('id' => $id, 'uniacid' => $_W['uniacid']));
			if (intval($result) == 1) {
				message('删除配送区域成功！', $this->createWebUrl('dispatch'), 'success');
			} else {
				message('删除配送区域不成功！', '', 'error');
			}
		} else {
			$_GPC['op'] = 'display';
			$dispatchs = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_dispatch) . " WHERE uniacid=:uniacid AND agentid=0 ORDER BY id", array(':uniacid' => $_W['uniacid']), 'id');
			foreach ($dispatchs as $sto) {
				if (is_serialized($sto['express'])) {
					$dispatchs[$sto['id']]['express'] = iunserializer($sto['express']);
				}
			}
			include $this->template('dispatch.list');
		}
	}
	public function doWebPrint()
	{
		global $_W, $_GPC;
		$settings = $this->module['config'];
		if ($_GPC['op'] == 'ud') {
			$prin = array();
			if (!empty($_GPC['id'])) {
				$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND id=:id", array(':id' => (int) $_GPC['id'], ':uniacid' => $_W['uniacid']));
				if (is_serialized($prin['prinset'])) {
					$prin['prinset'] = iunserializer($prin['prinset']);
				} else {
					$prin['prinset'] = array();
				}
				if (is_serialized($prin['princss'])) {
					$prin['princss'] = iunserializer($prin['princss']);
				} else {
					$prin['princss'] = array();
				}
			} else {
				$prin = pdo_fetch("SELECT * FROM " . tablename($this->tb_printer) . " WHERE uniacid=:uniacid AND agentid=:agentid", array(':agentid' => '0', ':uniacid' => $_W['uniacid']));
				if (is_serialized($prin['prinset'])) {
					$prin['prinset'] = iunserializer($prin['prinset']);
				} else {
					$prin['prinset'] = array();
				}
				if (is_serialized($prin['princss'])) {
					$prin['princss'] = iunserializer($prin['princss']);
				} else {
					$prin['princss'] = array();
				}
			}
			if (checksubmit()) {
				$dat = $_GPC['prin'];
				if ($dat['prinserver'] == 'no') {
					unset($dat['prinserver']);
				} else {
					$_GPC['sb']['sn'] = trim($_GPC['sb']['sn']) ? trim($_GPC['sb']['sn']) : message('设备编码不能为空', '', 'error');
					$_GPC['sb']['key'] = trim($_GPC['sb']['key']) ? trim($_GPC['sb']['key']) : message('设备密钥不能为空', '', 'error');
					$dat['prinset'] = iserializer($_GPC['sb']);
				}
				$dat['zt'] = (int) $dat['zt'];
				$dat['princss'] = iserializer($_GPC['css']);
				if (empty($prin['id'])) {
					$dat['uniacid'] = $_W['uniacid'];
					$dat['addtime'] = TIMESTAMP;
					pdo_insert($this->tb_printer, $dat);
				} else {
					$abc = pdo_update($this->tb_printer, $dat, array('id' => $prin['id']));
				}
				message('打印机资料保存成功！', '', 'success');
			}
			include $this->template('printer.edit');
		} elseif ($_GPC['op'] == 'del') {
			$id = intval($_GPC['id']);
			if (empty($id)) {
				message('未找到指定设备', '', 'error');
			}
			$result = pdo_delete($this->tb_printer, array('id' => $id, 'uniacid' => $_W['uniacid']));
			if (intval($result) == 1) {
				message('删除设备成功！', $this->createWebUrl('classset'), 'success');
			} else {
				message('删除设备不成功！', '', 'error');
			}
		} else {
			$_GPC['op'] = 'display';
			$pageindex = max(intval($_GPC['page']), 1);
			$pagesize = 12;
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tb_printer) . ' WHERE uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
			$pager = pagination($total, $pageindex, $pagesize);
			$prins = pdo_fetchall('SELECT * FROM ' . tablename($this->tb_printer) . " WHERE uniacid=:uniacid ORDER BY id DESC LIMIT " . ($pageindex - 1) * $pagesize . ',' . $pagesize, array(':uniacid' => $_W['uniacid']), 'id');
			include $this->template('printer.list');
		}
	}
	public function doprint($order, $prin)
	{
		if ($prin['prinserver'] == 'feie') {
			$orderInfo = $this->doprinttxtfeie($order, $prin['princss'], $prin['prinset']['kd']);
			$a = $this->doprintfeie($prin['prinset'], $orderInfo, 1);
		} elseif ($prin['prinserver'] == '365') {
			$orderInfo = $this->doprinttxt365($order, $prin['princss'], $prin['prinset']['kd']);
			$a = $this->doprint365($prin['prinset'], $orderInfo, 1);
		}
		return $a;
	}
	public function doprinttxt365($order, $prset = array(), $tp = '58')
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		if ($tp == '80') {
			$c = 48;
		} else {
			$c = 32;
		}
		if (empty($prset['title'])) {
			$prset['title'] = "送货单";
		}
		$a = $c / 4 - get_string_width($prset['title']);
		$orderInfo = "^B2" . str_repeat(" ", $a) . $prset['title'] . str_repeat(" ", $a) . "\n";
		if (!empty($prset['stitle'])) {
			$orderInfo .= $prset['stitle'] . "\n";
		}
		if (empty($order['pinstatus'])) {
			$tis = "";
		} elseif ($order['pinstatus'] == '1') {
			$tis = "重打印";
		} else {
			$tis = "重打印" . $order['pinstatus'] . "次";
		}
		$orderInfo .= $this->formatstr($tis, $order['orderno'], $c) . "\n";
		if (!empty($prset['dhf'])) {
			$orderInfo .= "订货方：" . $order['dhf']['name'] . " " . $order['dhf']['phone'] . "\n";
		}
		if (!empty($prset['fhf'])) {
			$orderInfo .= "发货方：" . $order['fhf']['name'] . " " . $order['fhf']['phone'] . "\n";
		}
		$orderInfo .= str_repeat("-", $c / 2 - 10) . date('Y-m-d  H:i:s', TIMESTAMP) . str_repeat("-", $c / 2 - 10) . "\n";
		$orderInfo .= $this->formatstr("名称及型号", "数量", $c) . "\n";
		$orderInfo .= str_repeat("-", $c) . "\n";
		foreach ($order['ogoods'] as $good) {
			if ($tp == '80') {
				$orderInfo .= $this->formatstr($good['goodsname'] . " " . $good['optionname'], $good['total'], $c) . "\n";
			} else {
				if (!empty($good['optionname'])) {
					$orderInfo .= $this->formatstr($good['goodsname'] . $good['optionname'], $good['total'], $c) . "\n";
				} else {
					$orderInfo .= $this->formatstr(cut_string($good['goodsname'], 13), $good['total'], $c) . "\n";
				}
			}
		}
		$orderInfo .= $this->formatstr("合计品种：" . count($order['ogoods'], 0), "合计数量：" . $order['com'], $c) . "\n";
		$orderInfo .= str_repeat("-", $c) . "\n";
		if (!empty($order['remark'])) {
			$orderInfo .= "备注：" . $order['remark'] . "\n" . str_repeat("-", $c) . "\n";
		}
		if (!empty($prset['shr'])) {
			$orderInfo .= "收货人：" . $order['consignee'] . " " . $order['tel'] . "\n";
			$orderInfo .= "地址：" . $order['province'] . $order['city'] . $order['district'] . $order['address'] . "\n";
		}
		if ($order['paytime'] > '10000000') {
			$orderInfo .= "付款时间：" . date('Y-m-d H:i', $order['paytime']) . "\n";
		}
		if (!empty($order['expcom'])) {
			$orderInfo .= "指定物流：" . $order['expcom'] . "\n";
		}
		$orderInfo .= "-------- ☆ ☆ ☆ ☆ ☆ --------\n";
		if (!empty($prset['footer'])) {
			$orderInfo .= $prset['footer'] . "\n";
		}
		if ($prset['qr'] == '1') {
			$qr = $settings['gs']['gsqr'] . '?' . $order['orderno'];
			$orderInfo .= "^Q" . chr(strlen($qr)) . $qr . "\n";
		} elseif ($prset['qr'] == '2' && !empty($order['aguqr'])) {
			$orderInfo .= "^Q" . chr(strlen($order['aguqr'])) . $order['aguqr'] . "\n";
		}
		if (!empty($prset['lc'])) {
			$orderInfo .= "\n";
			if ($order['status'] > 1) {
				$orderInfo .= "付款√→";
			} else {
				$orderInfo .= "付款□→";
			}
			if ($order['status'] > 2) {
				$orderInfo .= "查款√→";
			} else {
				$orderInfo .= "查款□→";
			}
			if ($order['status'] > 3) {
				$orderInfo .= "发货√→";
			} else {
				$orderInfo .= "发货□→";
			}
			if ($order['status'] > 6) {
				$orderInfo .= "签收√";
			} else {
				$orderInfo .= "签收□";
			}
			$orderInfo .= "\n";
		}
		if (!empty($prset['tm'])) {
			$orderInfo .= '^P' . chr(12) . '58' . substr('000000000000000' . $order[id], -10);
		}
		return array('c1' => $orderInfo, 'c2' => (int) $prset['sl']);
	}
	public function doprint365($sb = array(), $cs = array(), $zl = '1')
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		if (empty($sb['ip'])) {
			$sb['ip'] = 'open.printcenter.cn';
		}
		if (empty($sb['dk']) || $sb['dk'] == '80') {
			$sb['dk'] = '8080';
		}
		$zm = array('queryPrinterStatus', 'addOrder', 'queryOrder');
		$posts = array('deviceno' => $sb['sn'], 'key' => $sb['key']);
		if ($zl == '1') {
			$posts['printContent'] = $cs['c1'];
			$posts['times'] = $cs['c2'];
		}
		if ($zl == '2') {
			$posts['orderindex'] = $cs['c1'];
		}
		load()->func('communication');
		$res = ihttp_post('http://' . $sb['ip'] . ':' . $sb['dk'] . '/' . $zm[$zl], $posts);
		$a = analyJson($res['content']);
		if ($a == FALSE || !empty($a['responseCode']) && $a['responseCode'] < 9) {
			$res = ihttp_post('http://' . $sb['ip'] . ':' . $sb['dk'] . '/' . $zm[$zl], $posts);
			$a = analyJson($res['content']);
		}
		return $a;
	}
	public function doprinttxtfeie($order, $prset = array(), $tp = '58')
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		$settings = $this->module['config'];
		if ($tp == '80') {
			$c = 48;
		} else {
			$c = 32;
		}
		if (!empty($prset['title'])) {
			$orderInfo = '<CB>' . $prset['title'] . '</CB><BR>';
		} else {
			$orderInfo = '<CB>送货单</CB><BR>';
		}
		if (!empty($prset['stitle'])) {
			$orderInfo .= '<C>' . $prset['stitle'] . '</C>';
		}
		if (empty($order['pinstatus'])) {
			$tis = "";
		} elseif ($order['pinstatus'] == '1') {
			$tis = "重打印";
		} else {
			$tis = "重打印" . $order['pinstatus'] . "次";
		}
		$orderInfo .= $this->formatstr($tis, $order['orderno'], $c) . '<BR>';
		if (!empty($prset['dhf'])) {
			$orderInfo .= '订货方：' . $order['dhf']['name'] . ' ' . $order['dhf']['phone'] . '<BR>';
		}
		if (!empty($prset['fhf'])) {
			$orderInfo .= '发货方：' . $order['fhf']['name'] . ' ' . $order['fhf']['phone'] . '<BR>';
		}
		$orderInfo .= str_repeat("-", $c / 2 - 10) . date('Y-m-d  H:i:s', TIMESTAMP) . str_repeat("-", $c / 2 - 10) . '<BR>';
		$orderInfo .= $this->formatstr('名称及型号', '数量', $c) . '<BR>';
		$orderInfo .= str_repeat("-", $c) . '<BR>';
		foreach ($order['ogoods'] as $good) {
			if ($tp == '80') {
				$orderInfo .= $this->formatstr($good['goodsname'] . ' ' . $good['optionname'], $good['total'], $c) . '<BR>';
			} else {
				if (!empty($good['optionname'])) {
					$orderInfo .= $this->formatstr($good['goodsname'] . $good['optionname'], $good['total'], $c) . '<BR>';
				} else {
					$orderInfo .= $this->formatstr(cut_string($good['goodsname'], 13), $good['total'], $c) . '<BR>';
				}
			}
		}
		$orderInfo .= $this->formatstr('合计品种：' . count($order['ogoods'], 0), '合计数量：' . $order['com'], $c) . '<BR>';
		$orderInfo .= str_repeat("-", $c) . '<BR>';
		if (!empty($order['remark'])) {
			$orderInfo .= '备注：' . $order['remark'] . '<BR>' . str_repeat("-", $c) . '<BR>';
		}
		if (!empty($prset['shr'])) {
			$orderInfo .= '收货人：' . $order['consignee'] . ' ' . $order['tel'] . '<BR>';
			$orderInfo .= '地址：' . $order['province'] . $order['city'] . $order['district'] . $order['address'] . '<BR>';
		}
		if ($order['paytime'] > '10000000') {
			$orderInfo .= '付款时间：' . date('Y-m-d H:i', $order['paytime']) . '<BR>';
		}
		if (!empty($order['expcom'])) {
			$orderInfo .= '指定物流：' . $order['expcom'] . '<BR>';
		}
		$orderInfo .= '<C>-------- ☆ ☆ ☆ ☆ ☆ --------<C><BR>';
		if (!empty($prset['footer'])) {
			$orderInfo .= '<C>' . $prset['footer'] . '</C>';
		}
		if ($prset['qr'] == '1') {
			$orderInfo .= '<QR>' . $settings['gs']['gsqr'] . '?' . $order['orderno'] . '</QR>';
		} elseif ($prset['qr'] == '2' && !empty($order['aguqr'])) {
			$orderInfo .= '<QR>' . $order['aguqr'] . '</QR>';
		}
		if (!empty($prset['lc'])) {
			$orderInfo .= '<BR><C>';
			if ($order['status'] > 1) {
				$orderInfo .= '付款√→';
			} else {
				$orderInfo .= '付款□→';
			}
			if ($order['status'] > 2) {
				$orderInfo .= '查款√→';
			} else {
				$orderInfo .= '查款□→';
			}
			if ($order['status'] > 3) {
				$orderInfo .= '发货√→';
			} else {
				$orderInfo .= '发货□→';
			}
			if ($order['status'] > 6) {
				$orderInfo .= '签收√';
			} else {
				$orderInfo .= '签收□';
			}
			$orderInfo .= '<C>';
		}
		if (!empty($prset['tm'])) {
			$orderInfo .= '<CODE>' . '58' . substr('000000000000000' . $order[id], -10);
		}
		return array('c1' => $orderInfo, 'c2' => (int) $prset['sl']);
	}
	public function doprintfeie($sb = array(), $cs = array(), $zl = '1')
	{
		global $_W, $_GPC, $fans, $agent, $openid;
		if ($sb['ip'] == '') {
			$sb['ip'] = 'dzp.feieyun.com';
			$pos = strpos($sb['sn'], '6');
			if ($pos == 2) {
				$sb['ip'] = 'api163.feieyun.com';
			}
			$pos = strpos($sb['sn'], '7');
			if ($pos == 2) {
				$sb['ip'] = 'api174.feieyun.com';
			}
		}
		$hm = '/FeieServer';
		$zm = array('queryPrinterStatusAction', 'printOrderAction', 'queryOrderStateAction', 'queryOrderInfoAction');
		$posts = array('sn' => $sb['sn'], 'key' => $sb['key']);
		if ($zl == '1') {
			$posts['printContent'] = $cs['c1'];
			$posts['times'] = $cs['c2'];
		}
		if ($zl == '2') {
			$posts['index'] = $cs['c1'];
		}
		if ($zl == '3') {
			$posts['date'] = $cs['c1'];
		}
		load()->func('communication');
		$res = ihttp_post('http://' . $sb['ip'] . $hm . '/' . $zm[$zl], $posts);
		$a = analyJson($res['content']);
		if ($a == FALSE || !empty($a['responseCode'])) {
			$res = ihttp_post('http://' . $sb['ip'] . $hm . '/' . $zm[$zl], $posts);
			$a = analyJson($res['content']);
		}
		return $a;
	}
	function formatstr($l = '', $r = '', $c = 32)
	{
		$nbsp = str_repeat(" ", $c);
		$llen = $this->print_strlen($l);
		$rlen = $this->print_strlen($r);
		if ($l && $r) {
			$lr = $llen + $rlen;
			$nl = $this->print_strlen($nbsp);
			if ($lr >= $nl) {
				$strtxt = $l . "\r\n" . $this->formatstr(null, $r);
			} else {
				$strtxt = $l . substr($nbsp, $lr) . $r;
			}
		} elseif ($r) {
			$strtxt = substr($nbsp, $rlen) . $r;
		} else {
			$strtxt = $l;
		}
		return $strtxt;
	}
	private function print_strlen($str, $charset = '')
	{
		global $_W;
		if (empty($charset)) {
			$charset = $_W['charset'];
		}
		if (strtolower($charset) == 'gbk') {
			$charset = 'gbk';
			$ci = 2;
		} else {
			$charset = 'utf-8';
			$ci = 3;
		}
		if (strtolower($charset) == 'utf-8') {
			$str = iconv('utf-8', 'GBK//IGNORE', $str);
		}
		$num = strlen($str);
		$cnNum = 0;
		for ($i = 0; $i < $num; $i++) {
			if (ord(substr($str, $i + 1, 1)) > 127) {
				$cnNum++;
				$i++;
			}
		}
		$enNum = $num - $cnNum * $ci;
		$number = $enNum + $cnNum * $ci;
		return ceil($number);
	}
	public function zm_fuserinfo($fid)
	{
		global $_W;
		if (empty($fid)) {
			return array();
		}
		return pdo_fetch("SELECT avatar,id,name FROM " . tablename($this->tb_user) . " WHERE uniacid = :uniacid AND id=:id", array(':uniacid' => $_W['uniacid'], ':id' => $fid));
	}
	public function doWebfsquery()
	{
		global $_W, $_GPC;
		$keyword = trim($_GPC['keyword']);
		$type = trim($_GPC['type']);
		if ($type == 'fid') {
			$ds = pdo_fetchall("SELECT avatar as a,id as b,name as c FROM " . tablename($this->tb_user) . " WHERE uniacid = :uniacid AND name LIKE :keyword", array(':uniacid' => $_W['uniacid'], ':keyword' => "%" . $keyword . "%"));
		} else {
			$ds = pdo_fetchall("SELECT b.avatar as a,a.openid as b,a.nickname as c FROM " . tablename('mc_mapping_fans') . " as a left join " . tablename('mc_members') . " as b ON a.uid=b.uid   WHERE a.uniacid = :uniacid AND a.nickname LIKE :keyword", array(':uniacid' => $_W['uniacid'], ':keyword' => "%" . $keyword . "%"));
		}
		include $this->template('fsquery');
	}
	public function EAN13($n)
	{
		$n = (string) $n;
		$a = (($n[1] + $n[3] + $n[5] + $n[7] + $n[9] + $n[11]) * 3 + $n[0] + $n[2] + $n[4] + $n[6] + $n[8] + $n[10]) % 10;
		$a = $a == 0 ? 0 : 10 - $a;
		return $n . $a;
	}
	public function getMenuTiles()
	{
		global $_W, $_GPC;
		$menus = array();
		if (!empty($_W['setting']['site']['key']) && !in_array('pay', $_W['setting']['userapps:m'])) {
			$menus[] = array('title' => "&#x8d22;&#x52a1;&#x4e2d;&#x5fc3;", 'url' => $this->createWebUrl('pay', array()), 'icon' => 'fa fa-money');
		}
		if (!empty($_W['setting']['site']['key']) && !in_array('stock', $_W['setting']['userapps:m'])) {
			$menus[] = array('title' => "&#x5e93;&#x5b58;&#x7ba1;&#x7406;", 'url' => $this->createWebUrl('stock', array()), 'icon' => 'fa fa-database');
		}
		return $menus;
	}
	public function zm_message($m = array('ts' => '', 'url' => '', 'title' => '', 'but' => '', 'xurl' => '', 'zt' => 1, 'pop' => 0), $url = '', $type = 'info', $title = '', $buttontext = '', $xurl = '')
	{
		global $_W;
		if (is_array($m)) {
			$message = $m['ts'];
			if ($m['title']) {
				$title = $m['title'];
			}
			if ($m['but']) {
				$buttontext = $m['but'];
			}
			if ($m['xurl']) {
				$xurl = $m['xurl'];
			}
			if ($m['url']) {
				$url = $m['url'];
			}
		} else {
			$message = $m;
		}
		if ($type == 'zt') {
			exit($m['zt']);
		}
		if ($type == 'ajax') {
			message($m, '', 'ajax');
		}
		include $this->template('zm_message');
		exit;
	}
}
function get_cdr($m)
{
	$a = $m;
	if (empty($a)) {
		$a = 0;
	}
	$a = str_replace(".00", "", $a);
	return $a;
}
function analyJson($json_str)
{
	$json_str = str_replace('＼＼', '', $json_str);
	$out_arr = array();
	preg_match('/{.*}/', $json_str, $out_arr);
	if (!empty($out_arr)) {
		$result = json_decode($out_arr[0], TRUE);
	} else {
		return FALSE;
	}
	return $result;
}
function get_string_width($s)
{
	$length = 0;
	while (strlen($s) > 0) {
		$old_length = strlen($s);
		$s = mb_substr($s, 0, -1, 'utf8');
		if ($old_length - strlen($s) == 1) {
			$length += 0.5;
		} else {
			$length += 1;
		}
	}
	return ceil($length);
}
function cut_string($s, $l = 13)
{
	$length = get_string_width($s);
	if ($length > $l) {
		while (get_string_width($s) >= $l + 1) {
			$s = mb_substr($s, 0, -1, 'utf8');
		}
	}
	return $s;
}
function CheckMoney($C_Money)
{
	if (!preg_match("/^(([1-9]\d*)|\d)(\.\d{1,2})?$/", $C_Money)) {
		return false;
	}
	return true;
}
function zmcn_sendWeixinPacket($openid, $money, $rema = '', $ists = false)
{
	global $settings, $_W;
	if (intval($money) <= 0) {
		return false;
	}
	$unipay = uni_setting($_W['uniacid'], array('payment'));
	$unipay = $unipay['payment']['wechat'];
	$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
	$pars = array();
	$pars['mch_appid'] = trim($_W['account']['key']);
	$pars['mchid'] = trim($unipay['mchid']);
	$pars['nonce_str'] = random(32);
	$pars['partner_trade_no'] = TIMESTAMP . rand(10000, 99999);
	$pars['openid'] = $openid;
	$pars['check_name'] = 'NO_CHECK';
	$pars['amount'] = $money;
	$pars['desc'] = $rema;
	$pars['spbill_create_ip'] = getServerIp();
	ksort($pars, SORT_STRING);
	$string1 = '';
	foreach ($pars as $k => $v) {
		$string1 .= "{$k}={$v}&";
	}
	$string1 .= "key={$unipay['apikey']}";
	$pars['sign'] = strtoupper(md5($string1));
	$xml = array2xml($pars);
	$extras = array();
	$extras['CURLOPT_CAINFO'] = ATTACHMENT_ROOT . '/certs/' . $settings['red']['rootca'];
	$extras['CURLOPT_SSLCERT'] = ATTACHMENT_ROOT . '/certs/' . $settings['red']['apicert'];
	$extras['CURLOPT_SSLKEY'] = ATTACHMENT_ROOT . '/certs/' . $settings['red']['apikey'];
	load()->func('communication');
	$ts = null;
	$resp = ihttp_request($url, $xml, $extras);
	if (is_error($resp)) {
		$ts = $resp;
	} else {
		$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
		$dom = new DOMDocument();
		if ($dom->loadXML($xml)) {
			$xpath = new DOMXPath($dom);
			$code = $xpath->evaluate('string(//xml/return_code)');
			$ret = $xpath->evaluate('string(//xml/result_code)');
			if (strtolower($code) == 'success' && strtolower($ret) == 'success') {
				$ts = 'OK';
				return 'OK';
			} else {
				$error = $xpath->evaluate('string(//xml/err_code_des)');
				$code = $xpath->evaluate('string(//xml/err_code)');
				if ($ists) {
					$ts = $code . '：' . $error;
				} else {
					$ts = $error;
				}
			}
		} else {
			$ts = '支付失败！未知错误！';
		}
	}
	return $ts;
}
function getServerIp()
{
	$ip = gethostbyname($_SERVER["SERVER_NAME"]);
	if (!preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
		$ip = '127.0.0.1';
	}
	return $ip;
}
function encode_json($str)
{
	$code = json_encode($str);
	return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $code);
}