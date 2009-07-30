<?php

class Tuuti_Controller_Helper_Messenger extends Zend_Controller_Action_Helper_Abstract {

	public $controller;
	
	private $_this_page_messages = array(
		'status' => array(),
		'error' => array()
	);
	
	private $_next_page_messages = array(
		'status' => array(),
		'error' => array()
	);
	
	public function setActionController($controller) {
		$this->controller = $controller;
	}
	
	public function getName() {
		return "Messenger";
	}
	
	public function direct($message, $type = "status", $page = "next") {
		return $this->addMessage($message, $type, $page);
	}
	
	public function addMessage($message, $type = "status", $page = "next") {
		if (!in_array($type, array('status', 'error'))) {
			throw new Exception("Wrong message type, it should be 'status' or 'error'");
		}
		
		if (!in_array($page, array('this', 'next'))) {
			throw new Exception("Wrong page identifier, it should be 'next' or 'this'");
		}
		
		$this->{"_" . $page . "_page_messages"}[$type][] = $message;
		
		return $this;
	}
	
	public function thisStatus($message) {
		return $this->addMessage($message, 'status', 'this');
	}
	
	public function nextStatus($message) {
		return $this->addMessage($message, 'status', 'next');
	}
	
	public function thisError($message) {
		return $this->addMessage($message, 'error', 'this');
	}
	
	public function nextError($message) {
		return $this->addMessage($message, 'error', 'next');
	}
	
	public function postDispatch() {
	}
	
}