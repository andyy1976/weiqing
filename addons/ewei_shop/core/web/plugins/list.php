<?php

//微赞科技 by QQ:800083075 http://www.012wz.com/
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $plugins = m('plugin')->getAll();
    include $this->template('web/plugins/list');
    exit;
}