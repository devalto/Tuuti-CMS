<?php

class Tuuti_Language_Form extends Zend_Form {
	public function __construct($options = null) {
		parent::__construct($options);
		
		$txt_abbrev = new Zend_Form_Element_Text('abbrev');
		$txt_abbrev->setLabel('Abbreviation');
		$txt_abbrev->setRequired();
		$txt_abbrev->addFilter('StringToLower');
		$txt_abbrev->addValidator(new Zend_Validate_StringLength(2, 2));
		$this->addElement($txt_abbrev);
		
		$txt_value = new Zend_Form_Element_Text('value');
		$txt_value->setLabel('Value');
		$txt_value->setRequired();
		$this->addElement($txt_value);
		
		$this->addElement('submit', 'enregistrer', array('label' => 'Save'));
		
	}
}