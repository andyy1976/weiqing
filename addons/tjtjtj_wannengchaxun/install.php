<?php
$sql = "

CREATE TABLE IF NOT EXISTS `tjtjtj_dl_webset` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) unsigned NOT NULL,
  `key_name` varchar(32) NOT NULL,
  `key_value` varchar(240) NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tjtjtj_dl_data_list` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tjtjtj_dl_data_param` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(11) unsigned NOT NULL,
  `sort` int(11) unsigned DEFAULT 0,
  `param_name` varchar(32) NOT NULL,
  `param_type` varchar(32) NOT NULL,
  `param_default` varchar(240) NOT NULL,
  `param_intro` varchar(20) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

";

pdo_query($sql);
