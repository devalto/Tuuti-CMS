<?php

class Zend_View_Helper_Navigation {

	public $view;

	public function navigation() {
		$app = Application::getInstance();
		$lang_list = $app->getLanguageListSupported();
		$current_lang = $app->getLocale()->getLanguage();

		if (false !== ($pos = array_search($current_lang, $lang_list))) {
			unset($lang_list[$pos]);
		}

		array_unshift($lang_list, $current_lang);

		$tbl_section = new Tuuti_Section();
		$sections = $tbl_section->getSections($lang_list);

		$list = array();
		foreach ($sections as $section) {
			if ($section['display_in_nav']) {
				$list[] = "<a href=\"{$this->view->url(array('section' => $section['pretty_url_title']), 'cms', true)}\">{$section['value']}</a>";
			}
		}

		return "<ul class=\"menu\"><li>" . join('</li><li>', $list) . "</li></ul>";
	}

	public function setView($view) {
		$this->view = $view;
	}

}