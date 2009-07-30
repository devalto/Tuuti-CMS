<?php

class Tuuti_Controller_Plugin_Language extends Zend_Controller_Plugin_Abstract {

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$app = Application::getInstance();
		if ($lang = $request->getParam('lang', false)) {
			$app->setLanguage($lang);
		}

		parent::dispatchLoopStartup($request);
	}

}