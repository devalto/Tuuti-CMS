<?php

class Tuuti_Section_Row extends Zend_Db_Table_Row {

	/**
	 * Get the name of the section
	 *
	 * @param string $lang prefered language. If none given, it gets the language of the application.
	 * @return string
	 */
	public function getName($lang = null) {
		if (is_null($lang)) {
			$lang = Application::getInstance()->getLocale()->getLanguage();
		}

		$names = $this->getNames();

		$names_by_lang = array();
		$names_by_priority = array();
		foreach ($names as $name) {
			$names_by_lang[$name['abbrev']] = $name;
			$names_by_priority[$name['priority']] = $name;
		}

		if (isset($names_by_lang[$lang])) {
			return $names_by_lang[$lang]['value'];
		}

		$name = array_shift($names_by_priority);
		return $name['value'];
	}
	
	public function getNames() {
		$select = $this->getTable()->getAdapter()->select();
		return $select->from('SECTION_NAME', 'value')
						->join('LANGUAGE', 'fk_lang = LANGUAGE.id', array('abbrev', 'priority'))
						->where('SECTION_NAME.fk_section = ?', $this->id)
						->order('LANGUAGE.priority')
						->query()->fetchAll();
	}
	
	public function getNamesByLanguageFilled() {
		$tbl_lang = new Tuuti_Language();
		$languages = $tbl_lang->getAvailableLanguages();
		
		$db = $this->getTable()->getAdapter();
		$q = $db->quoteInto("SELECT abbrev, SECTION_NAME.value FROM LANGUAGE INNER JOIN SECTION_NAME ON LANGUAGE.id = SECTION_NAME.fk_lang WHERE fk_section = ? ORDER BY LANGUAGE.priority", $this->id);
		
		$names = $db->fetchPairs($q);
		
		$langs_fetched = array_keys($names);
		$top_name = $names[$langs_fetched[0]];
		
		foreach ($languages as $lang) {
			if (!isset($names[$lang['abbrev']])) {
				$names[$lang['abbrev']] = $top_name;
			}
		}
		
		return $names;
	}
	
	public function getNamesByLanguage() {
		$q = "SELECT abbrev, '' FROM LANGUAGE ORDER BY priority";
		$languages = $this->getTable()->getAdapter()->fetchPairs($q);
		
		$names = $this->getNames();
		foreach ($names as $name) {
			if (isset($languages[$name['abbrev']])) {
				$languages[$name['abbrev']] = $name['value'];
			}
		}
		
		return $languages;
	}
	
	public function setName($lang_abbrev, $name) {
		if (empty($this->_cleanData)) {
			throw new Exception('Cannot set the name of a not yet saved section');
		}
		
		$tbl_language = new Tuuti_Language();
		$db = $tbl_language->getAdapter();
		
		$lang = $tbl_language->fetchRow($db->quoteInto('abbrev = ?', $lang_abbrev));
		if (is_null($lang)) {
			throw new Exception('The language "' . $lang_abbrev . '" doesn\'t exist');
		}
		
		$name = trim($name);
		if (empty($name)) {
			$q = "DELETE FROM SECTION_NAME WHERE fk_lang = ? AND fk_section = ?";
			
			$db->query($q, array($lang->id, $this->id));
		} else {
			$q =  "INSERT INTO SECTION_NAME (value, fk_lang, fk_section) VALUES (?, ?, ?) ";
			$q .= "ON DUPLICATE KEY UPDATE value = ?";

			$db->query($q, array($name, $lang->id, $this->id, $name));
		}
	}
	
	public function getArticlesByLanguage($lang = null) {
		$tbl_lang = new Tuuti_Language();
		$tbl_article = new Tuuti_Article();
		
		$langs = $tbl_lang->getAvailableLanguages();
		$select = $tbl_article->getAdapter()->select()->from('ARTICLE')
										->joinInner('LANGUAGE', 'LANGUAGE.id = ARTICLE.fk_lang', 'abbrev')
										->where('ARTICLE.fk_section = ?', $this->id)
										->order('LANGUAGE.priority');								
		$select = $this->addOrderField($select);
		
		if (!is_null($lang)) {
			$select = $select->where('LANGUAGE.abbrev = ?', $lang);
		}
		
		$out = array();
		$articles = $select->query()->fetchAll();
		
		foreach ($articles as $article) {
			if (!isset($out[$article['abbrev']])) {
				$out[$article['abbrev']] = array();
			}
			
			$out[$article['abbrev']][] = $article;
		}
		
		return $out;
		
	}
	
	public function setPageArticle($lang, $content, $properties = array()) {
		$tbl_lang = new Tuuti_Language();
		$row_lang = $tbl_lang->getLangByAbbrev($lang);
		
		if (is_null($row_lang)) {
			throw new Exception('Invalid language abbreviation');
		}
		
		$tbl_article = new Tuuti_Article();
		$select = $tbl_article->select()->where('fk_lang = ?', $row_lang->id)
										->where('fk_section = ?', $this->id)->limit(1, 0);
										
		$this->addOrderField($select);
		
		$article = $tbl_article->fetchRow($select);
		if (is_null($article)) {
			$article = $tbl_article->createRow();
		}
		
		if (trim($content) == "") {
			if (!is_null($article->id)) {
				$article->delete();
			}
		} else {
			$article->fk_lang = $row_lang->id;
			$article->fk_section = $this->id;
			$article->content = $content;
			
			foreach ($properties as $f => $v) {
				$article->$f = $v;
			}
			
			$article->save();
		}
	}

	/**
	 * Get the articles of the section
	 *
	 * @param string $lang prefered language. If none given, it gets the language of the application.
	 * @return array
	 */
	public function getArticlesByLanguagePriority($lang = null) {
		if (is_null($lang)) {
			$lang = Application::getInstance()->getLocale()->getLanguage();
		}

		$select = $this->getTable()->getAdapter()->select();
		$select = $select->from('ARTICLE', array('id', 'fk_section', 'title', 'content', 'priority'))
						->join('LANGUAGE', 'fk_lang = LANGUAGE.id', array('abbrev', 'priority AS lang_priority'))
						->where('ARTICLE.fk_section = ?', $this->id);

		$select = $this->addOrderField($select);

		$articles = $select->order('lang_priority')->query()->fetchAll();

		$articles_by_priority_by_lang = array();
		foreach ($articles as $article) {
			if (!isset($articles_by_priority_by_lang[$article['priority']])) {
				$articles_by_priority_by_lang[$article['priority']] = array();
			}

			$articles_by_priority_by_lang[$article['priority']][$article['abbrev']] = $article;
		}

		$out = array();
		foreach ($articles_by_priority_by_lang as $articles_by_lang) {
			if (isset($articles_by_lang[$lang])) {
				$out[] = $articles_by_lang[$lang];
			} else {
				$out[] = array_shift($articles_by_lang);
			}
		}

		return $out;

	}
	
	public function addOrderField($select) {
		switch ($this->article_order_field) {
			case Tuuti_Section::CREATION_DATE:
				$select = $select->order('ARTICLE.created_on');
				break;
			case Tuuti_Section::MODIFICATION_DATE:
				$select = $select->order('ARTICLE.modified_on');
				break;
			case Tuuti_Section::CUSTOM_DATE:
				$select = $select->order('ARTICLE.date_custom');
				break;
			case Tuuti_Section::ALPHABETICAL:
				$select = $select->order('ARTICLE.title');
				break;
			case Tuuti_Section::PRIORITY:
				$select = $select->order('ARTICLE.priority');
				break;
		}
		
		return $select;
	}
	
	public function getArticleCount() {
		$db = $this->getAdapter();
		return $db->fetchOne($db->quoteInto('SELECT COUNT(*) FROM ARTICLE WHERE fk_section = ?'), $this->id);
	}

	public function getPageArticleRow($lang) {
		$tbl_article = new Tuuti_Article();
		
		$select = $tbl_article->select();
		$select = $select->from('ARTICLE')
						 ->joinInner('LANGUAGE', 'ARTICLE.fk_lang = LANGUAGE.id', array())
						 ->where('fk_section = ?', $this->id);
		$select = $this->addOrderField($select);
		
		return $tbl_article->fetchRow($select);
	}

	public function getPageArticleByLanguage() {
		$q = "SELECT abbrev, '' FROM LANGUAGE ORDER BY priority";
		$languages = $this->getTable()->getAdapter()->fetchPairs($q);
		foreach ($languages as $abbrev => $val) {
			$languages[$abbrev] = null;
		}
		
		$select = $this->getTable()->getAdapter()->select();
		$select = $select->from('ARTICLE', array('ARTICLE.id', 'ARTICLE.content'))
						 ->joinInner('LANGUAGE', 'ARTICLE.fk_lang = LANGUAGE.id', 'LANGUAGE.abbrev')
						 ->where('fk_section = ?', $this->id);
		$select = $this->addOrderField($select);
		
		$articles = $select->query()->fetchAll();
		
		$lang_count = count($languages);
		$set_count = 0;
		
		foreach ($articles as $article) {
			if (is_null($languages[$article['abbrev']])) {
				$languages[$article['abbrev']] = $article['content'];
				$set_count++;
			}
			
			if ($set_count == $lang_count) {
				break;
			}
		}
		
		return $languages;
	}
	
	public function getPageTitleByLanguage() {
		/**
		 * Get section title by default.
		 */
		$languages = $this->getNamesByLanguageFilled();
		
		$select = $this->getTable()->getAdapter()->select();
		$select = $select->from('ARTICLE', array('ARTICLE.id', 'ARTICLE.title'))
						 ->joinInner('LANGUAGE', 'ARTICLE.fk_lang = LANGUAGE.id', 'LANGUAGE.abbrev')
						 ->where('fk_section = ?', $this->id);
		$select = $this->addOrderField($select);
		
		$titles = $select->query()->fetchAll();
		
		$lang_count = count($languages);
		$set_count = 0;
		
		foreach ($titles as $title) {
			$languages[$title['abbrev']] = $title['title'];
		}
		
		return $languages;
	}
}