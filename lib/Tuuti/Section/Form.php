<?php

class Tuuti_Section_Form extends Zend_Form {
	public function __construct($tbl_lang, $options = null) {
		parent::__construct($options);
		
		$subform = new Zend_Form();
		$subform->setElementsBelongTo('name');
		$subform->removeDecorator('Form');
		
		$languages = $tbl_lang->getAvailableLanguages();
		foreach ($languages as $lang) {
			$txt = new Zend_Form_Element_Text($lang['abbrev']);
			if ($lang['priority'] == 1) {
				$txt->setRequired();
			}
			$txt->setLabel($lang['value']);
			$subform->addElement($txt);
		}
		$this->addSubForm($subform, 'name');
		
		$txt_pretty_url = new Zend_Form_Element_Text('pretty_url_title');
		$txt_pretty_url->setLabel('Url title');
		$txt_pretty_url->setRequired();
		$txt_pretty_url->addValidator(new Zend_Validate_Regex('/' . Tuuti_Section::PRETTY_URL_REGEX . '/i'));
		$this->addElement($txt_pretty_url);
		
		$lst_type = new Zend_Form_Element_Select('type');
		$lst_type->setLabel('Type');
		$lst_type->setRequired();
		$lst_type->addMultiOption('ONE ARTICLE', 'One article');
		$lst_type->addMultiOption('MULTIPLE ARTICLE', 'Multiple article');
		$this->addElement($lst_type);
		
		$lst_order = new Zend_Form_Element_Select('article_order_field');
		$lst_order->setLabel('Article ordering method');
		$lst_order->setRequired();
		$lst_order->addMultiOption('CREATION DATE', 'Creation date');
		$lst_order->addMultiOption('MODIFICATION DATE', 'Modification date');
		$lst_order->addMultiOption('CUSTOM DATE', 'Custom date');
		$lst_order->addMultiOption('ALPHABETICAL', 'Title');
		$lst_order->addMultiOption('PRIORITY', 'Priority');
		$this->addElement($lst_order);
		
		$chk_display_in_nav = new Zend_Form_Element_Checkbox('display_in_nav');
		$chk_display_in_nav->setLabel('Display in navigation');
		$chk_display_in_nav->setChecked(true);
		$this->addElement($chk_display_in_nav);
		
		$this->addElement('submit', 'save', array('label' => 'Save'));
	}
}