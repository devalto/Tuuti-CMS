<?php

class Tuuti_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract {

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
		$params = $request->getParams();
		if (($params['module'] == 'admin' && $params['controller'] != 'auth') && !Zend_Auth::getInstance()->hasIdentity()) {
			$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$redirector->gotoRouteAndExit(array('module' => 'admin', 'controller' => 'auth'), 'default', true);
		}
	}
	
}