<?php

require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Application.php');
$app = Application::init('../config.ini', 'tuuti');

/* Set the singleton instance of the front controller */
$frontController = Zend_Controller_Front::getInstance();

/******************************************************************************/
/** Getting default values                                                   **/
/******************************************************************************/
$tbl_section = new Tuuti_Section();
$section = $tbl_section->getDefaultSection();

$default_section = '';
if (!is_null($section)) {
	$default_section = $section->pretty_url_title;
}

/******************************************************************************/
/** Setting of the router                                                    **/
/******************************************************************************/
$pretty_url_regex = Tuuti_Section::PRETTY_URL_REGEX;

$router = $frontController->getRouter();

$router->addRoute(
	'cms',
	new Zend_Controller_Router_Route(
		':section',
		array(
			'controller' 	=> 'index',
			'action' 		=> 'index',
			'module' 		=> 'default',
			'section' 		=> $default_section
		),
		array(
			'section' => $pretty_url_regex
		)
	)
);

$router->addRoute(
	'language',
	new Zend_Controller_Router_Route(
		':lang/:section',
		array(
			'controller' => 'index',
			'action' => 'index',
			'module' => 'default',
			'section' => $default_section
		),
		array(
			'lang' => join('|', $app->getLanguageListSupported()),
			'section' => $pretty_url_regex
		)
	)
);

/******************************************************************************/
/** Plugins to catch params and do some operations                           **/
/******************************************************************************/

$frontController->registerPlugin(new Tuuti_Controller_Plugin_Language());
$frontController->registerPlugin(new Tuuti_Controller_Plugin_Auth());

/******************************************************************************/

$app->dispatch();