<?php

class ErrorController extends Zend_Controller_Action {

	public function errorAction() {
		$this->view->section_name = "An error as occured";

		$errors = $this->_getParam('error_handler');

		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
				$this->view->error_message = "The section you tried to reach doesn't exist.";
				break;
			default:
				$this->view->error_message = "An unrecoverable error as occured.";
		}

		$this->getResponse()->clearBody();

		/**********************************************************************/

		$scripts_path = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR;
		$lang = Application::getInstance()->getLocale()->getLanguage();

		$view_name = 'error';

		if (is_readable("{$scripts_path}{$view_name}-{$lang}.phtml")) {
			$this->render("{$view_name}-{$lang}", null, true);
		} elseif (is_readable("{$scripts_path}{$view_name}.phtml")) {
			$this->render("{$view_name}", null, true);
		} else {
			throw new Exception("The view file '$view_name' is not defined");
		}
	}

}