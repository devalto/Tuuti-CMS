<?php

class Admin_LanguagesController extends Zend_Controller_Action {
	
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->addActionContext('update-priority', 'json')->initContext();
		
		$this->view->headTitle(Application::getInstance()->getTitle());
		$this->view->headTitle()->setSeparator(' - ');
		
		$this->view->messages = $this->_helper->_flashMessenger->getMessages();
	}
	
	public function indexAction() {
		$this->view->headTitle('Languages list');
		
		$tbl_languages = new Tuuti_Language();
		$select = $tbl_languages->select()->order('priority');
		$this->view->languages = $tbl_languages->fetchAll($select);
	}
	
	public function addAction() {
		$this->view->headTitle('Add language');
		
		$tbl_language = new Tuuti_Language();
		
		$form = new Tuuti_Language_Form();
		$this->view->form = $form;
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues(true);
				$cols = $tbl_language->info(Zend_Db_Table_Abstract::COLS);
				
				$lang = array('priority' => $tbl_language->getPriorityManager()->getMax() + 1);
				foreach ($cols as $col_name) {
					if (isset($values[$col_name])) {
						$lang[$col_name] = $values[$col_name];
					}
				}
				
				if ($tbl_language->insert($lang)) {
					$this->_helper->flashMessenger->addMessage('Language added sucessfully');
					$this->_helper->redirector->gotoRoute(array('action' => 'index'), 'default');
				}
			}
		}
	}
	
	public function updateAction() {
		$this->view->headTitle('Update language');
		
		$lang = $this->_getLanguage($this->_getParam('id'));
		
		$form = new Tuuti_Language_Form();
		$this->view->form = $form;
		
		$form->populate($lang->toArray());
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues(true);
				$cols = array('abbrev', 'value');
				
				foreach ($cols as $col_name) {
					if (isset($values[$col_name])) {
						$lang->$col_name = $values[$col_name];
					}
				}
				
				if ($lang->save()) {
					$this->_helper->flashMessenger->addMessage('Language updated sucessfully');
					$this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null), 'default');
				}
			}
		}
	}
	
	public function deleteAction() {
		$lang = $this->_getLanguage($this->_getParam('id'));
		$lang->delete();
		
		$this->_helper->flashMessenger->addMessage('Language deleted sucessfully');
		$this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null), 'default');
	}
	
	public function updatePriorityAction() {
		$lang = $this->_getLanguage($this->_getParam('id'));
		$new_priority = (int) $this->_getParam('new_priority');
		
		$mgr = $lang->getTable()->getPriorityManager();
		
		$this->view->status = !is_null($mgr->update($lang, $new_priority));
	}
	
	private function _getLanguage($id) {
		$tbl_language = new Tuuti_Language();
		
		if (is_null($id) || !($lang = $tbl_language->fetchRow($tbl_language->getAdapter()->quoteInto('id = ?', $id)))) {
			throw new Exception('Invalid language identifier');
		}
		
		return $lang;
	}
	
}