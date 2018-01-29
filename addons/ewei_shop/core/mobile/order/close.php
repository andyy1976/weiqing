<?php
error_reporting(0);
require '../../../../../framework/bootstrap.inc.php';
require '../../../../../addons/ewei_shop/defines.php';
require '../../../../../addons/ewei_shop/core/inc/functions.php';
require '../../../../../addons/ewei_shop/core/inc/plugin/plugin_model.php';
global $_W, $_GPC;
ignore_user_abort();
set_time_limit(0);
$sets = pdo_fetchall('select uniacid from ' . tablename('ewei_shop_sysset'));
foreach ($sets as $set) {
    $_W['uniacid'] = $set['uniacid'];
    if (empty($_W['uniacid'])) {
        continue;
    }
    $trade = m('common')->getSysset('trade', $_W['uniacid']);
    $days  = intval($trade['closeorder']);
    if ($days <= 0) {
        continue;
    }
    $daytimes = 86400 * $days;
    $orders   = pdo_fetchall("select id from " . tablename('ewei_shop_order') . " where  uniacid={$_W['uniacid']} and status=0 and sendtime + {$daytimes} <=unix_timestamp() ", array(), 'id');
    if (!empty($orders)) {
        $orderids = implode(",", array_keys($orders));
        if (!empty($orderids)) {
            pdo_query("update " . tablename('ewei_shop_order') . ' set status=-1,closetime=' . time() . ' where id in (' . $orderids . ')');
        }
    }
}