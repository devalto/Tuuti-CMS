<?php

class Tuuti_Article extends Zend_Db_Table {
	
	protected $_primary = 'id';
	protected $_name = 'ARTICLE';
	protected $_rowClass = 'Tuuti_Article_Row';
	
	protected $_referenceMap = array(
		'ParentSection' => array(
			'columns' 		=> 'fk_section',
			'refTableClass' => 'Tuuti_Section',
			'refColumns' 	=> 'id'
		)
	);
	
	public function getPriorityManager() {
		return new Tuuti_Db_PriorityManager($this, array('fk_section', 'fk_lang'));
	}
	
}