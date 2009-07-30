<?php

class IndexController extends Zend_Controller_Action {

	public function indexAction() {
		$tbl_section = new Tuuti_Section();
		$section = $this->_getParam('section', false);
		if (!($row_section = $tbl_section->fetchRow($tbl_section->select()->where('pretty_url_title = ?', $section)))) {
			throw new Zend_Controller_Action_Exception('Invalid section', 404);
		}

		$this->view->section = $this->_getParam('section');
		$this->view->lang = Application::getInstance()->getLocale()->getLanguage();
		$this->view->section_name = $row_section->getName();

		$articles = $row_section->getArticlesByLanguagePriority();
		if ($row_section->type == Tuuti_Section::MULTIPLE_ARTICLE) {
			$this->view->articles = $articles;
		} else {
			$this->view->article = array_pop($articles);
		}

		$this->view->headTitle($this->view->section_name);
		$this->view->headTitle(Application::getInstance()->getTitle());

		$this->view->headTitle()->setSeparator(' - ');

		/**********************************************************************/

		$scripts_path = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "scripts" . DIRECTORY_SEPARATOR;
		$layout = Zend_Layout::getMvcInstance();
		$lang = Application::getInstance()->getLocale()->getLanguage();

		if (is_readable($scripts_path . "layout-$lang.phtml")) {
			$layout->setLayout("layout-$lang");
		}

		$view_name = 'one-article';
		if ($row_section->type == Tuuti_Section::MULTIPLE_ARTICLE) {
			$view_name = 'multiple-article';
		}

		if (is_readable("{$scripts_path}{$view_name}-{$lang}.phtml")) {
			$this->render("{$view_name}-{$lang}", null, true);
		} elseif (is_readable("{$scripts_path}{$view_name}.phtml")) {
			$this->render("{$view_name}", null, true);
		} else {
			throw new Exception("The view file '$view_name' is not defined");
		}
	}

}
