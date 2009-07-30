<?php

class Tuuti_Form_Element_TinyMCE extends Zend_Form_Element_Textarea {
	
	public $jsScriptPath = 'jscripts/tiny_mce/tiny_mce.js';
	
	protected static $_script_appended = false;
	
	public function setJsScriptPath($path) {
		$this->jsScriptPath = $path;
	}
	
	public function getJsScriptPath() {
		return $this->jsScriptPath;
	}
	
	public function render($content = null) {
		$this->_appendScript();
		
		$attrib = $this->class;
		if (!empty($attrib)) {
			$attrib .= ' ';
		}
		$attrib .= 'mce';
		$this->class = $attrib;
		
		return parent::render($content);
	}
	
	private function _appendScript() {
		if (!self::$_script_appended) {
			$script = $this->getJsScriptPath();
			
			$view = $this->getView();
			$view->headScript()->appendFile($view->staticUrl($script));
			$script = 'tinyMCE.init({
	mode : "specific_textareas",
	editor_selector : "mce",
	theme : "advanced",
	skin : "o2k7",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	plugins: "fullscreen"
});
';
			$view->headScript()->appendScript($script);
			
			self::$_script_appended = true;
		}
	}
	
}