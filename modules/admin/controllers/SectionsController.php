<?php

class Admin_SectionsController extends Zend_Controller_Action {
	
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->addActionContext('update-priority', 'json')->initContext();
		
		$this->view->headTitle(Application::getInstance()->getTitle());
		$this->view->headTitle()->setSeparator(' - ');
	}
	
	public function indexAction() {
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
		$tbl_lang = new Tuuti_Language();
		$this->view->lang = $tbl_lang->getPriorityManager()->getFirstRow();
		
		$tbl_section = new Tuuti_Section();
		$this->view->sections = $tbl_section->fetchAll(
			$tbl_section->select()->order('priority')
		);
	}
	
	public function addAction() {
		$this->view->headTitle('Add section');
		
		$tbl_section = new Tuuti_Section();
		$db = $tbl_section->getAdapter();
		
		$this->view->lang_count = $db->fetchOne('SELECT COUNT(*) FROM LANGUAGE');
		
		if ($this->view->lang_count) {
			$form = new Tuuti_Section_Form(new Tuuti_Language());
			$this->view->form = $form;
		
			if ($this->getRequest()->isPost()) {
				if ($form->isValid($_POST)) {
					$db->beginTransaction();
				
					$values = $form->getValues(true);
					$section = $tbl_section->createRow(array(
						'priority' => $tbl_section->getPriorityManager()->getMax() + 1,
						'nb_column' => 1
					));
				
					$cols = array('pretty_url_title', 'type', 'article_order_field', 'priority', 'display_in_nav');
					foreach ($cols as $col_name) {
						if (isset($values[$col_name])) {
							$section->$col_name = $values[$col_name];
						}
					}
				
					$section->save();
				
					// Setting of the names of the section in each languages
					foreach ($values['name'] as $lang => $value) {
						$section->setName($lang, $value);
					}
				
					$db->commit();
					$this->_helper->flashMessenger->addMessage('Section added sucessfully');
					$this->_helper->redirector->gotoRoute(array('action' => 'index'), 'default');
				}
			}
		}
	}
	
	public function updateAction() {
		$this->view->headTitle('Update section');
		
		$section = $this->_getSection($this->_getParam('id'));
		
		$tbl_section = new Tuuti_Section();
		$db = $tbl_section->getAdapter();
		
		$form = new Tuuti_Section_Form(new Tuuti_Language());
		$this->view->form = $form;
		
		$values = $section->toArray();
		$values['name'] = $section->getNamesByLanguage();
		
		$form->populate($values);
	
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$db->beginTransaction();
			
				$values = $form->getValues(true);
			
				$cols = array('pretty_url_title', 'type', 'article_order_field', 'priority', 'display_in_nav');
				foreach ($cols as $col_name) {
					if (isset($values[$col_name])) {
						$section->$col_name = $values[$col_name];
					}
				}
			
				$section->save();
			
				// Setting of the names of the section in each languages
				foreach ($values['name'] as $lang => $value) {
					$section->setName($lang, $value);
				}
			
				$db->commit();
				$this->_helper->flashMessenger->addMessage('Section updated sucessfully');
				$this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null), 'default');
			}
		}
	}

	public function deleteAction() {
		$row = $this->_getSection($this->_getParam('id'));
		$row->delete();
		
		$this->_helper->flashMessenger->addMessage('Section deleted sucessfully');
		$this->_helper->redirector->gotoRoute(array('action' => 'index', 'id' => null), 'default');
	}
	
	public function updatePriorityAction() {
		$section = $this->_getSection($this->_getParam('id'));
		$new_priority = (int) $this->_getParam('new_priority');
		
		$mgr = $section->getTable()->getPriorityManager();
		
		$this->view->status = !is_null($mgr->update($section, $new_priority));
	}
	
	public function _getSection($id) {
		$tbl = new Tuuti_Section();
		
		if (is_null($id) || !($row = $tbl->fetchRow($tbl->getAdapter()->quoteInto('id = ?', $id)))) {
			throw new Exception('Invalid section identifier');
		}
		
		return $row;
	}
}