<?php

class Tuuti_Article_Row extends Zend_Db_Table_Row {
	
	public function _postDelete() {
		$priority_manager = $this->getTable()->getPriorityManager();
		$priority_manager->removePriority($this->priority, $priority_manager->getKeyFromRow($this));
	}
	
	public function _insert() {
		$current_date = date('Y-m-d H:i:s');
		
		if (is_null($this->date_custom)) {
			$this->date_custom = $current_date;
		}
		
		if (is_null($this->title)) {
			$this->title = "New article";
		}
		
		if (is_null($this->created_on)) {
			$this->created_on = $current_date;
		}

		if (is_null($this->modified_on)) {
			$this->modified_on = $current_date;
		}

		if (is_null($this->created_by)) {
			$this->created_by = "system";
		}

		if (is_null($this->modified_by)) {
			$this->modified_by = "system";
		}
		
		if (is_null($this->priority)) {
			$this->getTable()->getPriorityManager()->setNext($this);
		}
	}
	
}