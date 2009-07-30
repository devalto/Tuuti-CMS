<?php

class Tuuti_Article_Form extends Zend_Form {
	
	public function __construct($tbl_lang = null, $options = null) {
		parent::__construct($options);
		
		if (is_null($tbl_lang)) {
			$tbl_lang = new Tuuti_Language();
		}
		
		if (!is_string($tbl_lang)) {
			$languages = $tbl_lang->getAvailableLanguages();
		} else {
			$abbrev = $tbl_lang;
			$tbl_lang = new Tuuti_Language();
			$languages = array($tbl_lang->getLangByAbbrev($abbrev)->toArray());
		}
		
		$lst_language = new Zend_Form_Element_Select('language');
		$lst_language->setRequired();
		$lst_language->setLabel('Language');
		foreach ($languages as $lang) {
			$lst_language->addMultiOption($lang['id'], $lang['value']);
		}
		
		$this->addElement($lst_language);
		
		$txt_title = new Zend_Form_Element_Text('title');
		$txt_title->setRequired();
		$txt_title->setLabel('Title');
		
		$this->addElement($txt_title);
		
		$txt_content = new Tuuti_Form_Element_TinyMCE('content', array(
			'jsScriptPath' => 'modules/admin/javascripts/tiny_mce/tiny_mce.js',
			'decorators' => array('ViewHelper')
		));
		$txt_content->setRequired();
		
		$this->addElement($txt_content);
		
		$txt_date_custom = new Zend_Form_Element_Text('date_custom');
		$txt_date_custom->setValue(date('Y-m-d h:i:s'));
		$txt_date_custom->setLabel('Custom date');
		$txt_date_custom->setDescription("Format: yyyy-mm-dd hh:mm:ss");
		
		$this->addElement($txt_date_custom);
		
		$this->addElement('submit', 'save', array('label' => 'Save'));
		
	}
	
}