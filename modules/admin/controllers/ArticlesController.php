<?php

class Admin_ArticlesController extends Zend_Controller_Action {
	
	public function init() {
		$contextSwitch = $this->_helper->getHelper('contextSwitch');
		$contextSwitch->addActionContext('update-priority', 'json')->initContext();
		
		$this->view->headTitle(Application::getInstance()->getTitle());
		$this->view->headTitle()->setSeparator(' - ');
		
		$this->view->messages = $this->_helper->_flashMessenger->getMessages();
	}
	
	public function updateAction() {
		$article = $this->_getArticle($this->_getParam('id'));
		$section = $article->findParentRow('Tuuti_Section');
		
		$db = $section->getTable()->getAdapter();
		$count = $db->fetchOne('SELECT COUNT(*) FROM ARTICLE WHERE fk_section = ?', $section->id);
		
		if ($section->type == Tuuti_Section::ONE_ARTICLE && $count) {
			$this->_helper->redirector->gotoRoute(array('action' => 'update'));
			return;
		}
		
		$form = new Tuuti_Article_Form();
		$this->view->form = $form;
		
		$form->populate(array(
			'language' => $article->fk_lang,
			'title' => $article->title,
			'content' => $article->content,
			'date_custom' => $article->date_custom
		));
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				
				$article->title = $values['title'];
				$article->content = $values['content'];
				$article->date_custom = empty($values['date_custom']) ? null : $values['date_custom'];
				$article->fk_lang = $values['language'];
				
				if ($article->save()) {
					$this->_helper->flashMessenger->addMessage('Article updated sucessfully');
					$this->_helper->redirector->gotoRoute(array('action' => 'list', 'section-id' => $section->id, 'id' => null), 'default');
				}
			}
		}
		
	}
	
	public function deleteAction() {
		$article = $this->_getArticle($this->_getParam('id'));
		$fk_section = $article->fk_section;
		$article->delete();
		
		$this->_helper->flashMessenger->addMessage('Article deleted sucessfully');
		$this->_helper->redirector->gotoRoute(array('action' => 'list', 'section-id' => $fk_section, 'id' => null));
	}
	
	public function listAction() {
		$section = $this->_getSection($this->_getParam('section-id'));
		
		$tbl_lang = new Tuuti_Language();
		$this->view->languages = $tbl_lang->getAvailableLanguages();
		
		$this->view->articles = $section->getArticlesByLanguage();
	}
	
	public function addAction() {
		$section = $this->_getSection($this->_getParam('section-id'));
		$db = $section->getTable()->getAdapter();
		$count = $db->fetchOne('SELECT COUNT(*) FROM ARTICLE WHERE fk_section = ?', $section->id);
		
		if ($section->type == Tuuti_Section::ONE_ARTICLE && $count) {
			$this->_helper->redirector->gotoRoute(array('action' => 'update'));
			return;
		}
		
		$tbl_article = new Tuuti_Article();
		$article = $tbl_article->createRow(array(
			'fk_section' => $section->id
		));
		
		$form = new Tuuti_Article_Form();
		$this->view->form = $form;
		
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$values = $form->getValues();
				
				$article->title = $values['title'];
				$article->content = $values['content'];
				$article->date_custom = empty($values['date_custom']) ? null : $values['date_custom'];
				$article->fk_lang = $values['language'];
				
				if ($article->save()) {
					$this->_helper->flashMessenger->addMessage('Article added sucessfully');
					$this->_helper->redirector->gotoRoute(array('action' => 'list', 'id' => null), 'default');
				}
			}
		}
	}
	
	public function updatePageAction() {
		$section = $this->_getSection($this->_getParam('section-id'));
		$db = $section->getTable()->getAdapter();
		
		$form = new Tuuti_Article_PageForm($section);
		
		$this->view->form = $form;
		
		$titles = $section->getPageTitleByLanguage();
		
		$values = array(
			'content' => $section->getPageArticleByLanguage(),
			'title' => $titles
		);
		
		$form->populate($values);
	
		if ($this->getRequest()->isPost()) {
			if ($form->isValid($_POST)) {
				$db->beginTransaction();
			
				$values = $form->getValues();
			
				// Setting of the names of the section in each languages
				foreach ($values['content'] as $lang => $article) {
					$section->setPageArticle($lang, $article, array('title' => $values['title'][$lang]));
					
					if (trim($article) == "") {
						$form->title->{$lang}->setValue($titles[$lang]);
					}
				}
			
				$db->commit();
				$this->view->messages = array('Section updated sucessfully');
			}
		}
	}

	public function updatePriorityAction() {
		$article = $this->_getArticle($this->_getParam('id'));
		$new_priority = (int) $this->_getParam('new_priority');

		$mgr = $article->getTable()->getPriorityManager();

		$this->view->status = !is_null($mgr->update($article, $new_priority));
	}
	
	public function _getSection($id) {
		$tbl = new Tuuti_Section();
		
		if (is_null($id) || !($row = $tbl->fetchRow($tbl->getAdapter()->quoteInto('id = ?', $id)))) {
			throw new Exception('Invalid section identifier');
		}
		
		return $row;
	}
	
	public function _getArticle($id) {
		$tbl = new Tuuti_Article();
		
		if (is_null($id) || !($row = $tbl->fetchRow($tbl->getAdapter()->quoteInto('id = ?', $id)))) {
			throw new Exception('Invalid article identifier');
		}
		
		return $row;
	}
	
}