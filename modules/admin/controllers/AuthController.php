<?php

class Admin_AuthController extends Zend_Controller_Action {
	
	public function init() {
		$this->view->show_actions = false;
	}
	
	public function indexAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$this->_forward('logout');
		}
		
		$form = new Zend_Form();
		$this->view->form = $form;
		
		$txt_login = new Zend_Form_Element_Text('username');
		$txt_login->setLabel("Username");
		
		$form->addElement($txt_login);
		
		$txt_password = new Zend_Form_Element_Password('password');
		$txt_password->setLabel('Password');
		
		$form->addElement($txt_password);
		
		$form->addElement('submit', 'login', array('label' => 'Login'));
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				
				$adapter = Application::getInstance()->getAuthAdapter();
				$adapter->setIdentity($values['username']);
				$adapter->setCredential($values['password']);
				
				$result = $auth->authenticate($adapter);
				
				if ($result->isValid()) {
					$this->_helper->redirector->gotoRoute(array('module' => 'admin'), 'default', true);
				} else {
					$this->view->error = "Unable to authenticate you to the administration, try again";
				}
				
			}
		}
		
	}
	
	public function logoutAction() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			Zend_Auth::getInstance()->clearIdentity();
		}
		
		$this->_forward('index');
	}
	
}