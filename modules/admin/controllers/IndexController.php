<?php

class Admin_IndexController extends Zend_Controller_Action {

	public function indexAction() {
		$tbl_lang = new Tuuti_Language();
		if (!count($tbl_lang->getAvailableLanguages())) {
			$this->_helper->redirector->gotoRoute(array('controller' => 'languages', 'action' => 'index'), 'default');
		} else {
			$this->_helper->redirector->gotoRoute(array('controller' => 'sections', 'action' => 'index'), 'default');
		}
	}

}