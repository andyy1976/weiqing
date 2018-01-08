<?php

//decode by QQ:270656184 http://www.yunlu99.com/
defined('IN_IA') or exit('Access Denied');
class Zmcn_erpModuleSite extends WeModuleSite
{
	public function doWebWelcome()
	{
		global $_W, $_GPC;
		$panel = array();
		$titles = array();
		$panel['zmcn_goods'] = array('title' => '商品管理', 'logo' => 'fa fa-gift', 'seturl' => url('profile/module/setting', array('m' => 'zmcn_goods')), 'menu' => array(array('title' => '商品分类', 'logo' => 'fa fa-list-ol', 'url' => wurl('site/entry/category', array('m' => 'zmcn_goods', 'op' => 'display')), 'rem' => '商品分类管理'), array('title' => '新增商品', 'logo' => 'fa fa-dropbox', 'url' => wurl('site/entry/goods', array('m' => 'zmcn_goods', 'op' => 'edit')), 'rem' => '新建一个商品'), array('title' => '商品管理', 'logo' => 'fa fa-gift', 'url' => wurl('site/entry/goods', array('m' => 'zmcn_goods', 'op' => 'display')), 'rem' => '商品资料管理，价格管理，上下架等')));
		$panel['zmcn_cer'] = array('title' => '经销商管理', 'logo' => 'fa fa-users', 'seturl' => url('profile/module/setting', array('m' => 'zmcn_cer')), 'menu' => array(array('title' => '经销商分类', 'logo' => 'fa fa-list-ul', 'url' => wurl('site/entry/classset', array('m' => 'zmcn_cer')), 'rem' => '对经销商进行分类，不同分类销售的商品可以不同'), array('title' => '经销商等级', 'logo' => 'fa fa-list-ol', 'url' => wurl('site/entry/classset', array('m' => 'zmcn_cer', 'ty' => '2')), 'rem' => '对分类内的经销商设置等级，不同等级可以不同价格'), array('title' => '证书部暑', 'logo' => 'fa fa-image', 'url' => wurl('site/entry/setcer', array('m' => 'zmcn_cer')), 'rem' => '授权证书设置（此项为前置设置）'), array('title' => '新增经销商', 'logo' => 'fa fa-user-plus', 'url' => wurl('site/entry/agent', array('m' => 'zmcn_cer', 'op' => 'edit')), 'rem' => '为了让经销商和微信有绑定关系，尽量让其通过手机自行注册'), array('title' => '经销商管理', 'logo' => 'fa fa-users', 'url' => wurl('site/entry/agent', array('m' => 'zmcn_cer', 'op' => 'display')), 'rem' => '经销商档案管理，资质审核及授权管理'), array('title' => '经销商结构树', 'logo' => 'fa fa-sitemap', 'url' => wurl('site/entry/tree', array('m' => 'zmcn_cer')), 'rem' => '所有经销商的层级关系，一目了然')));
		$panel['zmcn_order'] = array('title' => '订单管理', 'logo' => 'fa fa-shopping-cart', 'seturl' => url('profile/module/setting', array('m' => 'zmcn_order')), 'menu' => array(array('title' => '销售开单', 'logo' => 'fa fa-building-o', 'url' => wurl('site/entry/order', array('m' => 'zmcn_order', 'op' => 'ud')), 'rem' => '总部开单，支持店面扫码开单'), array('title' => '总部订单管理', 'logo' => 'fa fa-shopping-cart', 'url' => wurl('site/entry/order', array('m' => 'zmcn_order')), 'rem' => '对各订单的管理'), array('title' => '扫码发货', 'logo' => 'fa fa-barcode', 'url' => wurl('site/entry/fahuo', array('m' => 'zmcn_order')), 'rem' => '普通发货或和支持发货时扫码验货'), array('title' => '批量发货', 'logo' => 'fa fa-paper-plane-o', 'url' => wurl('site/entry/fahuo', array('m' => 'zmcn_order', 'op' => 'ud')), 'rem' => '发货工作台，实现快速扫码发货'), array('title' => '商品入库', 'logo' => 'fa fa-sign-in', 'url' => wurl('site/entry/stock', array('m' => 'zmcn_order', 'op' => 'in')), 'rem' => '商品入库，支持扫码录单'), array('title' => '库存查询', 'logo' => 'fa fa-database', 'url' => wurl('site/entry/stock', array('m' => 'zmcn_order')), 'rem' => '商品出入库管理'), array('title' => '财务管理', 'logo' => 'fa fa-money', 'url' => wurl('site/entry/pay', array('m' => 'zmcn_order')), 'rem' => '收支账户管理及收支流水查看'), array('title' => '佣金管理', 'logo' => 'fa fa-calculator', 'url' => wurl('site/entry/yongjing', array('m' => 'zmcn_order')), 'rem' => '代理商提成管理及发放'), array('title' => '代理订单管控', 'logo' => 'fa fa-cloud', 'url' => wurl('site/entry/guangkong', array('m' => 'zmcn_order')), 'rem' => '查看代理之间的来往订单'), array('title' => '打印机管理', 'logo' => 'fa fa-print', 'url' => wurl('site/entry/print', array('m' => 'zmcn_order')), 'rem' => '总部订单打印机的接入和代理商订单打印机的管理'), array('title' => '运费模板', 'logo' => 'fa fa-truck', 'url' => wurl('site/entry/dispatch', array('m' => 'zmcn_order')), 'rem' => '可以设置配区域，以及该区域的配送方式和费用'), array('title' => '企业文章管理', 'logo' => 'fa fa-file-powerpoint-o', 'url' => wurl('site/article'), 'rem' => '企业相关的文章管理')));
		$panel['zmcn_sms'] = array('title' => '通知中心', 'logo' => 'fa fa-comments', 'seturl' => url('profile/module/setting', array('m' => 'zmcn_sms')), 'menu' => array(array('title' => '微信通知模板', 'logo' => 'fa fa-comments', 'url' => wurl('site/entry/wxsms', array('m' => 'zmcn_sms')), 'rem' => '微信模板消息的设置'), array('title' => '手机通知模板', 'logo' => 'fa fa-tablet', 'url' => wurl('site/entry/sjsms', array('m' => 'zmcn_sms')), 'rem' => '手机短信模板设置'), array('title' => '公告通知', 'logo' => 'fa fa-bullhorn', 'url' => wurl('site/entry/notice', array('m' => 'zmcn_sms')), 'rem' => '给代理商发通知')));
		$panel['zmcn_fw'] = array('title' => '防伪溯源', 'logo' => 'fa fa-qrcode', 'seturl' => url('profile/module/setting', array('m' => 'zmcn_fw')), 'menu' => array(array('title' => '新增批次', 'logo' => 'fa fa-cubes', 'url' => wurl('site/entry/zmfwpee', array('m' => 'zmcn_fw', 'foo' => 'post')), 'rem' => '建立防伪码或活动码'), array('title' => '防伪码管理', 'logo' => 'fa fa-qrcode', 'url' => wurl('site/entry/zmfwpee', array('m' => 'zmcn_fw')), 'rem' => '防伪码批次管理及所以设置'), array('title' => '活动码管理', 'logo' => 'fa fa-qrcode', 'url' => wurl('site/entry/zmfwpee', array('m' => 'zmcn_fw', 'foo' => 'post', 'at' => '1')), 'rem' => '活动码批次管理及所以设置'), array('title' => '查询管理', 'logo' => 'fa fa-search', 'url' => wurl('site/entry/zmfwcaa', array('m' => 'zmcn_fw')), 'rem' => '扫码查询记录'), array('title' => '消费者管理', 'logo' => 'fa fa-users', 'url' => wurl('site/entry/zmfwuser', array('m' => 'zmcn_fw')), 'rem' => '对扫码人员的资料查看及其消费记录查看'), array('title' => '数据魔方', 'logo' => 'fa fa-bar-chart-o', 'url' => wurl('site/entry/zmfwsjj', array('m' => 'zmcn_fw')), 'rem' => '通过防伪查询，对获取的用户资料做深层多维度的统计分析')));
		$panel['zmcn_haibao'] = array('title' => '海报中心', 'logo' => 'fa fa-comments', 'seturl' => url('profile/module/setting', array('m' => 'zmcn_haibao')), 'menu' => array(array('title' => '海报模板管理', 'logo' => 'fa fa-photo', 'url' => wurl('site/entry/haibao', array('m' => 'zmcn_haibao')), 'rem' => '海报模板管理'), array('title' => '海报用户管理', 'logo' => 'fa fa-user', 'url' => wurl('site/entry/user', array('m' => 'zmcn_haibao')), 'rem' => '海报用户管理')));
		load()->model('account');
		load()->model('module');
		$modules = uni_modules();
		foreach ($modules as $key => $m) {
			$titles[$key] = $m['title'];
		}
		$modules = array_keys($modules);
		foreach ($panel as $key => $m) {
			if ($_W['role'] == "founder" || in_array($key, $modules)) {
				if ($titles[$key]) {
					$panel[$key]['title'] = $titles[$key];
				}
				$panel[$key]['entries'] = module_entries($key, array('home', 'profile', 'shortcut', 'cover'));
				$panel[$key]['shortcut_url'] = url('site/nav/shortcut', array('m' => $key));
				$panel[$key]['profile_url'] = url('site/nav/profile', array('m' => $key));
				$panel[$key]['home_url'] = url('site/nav/home', array('m' => $key));
			} else {
				unset($panel[$key]);
			}
		}
		$panel['about_set'] = array('title' => '常用功能', 'logo' => 'fa fa-wrench', 'seturl' => url('home/welcome/platform'), 'menu' => array(array('title' => '会员积分管理', 'logo' => 'fa fa-tasks', 'url' => wurl('mc/creditmanage', array('type' => 3)), 'rem' => '代理商积分和预存款的管理充值'), array('title' => '粉丝管理', 'logo' => 'fa fa-child', 'url' => wurl('mc/fans', array('acid' => $_W['acid'], 'nickname' => '')), 'rem' => '公众号粉丝资料管理'), array('title' => '消息群发', 'logo' => 'fa fa-send', 'url' => wurl('material/mass'), 'rem' => '群发通知'), array('title' => '粉丝自动同步', 'logo' => 'fa fa-jsfiddle', 'url' => wurl('mc/passport/sync'), 'rem' => '用在完善系统粉丝数据'), array('title' => '积分兑换管理', 'logo' => 'fa fa-exchange', 'url' => wurl('activity/exchange/display', array('type' => 'goods')), 'rem' => '积分商城礼品管理')));
		include $this->template('welcome');
	}
}