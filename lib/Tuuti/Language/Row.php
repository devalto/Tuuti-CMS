<?php

class Tuuti_Language_Row extends Zend_Db_Table_Row {
	
	public function _postDelete() {
		$this->getTable()->getPriorityManager()->removePriority($this->priority);
		
		$db = $this->getTable()->getAdapter();
		$q = "DELETE FROM SECTION WHERE id NOT IN (SELECT fk_section FROM SECTION_NAME)";
		$db->query($q);
	}
	
	public function updatePriority($new_priority) {
		if (empty($this->_cleanData)) {
			throw new Exception('Cannot reorder a new language');
		}
		
		if ($new_priority == $this->priority) {
			return;
		}
		
		$max_priority = $this->getTable()->getMaxPriority();
		if ($new_priority > $max_priority || $new_priority < 1) {
			throw new Exception("Cannot reorder the language to a priority lower than 1 or bigger than the maximum priority");
		}
		
		$db = $this->getTable()->getAdapter();
		$db->beginTransaction();

		$old_priority = $this->priority;
		
		/**
		 * Must change the current priority to 0. This is required to satisfy
		 * the uniqueness of the abbrev field while updating the priority of
		 * the other language entry.
		 */
		$this->priority = 0;
		$this->save();
		
		if ($new_priority < $old_priority) {
			$q = "UPDATE LANGUAGE SET priority = priority + 1 WHERE priority < ? AND priority >= ?";
			$db->query($q, array($old_priority, $new_priority));
		} elseif ($new_priority > $old_priority) {
			$q = "UPDATE LANGUAGE SET priority = priority - 1 WHERE priority <= ? AND priority > ?";
			$db->query($q, array($new_priority, $old_priority));
		}
		
		$this->priority = $new_priority;
		
		if ($this->save()) {
			$db->commit();
		}
		
		return true;
		
	}
	
}