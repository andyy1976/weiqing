<?php
/**
 * 微酒店
 *
 * @author WeEngine Team & ewei
 * @url
 */
defined('IN_IA') or exit('Access Denied');


/**
 * 计算用户密码hash
 * @param int $flag 0注册，1登录
 * @param array $member 用户数据
 * @return string
 */
if(!function_exists('hotel_set_userinfo')) {
	function hotel_set_userinfo($flag = 0, $member) {
		global $_GPC, $_W;
		unset($member['password']);
		unset($member['salt']);
		insert_cookie('__hotel_member', $member);
	}
}



if(!function_exists('hotel_get_userinfo')) {
	function hotel_get_userinfo() {
		global $_W;
		$key = '__hotel_member';
		return get_cookie($key);
	}
}



if(!function_exists('get_cookie')) {
	function get_cookie($key) {
		global $_W;
		$key = $_W['config']['cookie']['pre'] . $key;
		return json_decode(base64_decode($_COOKIE[$key]), true);
	}
}



if(!function_exists('insert_cookie')) {
	function insert_cookie($key, $data) {
		global $_W, $_GPC;
		$session = base64_encode(json_encode($data));
		setcookie($_W['config']['cookie']['pre'].$key, $session);
	}
}

//检查用户是否登录
if(!function_exists('check_hotel_user_login')) {
	function check_hotel_user_login($set) {
		global $_W;
		$weid = $_W['uniacid'];
		$from_user = $_W['fans']['from_user'];
		$user_info = hotel_get_userinfo();
		if (empty($user_info['id'])) {
			return 0;
		} else {
			if ($weid != $user_info['weid']) {
				return 0;
			}
			if ($from_user == $user_info['from_user']) {
				if ($set['user'] == 2 && $user_info['user_set'] != 2) {
					return 0;
				} else {
					return 1;
				}
			} else {
				if ($set['bind'] == 1) {
					return 1;
				} elseif ($set['bind'] == 2) {
					return 0;
				} elseif ($set['bind'] == 3) {
					if ($user_info['userbind'] == 0) {
						return 1;
					} else {
						return 0;
					}
				}
			}
		}
	}
}

/**
 * 计算用户密码hash
 * @param string $input 输入字符串
 * @param string $salt 附加字符串
 * @return string
 */
if(!function_exists('hotel_member_hash')) {
	function hotel_member_hash($input, $salt) {
		global $_W;
		$input = "{$input}-{$salt}-{$_W['config']['setting']['authkey']}";
		return sha1($input);
	}
}

/**
 * 用户注册
 * PS:密码字段不要加密
 * @param array $member 用户注册信息，需要的字段必须包括 username, password, remark
 * @return int 成功返回新增的用户编号，失败返回 0
 */
if(!function_exists('hotel_member_check')) {
	function hotel_member_check($member) {
		$sql = 'SELECT `password`,`salt` FROM ' . tablename('storex_member') . " WHERE 1";
		$params = array();
		if (!empty($member['uid'])) {
			$sql .= ' AND `uid`=:uid';
			$params[':uid'] = intval($member['uid']);
		}
		if (!empty($member['weid'])) {
			$sql .= ' AND `weid`=:weid';
			$params[':weid'] = intval($member['weid']);
		}
		if (!empty($member['username'])) {
			$sql .= ' AND `username`=:username';
			$params[':username'] = $member['username'];
		}
		if (!empty($member['from_user'])) {
			$sql .= ' AND `from_user`=:from_user';
			$params[':from_user'] = $member['from_user'];
		}
		if (!empty($member['status'])) {
			$sql .= " AND `status`=:status";
			$params[':status'] = intval($member['status']);
		}
		if (!empty($member['id'])) {
			$sql .= " AND `id`!=:id";
			$params[':id'] = intval($member['id']);
		}
		$sql .= " LIMIT 1";
		$record = pdo_fetch($sql, $params);
		if (!$record || empty($record['password']) || empty($record['salt'])) {
			return false;
		}
		if (!empty($member['password'])) {
			$password = hotel_member_hash($member['password'], $record['salt']);
			return $password == $record['password'];
		}
		return true;
	}
}

/**
 * 获取单条用户信息，如果查询参数多于一个字段，则查询满足所有字段的用户
 * PS:密码字段不要加密
 * @param array $member 要查询的用户字段，可以包括  uid, username, password, status
 * @param bool 是否要同时获取状态信息
 * @return array 完整的用户信息
 */
if(!function_exists('hotel_member_single')) {
	function hotel_member_single($member) {
		$sql = 'SELECT * FROM ' . tablename('storex_member') . " WHERE 1";
		$params = array();
		if (!empty($member['weid'])) {
			$sql .= ' AND `weid`=:weid';
			$params[':weid'] = $member['weid'];
		}
		if (!empty($member['from_user'])) {
			$sql .= ' AND `from_user`=:from_user';
			$params[':from_user'] = $member['from_user'];
		}
		if (!empty($member['username'])) {
			$sql .= ' AND `username`=:username';
			$params[':username'] = $member['username'];
		}
		if (!empty($member['status'])) {
			$sql .= " AND `status`=:status";
			$params[':status'] = intval($member['status']);
		}
		$sql .= " LIMIT 1";
		$record = pdo_fetch($sql, $params);
		if (!$record) {
			return false;
		}
		if (!empty($member['password'])) {
			$password = hotel_member_hash($member['password'], $record['salt']);
			if ($password != $record['password']) {
				return false;
			}
		}
		return $record;
	}
}



if(!function_exists('get_hotel_set')) {
	function get_hotel_set() {
		global $_GPC, $_W;
		$set = pdo_get('storex_set', array('weid' => intval($_W['uniacid'])));
		if (!$set) {
			$set = array(
				"user" => 1,
				"bind" => 1,
				"reg" => 1,
				"ordertype" => 1,
				"regcontent" => "",
				"paytype1" => 0,
				"paytype2" => 0,
				"paytype3" => 0,
				"is_unify" => 0,
				"version" => 0,
				"tel" => "",
			);
		}
		return $set;
	}
}

/**
 * 生成分页数据
 * @param int $currentPage 当前页码
 * @param int $totalCount 总记录数
 * @param string $url 要生成的 url 格式，页码占位符请使用 *，如果未写占位符，系统将自动生成
 * @param int $pageSize 分页大小
 * @return string 分页HTML
 */
if(!function_exists('get_page_array')) {
	function get_page_array($tcount, $pindex, $psize = 15) {
		global $_W;
		$pdata = array(
			'tcount' => 0,
			'tpage' => 0,
			'cindex' => 0,
			'findex' => 0,
			'pindex' => 0,
			'nindex' => 0,
			'lindex' => 0,
			'options' => ''
		);
		$pdata['tcount'] = $tcount;
		$pdata['tpage'] = ceil($tcount / $psize);
		if ($pdata['tpage'] <= 1) {
			$pdata['isshow'] = 0;
			return $pdata;
		}
		$cindex = $pindex;
		$cindex = min($cindex, $pdata['tpage']);
		$cindex = max($cindex, 1);
		$pdata['cindex'] = $cindex;
		$pdata['findex'] = 1;
		$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
		$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
		$pdata['lindex'] = $pdata['tpage'];
		if ($pdata['cindex'] == $pdata['lindex']) {
			$pdata['isshow'] = 0;
			$pdata['islast'] = 1;
		} else {
			$pdata['isshow'] = 1;
			$pdata['islast'] = 0;
		}
		return $pdata;
	}
}
//支付成功后，根据酒店设置的消费返积分的比例给积分
if(!function_exists('give_credit')) {
	function give_credit($weid, $openid, $sum_price ,$hotelid){
		load()->model('mc');
		$hotel_info = pdo_get('storex_bases', array('weid' => $weid ,'id' => $hotelid), array('integral_rate', 'weid'));
		$num = $sum_price * $hotel_info['integral_rate']*0.01;//实际消费的金额*比例(值时百分数)*0.01
		$tips .= "用户消费{$sum_price}元，支付{$sum_price}，积分赠送比率为:【1：{$hotel_info['integral_rate']}%】,共赠送【{$num}】积分";
		mc_credit_update($openid, 'credit1', $num, array('0', $tips, 'wn_storex', 0, 0, 3));
		return error(0, $num);
	}
}
//完成订单后加售出数量
if(!function_exists('add_sold_num')) {
	function add_sold_num($room){
		if (intval($_GPC['store_type']) == 1){
			pdo_update('storex_room', array('sold_num' => ($room['sold_num']+1)), array('id' => $room['id']));
		} else {
			pdo_update('storex_goods', array('sold_num' => ($room['sold_num']+1)), array('id' => $room['id']));
		}
	}
}
//获取房型某天的记录
if(!function_exists('getRoomPrice')) {
	function getRoomPrice($hotelid, $roomid, $date) {
		global $_W;
		$btime = strtotime($date);
		$roomprice = pdo_get('storex_room_price', array('weid' => intval($_W['uniacid']), 'hotelid' => $hotelid, 'roomid' => $roomid, 'roomdate' => $btime));
		if (empty($roomprice)) {
			$room = pdo_get('storex_room', array('hotelid' => $hotelid, 'id' => $roomid, 'weid' => intval($_W['uniacid'])));
			$roomprice = array(
					"weid" => $_W['uniacid'],
					"hotelid" => $hotelid,
					"roomid" => $roomid,
					"oprice" => $room['oprice'],
					"cprice" => $room['cprice'],
					"mprice" => $room['mprice'],
					"status" => $room['status'],
					"roomdate" => strtotime($date),
					"thisdate" => $date,
					"num" => "-1",
					"status" => 1,
			);
		}
		return $roomprice;
	}
}

if(!function_exists('gettablebytype')) {
	function gettablebytype($store_type){
		if ($store_type == 1) {
			return 'storex_room';
		} else {
			return 'storex_goods';
		}
	}
}

//获取订单的商户订单号
if(!function_exists('getOrderUniontid')) {
	function getOrderUniontid(&$lists){
		if (!empty($lists)){
			foreach ($lists as $orderkey=>$orderinfo){
				$paylog = pdo_get('core_paylog', array('uniacid' => $orderinfo['weid'], 'tid' => $orderinfo['id'], 'module' => 'wn_storex'), array('uniacid', 'uniontid', 'tid'));
				if (!empty($paylog)){
					$lists[$orderkey]['uniontid'] = $paylog['uniontid'];
				}
			}
		}
		return $list;
	}
}

if(!function_exists('format_list')) {
	function format_list($category, $list){
		if (!empty($category) && !empty($list)){
			$cate = array();
			foreach ($category as $category_info){
				$cate[$category_info['id']] = $category_info;
			}
			foreach ($list as $k => $info){
				if (!empty($cate[$info['pcate']])){
					$list[$k]['pcate'] = $cate[$info['pcate']]['name'];
				}
				if (!empty($cate[$info['ccate']])){
					$list[$k]['ccate'] = $cate[$info['ccate']]['name'];
				}
			}
		}
		return $list;
	}
}