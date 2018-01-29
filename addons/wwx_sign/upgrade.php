<?php
if (!pdo_fieldexists('wwx_sign', 'new_title')) {
    pdo_query("ALTER TABLE " . tablename('wwx_sign') . "ADD  `new_title` varchar(200);");
}

if (!pdo_fieldexists('wwx_sign', 'copyright')) {
    pdo_query("ALTER TABLE " . tablename('wwx_sign') . "ADD  `copyright` varchar(200) ;");
}

if (!pdo_fieldexists('wwx_sign', 'new_icon')) {
    pdo_query("ALTER TABLE " . tablename('wwx_sign') . "ADD  `new_icon` varchar(200);");
}

if (!pdo_fieldexists('wwx_sign', 'new_content')) {
    pdo_query("ALTER TABLE " . tablename('wwx_sign') . "ADD  `new_content` varchar(200);");
}

if (!pdo_fieldexists('wwx_sign_award', 'serial_day')) {
    pdo_query("ALTER TABLE " . tablename('wwx_sign_award') . "ADD  serial_day int(10) ;");
}

/**
* links
*/
$sql = "
CREATE TABLE IF NOT EXISTS " . tablename('wwx_sign_link') . " (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`sid` INT(11) UNSIGNED DEFAULT NULL,
`sort` int(2) default 0,
`link_name` varchar(50) NOT NULL ,
`link_url` varchar(50) NOT NULL,
`createtime` int(10) unsigned NOT NULL COMMENT '日期',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
pdo_query($sql);