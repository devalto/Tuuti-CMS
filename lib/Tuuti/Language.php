<?php

class Tuuti_Language extends Zend_Db_Table {
	
	protected $_primary = 'id';
	protected $_name = 'LANGUAGE';
	protected $_rowClass = 'Tuuti_Language_Row';
	
	public function getAvailableLanguages(Zend_Locale $remove_language = null) {
		$where = $this->select()->order('priority');
		if ($remove_language != null) {
			$where = $where->where('abbrev != ?', $remove_language->getLanguage());
		}
		return $this->fetchAll($where)->toArray();
	}
	
	public function getMaxPriority() {
		$languages = $this->getAvailableLanguages();
		$c = count($languages);
		
		$priority = 0;
		if ($c) {
			$max_lang = $languages[$c - 1];
			$priority = $max_lang['priority'];
		}
		
		return $priority;
	}
	
	public function getPriorityManager() {
		return new Tuuti_Db_PriorityManager($this);
	}
	
	public function getLangByAbbrev($abbrev) {
		return $this->fetchRow($this->getAdapter()->quoteInto('abbrev = ?', $abbrev));
	}
}