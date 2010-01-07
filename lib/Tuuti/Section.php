<?php

class Tuuti_Section extends Zend_Db_Table {

	const MULTIPLE_ARTICLE = "MULTIPLE ARTICLE";
	const ONE_ARTICLE = "ONE ARTICLE";
	const BLANK = "BLANK";

	const CREATION_DATE = "CREATION DATE";
	const MODIFICATION_DATE = "MODIFICATION DATE";
	const CUSTOM_DATE = "CUSTOM DATE";
	const ALPHABETICAL = "ALPHABETICAL";
	const PRIORITY = "PRIORITY";
	
	const PRETTY_URL_REGEX = '(?=^(?!admin$))[-a-zA-Z0-9]+$';

	protected $_primary = 'id';
	protected $_name = 'SECTION';
	protected $_rowClass = 'Tuuti_Section_Row';
	
	protected $_dependentTables = array(
		'Tuuti_Article'
	);

	/**
	 * Get the sections of the website
	 *
	 * Labels of the sections are in the language preferences of the user and
	 * when it is not possible, it switches to the second language available in the
	 * list of sections.
	 *
	 * @param array $lang_priority
	 * @return array
	 */
	public function getSections($lang_priority) {
		$q = "
  SELECT LANGUAGE.abbrev,
         SECTION_NAME.value,
         SECTION.id,
         SECTION.pretty_url_title,
         SECTION.priority,
         SECTION.display_in_nav
    FROM SECTION INNER JOIN SECTION_NAME ON SECTION.id = SECTION_NAME.fk_section
                 INNER JOIN LANGUAGE ON SECTION_NAME.fk_lang = LANGUAGE.id
ORDER BY SECTION.priority,
         LANGUAGE.priority";

		$sections_by_lang = $this->getAdapter()->fetchAll($q);
		$sections = array();
		$sections_priority = array();
		foreach ($sections_by_lang as $section) {
			if (!isset($sections[$section['id']])) {
				$sections[$section['id']] = array();
			}
			$sections[$section['id']][$section['abbrev']] = $section;

			if (!isset($sections_priority[$section['priority']])) {
				$sections_priority[$section['priority']] = $section;
			}
		}

		$out = array();

		foreach ($sections_priority as $priority) {
			$lang_copy = $lang_priority;
			while ($lang = array_shift($lang_copy)) {
				if (isset($sections[$priority['id']][$lang])) {
					$out[] = $sections[$priority['id']][$lang];
					break;
				}
			}

		}

		return $out;
	}

	public function getDefaultSection() {
		return $this->fetchRow($this->select()->order('priority')->limit(1, 0));
	}
	
	public function getPriorityManager() {
		return new Tuuti_Db_PriorityManager($this);
	}

}