<?php
$installSql = <<<sql
CREATE TABLE IF NOT EXISTS `{$_W['config']['db']['tablepre']}qiyue_qiuqian` (
  `id` int(10) unsigned NOT NULL,
  `uniacid` int(10) unsigned NOT NULL,
  `sharenum` int(10) NOT NULL DEFAULT '0',
  `viewnum` int(10) NOT NULL DEFAULT '0',
  `morepic` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`id`,`uniacid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
sql;
$row = pdo_run($installSql);