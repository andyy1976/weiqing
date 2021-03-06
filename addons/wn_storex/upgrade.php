<?php 
if (!pdo_fieldexists('storex_room', 'is_house')) {
	pdo_query("ALTER TABLE " . tablename('storex_room') . " ADD `is_house` INT(11) NOT NULL DEFAULT '1' COMMENT '是否是房型 1 是，2不是 ';");
}
if (!pdo_fieldexists('storex_comment', 'goodsid')) {
	pdo_query("ALTER TABLE " . tablename('storex_comment') . " ADD `goodsid` INT(11) NOT NULL COMMENT '评论商品的id';");
}
if (!pdo_fieldexists('storex_comment', 'comment_level')) {
	pdo_query("ALTER TABLE " . tablename('storex_comment') . " ADD `comment_level` TINYINT(11) NOT NULL COMMENT '评论商品的级别';");
}
if (!pdo_fieldexists('storex_order', 'track_number')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `track_number` varchar(64) NOT NULL COMMENT '物流单号';");
}
if (!pdo_fieldexists('storex_order', 'express_name')) {
	pdo_query("ALTER TABLE " . tablename('storex_order') . " ADD `express_name` varchar(50) NOT NULL COMMENT '物流类型';");
}
if (!pdo_fieldexists('storex_bases', 'distance')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " ADD `distance` int(11) NOT NULL COMMENT '配送距离';");
}
if (pdo_fieldexists('storex_bases', 'lng')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " CHANGE `lng` `lng` DECIMAL(10,6) NULL DEFAULT '0.00';");
}
if (pdo_fieldexists('storex_bases', 'lat')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " CHANGE `lat` `lat` DECIMAL(10,6) NULL DEFAULT '0.00';");
}
$sql = "CREATE TABLE IF NOT EXISTS `ims_storex_clerk` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`weid` int(11) DEFAULT '0',
	`userid` varchar(50) DEFAULT '',
	`from_user` varchar(50) DEFAULT '',
	`realname` varchar(255) DEFAULT '',
	`mobile` varchar(255) DEFAULT '',
	`score` int(11) DEFAULT '0' COMMENT '积分',
	`createtime` int(11) DEFAULT '0',
	`userbind` int(11) DEFAULT '0',
	`status` int(11) DEFAULT '0',
	`username` varchar(30) DEFAULT '' COMMENT '用户名',
	`password` varchar(200) DEFAULT '' COMMENT '密码',
	`salt` varchar(8) NOT NULL DEFAULT '' COMMENT '加密盐',
	`nickname` varchar(255) NOT NULL DEFAULT '',
	`permission` text NOT NULL COMMENT '店员权限',
	PRIMARY KEY (`id`),
	KEY `indx_weid` (`weid`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);
if (!pdo_fieldexists('storex_bases', 'category_set')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " ADD `category_set` TINYINT NOT NULL DEFAULT '1' COMMENT '分类开启设置1开启，2关闭';");
}
if (!pdo_fieldexists('storex_bases', 'skin_style')) {
	pdo_query("ALTER TABLE " . tablename('storex_bases') . " ADD `skin_style` VARCHAR(48) NOT NULL DEFAULT 'display' COMMENT '皮肤选择';");
}
if (!pdo_fieldexists('storex_categorys', 'category_type')) {
	pdo_query("ALTER TABLE " . tablename('storex_categorys') . " ADD `category_type` TINYINT(2) NOT NULL DEFAULT '1' COMMENT '分类类型 1 酒店，2,普通';");
}

$category = pdo_getall('storex_categorys');
$stores = pdo_getall('storex_bases', array(), array('id', 'store_type', 'skin_style'), 'id');
if (!empty($stores)) {
	foreach ($stores as $val) {
		if ($val['skin_style'] == 'style1') {
			pdo_update('storex_bases', array('skin_style' => 'display'), array('id' => $val['id']));
		}
	}
}
if (!empty($category) && !empty($stores)) {
	foreach ($category as &$info){
		if ($info['category_type'] != 2) {
			if (!empty($stores[$info['store_base_id']])) {
				if ($stores[$info['store_base_id']]['store_type'] == 1) {
					$data = array('category_type' => 1);
					$info['category_type'] = 1;
				} else {
					$data = array('category_type' => 2);
					$info['category_type'] = 2;
				}
				pdo_update('storex_categorys', $data, array('id' => $info['id']));
			}
			if (!empty($info['parentid'])) {
				pdo_update('storex_room', array('is_house' => $info['category_type']), array('id' => $val['id'], 'pcate' => $info['id']));
			}
		}
	}
}
?>