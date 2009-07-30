<?php

class Tuuti_Article_PageForm extends Zend_Form {
	
	public function __construct($section, $tbl_lang = null, $options = null) {
		parent::__construct($options);
		
		if (is_null($tbl_lang)) {
			$tbl_lang = new Tuuti_Language();
		}
		
		$languages = $tbl_lang->getAvailableLanguages();
		$names = $section->getNamesByLanguageFilled();
		$abbrevs = array();
		
		$title_sub_form = new Zend_Form_SubForm();
		$title_sub_form->setElementsBelongTo('title');
		
		$content_sub_form = new Zend_Form_SubForm();
		$content_sub_form->setElementsBelongTo('content');
		
		foreach ($languages as $lang) {
			$txt_title = new Zend_Form_Element_Text($lang['abbrev']);
			$txt_title->setLabel('Title');
			$txt_title->setOptions(array(
				'belongsTo' => 'title',
				'decorators' => array('Label', 'ViewHelper')
			));
			
			$title_sub_form->addElement($txt_title);
			
			$txt_content = new Tuuti_Form_Element_TinyMCE($lang['abbrev'], array(
				'jsScriptPath' => 'modules/admin/javascripts/tiny_mce/tiny_mce.js'
			));
			$txt_content->setLabel('Content');
			$txt_content->setOptions(array(
				'rows' => 7, 
				'cols' => 70, 
				'style' => 'width: 100%;',
				'class' => 'mce',
				'decorators' => array(
					'ViewHelper'
				),
				'belongsTo' => 'content'
			));
			
			$content_sub_form->addElement($txt_content);
		}
		
		$this->addSubform($title_sub_form, 'title');
		$this->addSubform($content_sub_form, 'content');
		
	}
	
}