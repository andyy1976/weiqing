<?php

if(!pdo_fieldexists('mon_egg', 'exchangeEnable')) {
	pdo_query("ALTER TABLE ".tablename('mon_egg')." ADD   `exchangeEnable` int(1) DEFAULT NULL ;");
}

if(!pdo_fieldexists('mon_egg', 'xhjf_enable')) {
	pdo_query("ALTER TABLE ".tablename('mon_egg')." ADD   `xhjf_enable` int(1) DEFAULT NULL;");
}

if(!pdo_fieldexists('mon_egg', 'xhjf')) {
	pdo_query("ALTER TABLE ".tablename('mon_egg')." ADD    `xhjf` int(10) DEFAULT NULL;");
}
