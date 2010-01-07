<?php

class Tuuti_Db_PriorityManager {
	
	private $_table;
	
	private $_field;
	
	private $_unique_key = array();
	
	public function __construct(Zend_Db_Table $table, $unique_key = array(), $field = 'priority') {
		$this->_table = $table;
		$this->_field = $field;
		$this->_unique_key = $unique_key;
	}
	
	public function getFirstRow() {
		if (!empty($this->_unique_key)) {
			throw new Exception('Not yet implemented (priority with unique key)');
		}
		
		return $this->_table->fetchRow($this->_table->select()->order($this->_field)->limit(1, 0));
	}
	
	public function getKeyFromRow($row) {
		$key = array();
		foreach ($this->_unique_key as $f) {
			$key[$f] = $row->$f;
		}
		
		return $key;
	}
	
	public function getMax($key = array()) {
		$tbl_name = $this->_table->info(Zend_Db_Table::NAME);
		$db = $this->_table->getAdapter();
		
		$select = $db->select()->from($tbl_name, new Zend_Db_Expr('MAX(' . $db->quoteIdentifier($this->_field) . ')'));
		foreach ($key as $f => $v) {
			if (is_null($v)) {
				$select->where($db->quoteIdentifier($f) . ' IS NULL');
			} else {
				$select->where($db->quoteIdentifier($f) . ' = ?', $v);
			}
		}
		
		$val = $db->fetchOne($select);
		
		return (!is_null($val) ? $val : 0);
	}
	
	public function update(Zend_Db_Table_Row $row, $new_priority) {
		
		if (is_null($row->id)) {
			throw new Exception('Cannot reorder a new row');
		}
		
		if ($new_priority == $row->{$this->_field}) {
			return;
		}
		
		$max_priority = $this->getMax();
		if ($new_priority > $max_priority || $new_priority < 1) {
			throw new Exception("Cannot reorder the row to a priority lower than 1 or bigger than the maximum priority");
		}
		
		$db = $this->_table->getAdapter();
		$db->beginTransaction();

		$old_priority = $row->{$this->_field};
		
		/**
		 * Must change the current priority to 0. This is required to satisfy
		 * the uniqueness of the priority field while updating the priority of
		 * the other entries.
		 */
		$row->{$this->_field} = 0;
		$row->save();
		
		$key = $this->getKeyFromRow($row);
		
		$key_cond = "";
		foreach ($key as $k => $v) {
			$key_cond .= " AND {$db->quoteIdentifier($k)} = {$db->quote($v)}";
		}
		
		$tbl_name = $this->_table->info(Zend_Db_Table::NAME);
		if ($new_priority < $old_priority) {
			$q = "UPDATE {$tbl_name} SET {$this->_field} = {$this->_field} + 1 WHERE {$this->_field} < ? AND {$this->_field} >= ? $key_cond ORDER BY {$this->_field} DESC";
			$db->query($q, array($old_priority, $new_priority));
		} elseif ($new_priority > $old_priority) {
			$q = "UPDATE {$tbl_name} SET {$this->_field} = {$this->_field} - 1 WHERE {$this->_field} <= ? AND {$this->_field} > ? $key_cond ORDER BY {$this->_field} ASC";
			$db->query($q, array($new_priority, $old_priority));
		}
		
		$row->priority = $new_priority;
		
		if ($row->save()) {
			$db->commit();
		}
		
		return $row;
	}
	
	public function setNext($row) {
		$row->{$this->_field} = $this->getMax($this->getKeyFromRow($row)) + 1;
	}
	
	public function removePriority($priority, $key = array()) {
		$tbl_name = $this->_table->info(Zend_Db_Table::NAME);
		$db = $this->_table->getAdapter();
		
		$q = "UPDATE {$tbl_name} SET {$this->_field} = {$this->_field} - 1 WHERE {$this->_field} > ?";
		foreach ($key as $k => $v) {
			$q .= " AND {$db->quoteIdentifier($k)} = {$db->quote($v)}";
		}
		$db = $this->_table->getAdapter();
		
		$db->query($db->quoteInto($q, $priority));
	}
	
}