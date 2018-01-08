<?php
$sql="CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `m1` varchar(2) NOT NULL,
  `batch` varchar(20) NOT NULL DEFAULT '0' COMMENT '批号',
  `num` int(11) NOT NULL DEFAULT '0',
  `rcon` int(10) NOT NULL DEFAULT '0',
  `factory` varchar(40) NOT NULL COMMENT '生产企业',
  `product` varchar(50) NOT NULL COMMENT '品名',
  `brand` varchar(40) NOT NULL COMMENT '品牌',
  `yuan` varchar(30) NOT NULL COMMENT '零售价',
  `jianjie` varchar(255) NOT NULL COMMENT '简介',
  `ischuanhuo` int(11) NOT NULL DEFAULT '0',
  `province` varchar(30) NOT NULL,
  `city` varchar(40) NOT NULL,
  `remark` varchar(100) NOT NULL,
  `inttype` int(2) NOT NULL DEFAULT '1',
  `integral` int(10) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  `lasttime` int(10) NOT NULL DEFAULT '0',
  `validity` int(10) NOT NULL DEFAULT '0',
  `buylink` varchar(255) NOT NULL,
  `wailink` varchar(6000) NOT NULL,
  `logo` varchar(150) NOT NULL,
  `banner` varchar(150) NOT NULL,
  `video` varchar(150) NOT NULL,
  `videoid` varchar(100) NOT NULL,
  `param` text NOT NULL,
  `toshop` varchar(500) NOT NULL,
  `sint` int(7) NOT NULL DEFAULT '0',
  `leint` varchar(300) NOT NULL,
  `caid` varchar(6000) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`),
  KEY `batch` (`batch`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_chai` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `batchid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(40) NOT NULL,
  `type` int(10) NOT NULL DEFAULT '0',
  `num` int(10) NOT NULL DEFAULT '0',
  `isvalid` int(2) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  `userna` varchar(50) NOT NULL,
  `userid` int(10) NOT NULL DEFAULT '0',
  `ip` varchar(30) NOT NULL,
  `gender` int(2) NOT NULL DEFAULT '0',
  `province` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `os` varchar(15) NOT NULL,
  `container` varchar(15) NOT NULL,
  `iserr` int(2) NOT NULL DEFAULT '0' COMMENT '1跨区',
  `isgz` int(10) NOT NULL,
  `hdtype` int(3) NOT NULL,
  `credittype` varchar(20) NOT NULL,
  `fnum` decimal(10,2) NOT NULL,
  `isy` int(2) NOT NULL,
  `tx` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `province` (`province`),
  KEY `city` (`city`),
  KEY `gender` (`gender`),
  KEY `type` (`type`),
  KEY `uniacid` (`uniacid`),
  KEY `batchid` (`batchid`),
  KEY `hdtype` (`hdtype`),
  KEY `credittype` (`credittype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_clerks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(200) NOT NULL DEFAULT '',
  `storeid` int(10) unsigned NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `openid` varchar(50) NOT NULL,
  `nickname` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `password` (`password`),
  KEY `uniacid` (`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_codeset` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `rid` int(10) NOT NULL DEFAULT '0',
  `m1` varchar(2) NOT NULL,
  `k` varchar(300) NOT NULL,
  `act` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_exchange` (
  `tid` int(11) NOT NULL DEFAULT '0',
  `uniacid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `act` varchar(50) NOT NULL,
  `intd` varchar(50) NOT NULL,
  `zhtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `tid` (`tid`),
  KEY `uniacid` (`uniacid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL,
  `type` int(11) NOT NULL,
  `summary` varchar(50) NOT NULL,
  `uid` int(10) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  `remark` varchar(50) NOT NULL,
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `summary` (`summary`),
  KEY `uniacid` (`uniacid`),
  KEY `summary_2` (`summary`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_fw_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `luck` varchar(4000) NOT NULL,
  `settings` text NOT NULL,
  `red` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_gy_chis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `userna` varchar(50) NOT NULL,
  `userid` int(10) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `zmid` varchar(30) NOT NULL,
  `type` int(3) NOT NULL DEFAULT '0' COMMENT '积分类型',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分金额分为单位',
  `sint` int(3) NOT NULL DEFAULT '0' COMMENT '活动计数',
  `sintp` int(10) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `remark` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`),
  KEY `userna` (`userna`),
  KEY `userid` (`userid`),
  KEY `rid` (`rid`),
  KEY `zmid` (`zmid`),
  KEY `type` (`type`),
  KEY `addtime` (`addtime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_gy_ints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `userna` varchar(50) NOT NULL,
  `userid` int(10) NOT NULL DEFAULT '0',
  `rid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `zmid` varchar(30) NOT NULL,
  `usid` int(11) NOT NULL DEFAULT '0',
  `type` int(3) NOT NULL DEFAULT '0' COMMENT '积分类型',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分金额分为单位',
  `sint` int(3) NOT NULL DEFAULT '0' COMMENT '活动计数',
  `keys` varchar(50) NOT NULL,
  `addtime` int(10) NOT NULL DEFAULT '0',
  `lasttime` int(10) NOT NULL DEFAULT '0',
  `endtime` int(10) NOT NULL DEFAULT '0',
  `remark` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`),
  KEY `userna` (`userna`),
  KEY `userid` (`userid`),
  KEY `rid` (`rid`),
  KEY `zmid` (`zmid`),
  KEY `type` (`type`),
  KEY `keys` (`keys`),
  KEY `endtime` (`endtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `ims_zmcn_hd_hdlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) NOT NULL DEFAULT '0',
  `zmid` varchar(30) NOT NULL,
  `rid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `usid` int(11) NOT NULL DEFAULT '0',
  `hdname` varchar(50) NOT NULL,
  `sint` int(11) NOT NULL DEFAULT '0' COMMENT '活动次数',
  `zint` int(11) NOT NULL DEFAULT '0',
  `sred` int(11) NOT NULL DEFAULT '0',
  `zred` int(11) NOT NULL DEFAULT '0',
  `addtime` int(10) NOT NULL DEFAULT '0',
  `lasttime` int(10) NOT NULL DEFAULT '0',
  `startime` int(10) NOT NULL DEFAULT '0',
  `endtime` int(10) NOT NULL DEFAULT '0',
  `hd` text NOT NULL,
  `set` text NOT NULL,
  `remark` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`),
  KEY `rid` (`rid`),
  KEY `zmid` (`zmid`),
  KEY `startime` (`startime`),
  KEY `endtime` (`endtime`),
  KEY `startime_2` (`startime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
pdo_run($sql);
if(!pdo_fieldexists('zmcn_fw_batch',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'm1')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `m1` varchar(2) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'batch')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `batch` varchar(20) NOT NULL DEFAULT '0' COMMENT '批号';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'num')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `num` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'rcon')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `rcon` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'factory')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `factory` varchar(40) NOT NULL COMMENT '生产企业';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'product')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `product` varchar(50) NOT NULL COMMENT '品名';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'brand')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `brand` varchar(40) NOT NULL COMMENT '品牌';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'yuan')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `yuan` varchar(30) NOT NULL COMMENT '零售价';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'jianjie')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `jianjie` varchar(255) NOT NULL COMMENT '简介';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'ischuanhuo')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `ischuanhuo` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'province')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `province` varchar(30) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'city')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `city` varchar(40) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'remark')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `remark` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'inttype')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `inttype` int(2) NOT NULL DEFAULT '1';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'integral')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `integral` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `addtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'lasttime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `lasttime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'validity')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `validity` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'buylink')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `buylink` varchar(255) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'wailink')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `wailink` varchar(6000) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'logo')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `logo` varchar(150) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'banner')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `banner` varchar(150) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'video')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `video` varchar(150) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'videoid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `videoid` varchar(100) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'param')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `param` text NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'toshop')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `toshop` varchar(500) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'sint')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `sint` int(7) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'leint')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `leint` varchar(300) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_batch',  'caid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD `caid` varchar(6000) NOT NULL;");
}
if(!pdo_indexexists('zmcn_fw_batch',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_fw_batch',  'batch')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_batch')." ADD KEY `batch` (`batch`);");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'batchid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `batchid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'code')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `code` varchar(40) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `type` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'num')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `num` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'isvalid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `isvalid` int(2) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `addtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'userna')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `userna` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'userid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `userid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'ip')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `ip` varchar(30) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'gender')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `gender` int(2) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'province')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `province` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'city')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `city` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'os')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `os` varchar(15) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'container')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `container` varchar(15) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'iserr')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `iserr` int(2) NOT NULL DEFAULT '0' COMMENT '1跨区';");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'isgz')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `isgz` int(10) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'hdtype')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `hdtype` int(3) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'credittype')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `credittype` varchar(20) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'fnum')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `fnum` decimal(10,2) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'isy')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `isy` int(2) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_chai',  'tx')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD `tx` varchar(100) NOT NULL;");
}
if(!pdo_indexexists('zmcn_fw_chai',  'userid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `userid` (`userid`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'province')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `province` (`province`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'city')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `city` (`city`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'gender')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `gender` (`gender`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `type` (`type`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'batchid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `batchid` (`batchid`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'hdtype')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `hdtype` (`hdtype`);");
}
if(!pdo_indexexists('zmcn_fw_chai',  'credittype')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_chai')." ADD KEY `credittype` (`credittype`);");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `uniacid` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'name')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `name` varchar(50) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'password')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `password` varchar(200) NOT NULL DEFAULT '';");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'storeid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `storeid` int(10) unsigned NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'mobile')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `mobile` varchar(20) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'openid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `openid` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_clerks',  'nickname')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD `nickname` varchar(30) NOT NULL;");
}
if(!pdo_indexexists('zmcn_fw_clerks',  'password')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD KEY `password` (`password`);");
}
if(!pdo_indexexists('zmcn_fw_clerks',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_clerks')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_fieldexists('zmcn_fw_codeset',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_codeset')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_fw_codeset',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_codeset')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_codeset',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_codeset')." ADD `rid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_codeset',  'm1')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_codeset')." ADD `m1` varchar(2) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_codeset',  'k')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_codeset')." ADD `k` varchar(300) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_codeset',  'act')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_codeset')." ADD `act` int(2) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_exchange',  'tid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD `tid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_exchange',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD `uniacid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_exchange',  'uid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD `uid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_exchange',  'act')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD `act` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_exchange',  'intd')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD `intd` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_exchange',  'zhtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD `zhtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_indexexists('zmcn_fw_exchange',  'tid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD KEY `tid` (`tid`);");
}
if(!pdo_indexexists('zmcn_fw_exchange',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_fw_exchange',  'uid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_exchange')." ADD KEY `uid` (`uid`);");
}
if(!pdo_fieldexists('zmcn_fw_history',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_fw_history',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `uniacid` int(10) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_history',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `type` int(11) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_history',  'summary')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `summary` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_history',  'uid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `uid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_history',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `addtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_history',  'remark')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `remark` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_history',  'ip')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD `ip` varchar(20) NOT NULL;");
}
if(!pdo_indexexists('zmcn_fw_history',  'summary')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD KEY `summary` (`summary`);");
}
if(!pdo_indexexists('zmcn_fw_history',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_fw_history',  'summary_2')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_history')." ADD KEY `summary_2` (`summary`);");
}
if(!pdo_fieldexists('zmcn_fw_set',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_set')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_fw_set',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_set')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_fw_set',  'luck')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_set')." ADD `luck` varchar(4000) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_set',  'settings')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_set')." ADD `settings` text NOT NULL;");
}
if(!pdo_fieldexists('zmcn_fw_set',  'red')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_fw_set')." ADD `red` text NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'userna')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `userna` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'userid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `userid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `rid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'tid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `tid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'zmid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `zmid` varchar(30) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `type` int(3) NOT NULL DEFAULT '0' COMMENT '积分类型';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'num')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分金额分为单位';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'sint')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `sint` int(3) NOT NULL DEFAULT '0' COMMENT '活动计数';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'sintp')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `sintp` int(10) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `addtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_chis',  'remark')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD `remark` text NOT NULL;");
}
if(!pdo_indexexists('zmcn_gy_chis',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_gy_chis',  'userna')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `userna` (`userna`);");
}
if(!pdo_indexexists('zmcn_gy_chis',  'userid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `userid` (`userid`);");
}
if(!pdo_indexexists('zmcn_gy_chis',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `rid` (`rid`);");
}
if(!pdo_indexexists('zmcn_gy_chis',  'zmid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `zmid` (`zmid`);");
}
if(!pdo_indexexists('zmcn_gy_chis',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `type` (`type`);");
}
if(!pdo_indexexists('zmcn_gy_chis',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_chis')." ADD KEY `addtime` (`addtime`);");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'userna')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `userna` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'userid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `userid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `rid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'tid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `tid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'zmid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `zmid` varchar(30) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'usid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `usid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `type` int(3) NOT NULL DEFAULT '0' COMMENT '积分类型';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'num')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `num` int(11) NOT NULL DEFAULT '0' COMMENT '积分金额分为单位';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'sint')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `sint` int(3) NOT NULL DEFAULT '0' COMMENT '活动计数';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'keys')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `keys` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `addtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'lasttime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `lasttime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'endtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `endtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_gy_ints',  'remark')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD `remark` text NOT NULL;");
}
if(!pdo_indexexists('zmcn_gy_ints',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'userna')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `userna` (`userna`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'userid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `userid` (`userid`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `rid` (`rid`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'zmid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `zmid` (`zmid`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'type')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `type` (`type`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'keys')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `keys` (`keys`);");
}
if(!pdo_indexexists('zmcn_gy_ints',  'endtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_gy_ints')." ADD KEY `endtime` (`endtime`);");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'id')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `uniacid` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'zmid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `zmid` varchar(30) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `rid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'tid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `tid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'usid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `usid` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'hdname')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `hdname` varchar(50) NOT NULL;");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'sint')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `sint` int(11) NOT NULL DEFAULT '0' COMMENT '活动次数';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'zint')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `zint` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'sred')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `sred` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'zred')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `zred` int(11) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'addtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `addtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'lasttime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `lasttime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'startime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `startime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'endtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `endtime` int(10) NOT NULL DEFAULT '0';");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'hd')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `hd` text NOT NULL;");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'set')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `set` text NOT NULL;");
}
if(!pdo_fieldexists('zmcn_hd_hdlist',  'remark')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD `remark` varchar(50) NOT NULL;");
}
if(!pdo_indexexists('zmcn_hd_hdlist',  'uniacid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD KEY `uniacid` (`uniacid`);");
}
if(!pdo_indexexists('zmcn_hd_hdlist',  'rid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD KEY `rid` (`rid`);");
}
if(!pdo_indexexists('zmcn_hd_hdlist',  'zmid')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD KEY `zmid` (`zmid`);");
}
if(!pdo_indexexists('zmcn_hd_hdlist',  'startime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD KEY `startime` (`startime`);");
}
if(!pdo_indexexists('zmcn_hd_hdlist',  'endtime')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD KEY `endtime` (`endtime`);");
}
if(!pdo_indexexists('zmcn_hd_hdlist',  'startime_2')) {
	pdo_query("ALTER TABLE ".tablename('zmcn_hd_hdlist')." ADD KEY `startime_2` (`startime`);");
}

?>