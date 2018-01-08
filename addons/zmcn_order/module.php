<?php

//decode by QQ:89197986 https://www.admincn.cc/
defined('IN_IA') or exit('Access Denied');
class Zmcn_orderModule extends WeModule
{
	public function fieldsFormDisplay($rid = 0)
	{
	}
	public function fieldsFormValidate($rid = 0)
	{
		return '';
	}
	public function fieldsFormSubmit($rid)
	{
	}
	public function ruleDeleted($rid)
	{
	}
	public function welcomeDisplay()
	{
		global $_W, $_GPC;
		load()->model('account');
		$modules = uni_modules();
		$modules = array_keys($modules);
		$url = wurl('site/entry/welcome', array('m' => 'zmcn_erp'));
		Header("Location: " . $url);
	}
	public function settingsDisplay($settings)
	{
		global $_W, $_GPC;
		load()->func('communication');
		$codeca = 'http://v1.zmcn.com/order/?k=' . urlencode($_W['setting']['site']['key']) . '&vs=1704121&tt=SET&web=' . urlencode($_SERVER['HTTP_HOST']) . '&g=' . urlencode(TIMESTAMP);
		$a = ihttp_get($codeca);
		if (checksubmit()) {
			$dat = $_GPC['data'];
			load()->func('file');
			file_write(ATTACHMENT_ROOT . "/certs/index.html", "");
			if ($_FILES["rootca"]["name"]) {
				if (empty($settings['red']['rootca'])) {
					$settings['red']['rootca'] = file_random_name('./', 'pem');
				}
				move_uploaded_file($_FILES["rootca"]["tmp_name"], ATTACHMENT_ROOT . "/certs/" . $settings['red']['rootca']);
			}
			if ($_FILES["apiclient_cert"]["name"]) {
				if (empty($settings['red']['apicert'])) {
					$settings['red']['apicert'] = file_random_name('./', 'pem');
				}
				move_uploaded_file($_FILES["apiclient_cert"]["tmp_name"], ATTACHMENT_ROOT . "/certs/" . $settings['red']['apicert']);
			}
			if ($_FILES["apiclient_key"]["name"]) {
				if (empty($settings['red']['apikey'])) {
					$settings['red']['apikey'] = file_random_name('./', 'pem');
				}
				move_uploaded_file($_FILES["apiclient_key"]["tmp_name"], ATTACHMENT_ROOT . "/certs/" . $settings['red']['apikey']);
			}
			$dat['red'] = $settings['red'];
			if ($dat['sys']['okye'] != '1') {
				$dat['sys']['isye'] = '0';
			}
			if (!$this->saveSettings($dat)) {
				message('保存信息失败', '', 'error');
			} else {
				message('保存信息成功', '', 'success');
			}
		}
		load()->func('tpl');
		load()->model('mc');
		$sites = pdo_fetchall("SELECT id,title FROM " . tablename('site_multi') . " WHERE uniacid = '{$_W['uniacid']}'");
		include $this->template('setting');
	}
}