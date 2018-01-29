<?php

 if(!pdo_fieldexists('ewei_shop_creditshop_log', 'storeid')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_creditshop_log')." ADD    `storeid` int(11) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_creditshop_log', 'realname')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_creditshop_log')." ADD   `realname` varchar(255) DEFAULT '';");
}
 if(!pdo_fieldexists('ewei_shop_creditshop_log', 'mobile')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_creditshop_log')." ADD   `mobile` varchar(255) DEFAULT '';");
}
 if(!pdo_fieldexists('ewei_shop_order', 'ordersn2')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `ordersn2` int(11) DEFAULT '0';");
}
if(!pdo_fieldexists('ewei_shop_order', 'changeprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `changeprice` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('ewei_shop_order', 'changedispatchprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `changedispatchprice` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('ewei_shop_order', 'oldprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `oldprice` decimal(10,2) DEFAULT '0.00';");
}
if(!pdo_fieldexists('ewei_shop_order', 'olddispatchprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD    `olddispatchprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('ewei_shop_order', 'isvirtual')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order')." ADD   `isvirtual` tinyint(3) DEFAULT '0';");
}
 if(!pdo_fieldexists('ewei_shop_order_goods', 'changeprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order_goods')." ADD    `changeprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('ewei_shop_order_goods', 'oldprice')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order_goods')." ADD    `oldprice` decimal(10,2) DEFAULT '0.00';");
}
 if(!pdo_fieldexists('ewei_shop_order_goods', 'commissions')) {
	pdo_query("ALTER TABLE ".tablename('ewei_shop_order_goods')." ADD    `commissions` text;");
}