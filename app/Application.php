<?php

class Application {

	/**
	 * Title of the application
	 *
	 * @var string
	 */
	private $_title = "Application";

	/**
	 * Reference to the config file
	 *
	 * @var Zend_Config
	 */
	private $_config;

	/**
	 * Reference to the locale choosen by the visitor
	 *
	 * @var Zend_Locale
	 */
	private $_locale;

	/**
	 * List of language supported by the website
	 *
	 * @var array
	 */
	private $_language_list_supported = array('en');

	/**
	 * Reference to the database adapter
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	private $_db;

	/**
	 * Reference the authentification adapter
	 *
	 * Used by Zend_Auth to authenticates users with the database
	 *
	 * @var Zend_Auth_Adapter_Interface
	 */
	private $_auth_adapter;

	/**
	 * Session object for the application namespace
	 *
	 * @var Zend_Session_Namespace
	 */
	private $_application_session;

	/**
	 * Instance of Application
	 *
	 * @var Application
	 */
	private static $_instance = null;

	private function __construct($config_file, $section) {
		$this->_systemInit()
		     ->_frameworkInit()
		     ->_readConfig($config_file, $section)
		     ->_sessionInit()
		     ->_databaseInit()
		     ->_localeInit()
			 ->_viewInit()
			 ->_authentificationInit();
	}

	/**
	 * Initialize the system
	 *
	 * @return Application
	 */
	private function _systemInit() {
		error_reporting(E_ALL);
		ini_set('display_startup_errors', 1);
		ini_set('display_errors', 1);
		ini_set('xdebug.var_display_max_depth', 7);

		set_include_path('../lib' . PATH_SEPARATOR . '../app' . PATH_SEPARATOR . get_include_path());

		return $this;
	}

	/**
	 * Initialize ZendFramework
	 *
	 * @return Application
	 */
	private function _frameworkInit() {
		require_once "Zend/Loader/Autoloader.php";
		Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

		if (extension_loaded('xdebug')) {
			Zend_Debug::setSapi('cli');
		}

		return $this;
	}

	/**
	 * Read the configuration ini file
	 *
	 * @param string $config_file
	 * @param string $section
	 * @return Application
	 */
	private function _readConfig($config_file, $section) {
		$this->_config = new Zend_Config_Ini($config_file, $section);

		if (isset($this->_config->application)) {
			$app_config = $this->_config->application;
			if (isset($app_config->title)) {
				$this->_title = (string) $this->_config->application->title;
			}
		}

		return $this;
	}

	/**
	 * Initialize session
	 *
	 * @return Application
	 */
	private function _sessionInit() {
		Zend_Session::start();
		if (isset($this->_config->session)) {
			Zend_Session::setOptions($this->_config->session->toArray());
		}

		$this->_application_session = new Zend_Session_Namespace('application');

		return $this;
	}

	/**
	 * Initialize database if there is a configuration
	 *
	 * @return Application
	 */
	private function _databaseInit() {
		if (isset($this->_config->database)) {
			$db = Zend_Db::factory($this->_config->database);
			Zend_Db_Table::setDefaultAdapter($db);
			
			$db->query('SET NAMES utf8');

			$this->_db = $db;
		}

		return $this;
	}
	
	private function _viewInit() {
		Zend_Layout::startMvc();
		
		$view = new Zend_View();
		$view->addHelperPath(realpath(dirname(__FILE__) . '/views/helpers'), 'Zend_View_Helper');

		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view);
		
		return $this;
	}

	/**
	 * Initialize the localisation
	 *
	 * @return Application
	 */
	private function _localeInit() {
		list($lang_list, $default_lang) = $this->_findLanguage();

		$user_lang = null;
		if (isset($this->_application_session->lang)) {
			$user_lang = $this->_application_session->lang;
		}

		if (!in_array($user_lang, $lang_list)) {
			try {
				$locale = new Zend_Locale(Zend_Locale::BROWSER);
			} catch (Exception $e) {
				$locale = new Zend_Locale($default_lang);
			}
		} else {
			$locale = new Zend_Locale($user_lang);
		}

		$this->_locale = $locale;

		return $this;
	}

	/**
	 * Set the locale used for the website
	 *
	 * @param Zend_Locale $locale
	 */
	private function _setLocale(Zend_Locale $locale) {
		$this->_locale = $locale;
	}

	/**
	 * Get the locale choosen by the visitor
	 *
	 * @return Zend_Locale
	 */
	public function getLocale() {
		return $this->_locale;
	}

	/**
	 * Set the language
	 *
	 * @param string $lang
	 *
	 * @return Zend_Locale
	 */
	public function setLanguage($lang, $persist = true) {
		if (in_array($lang, $this->_language_list_supported)) {
			if ($persist) {
				$this->_application_session->lang = $lang;
			}

			return $this->_setLocale(new Zend_Locale($lang));
		}

		throw new InvalidArgumentException("The language isn't supported");
	}

	/**
	 * Find the languages of the websites
	 *
	 * Depending on the configuration, the method find the list of languages
	 * available in the websites.
	 *
	 * @return array
	 */
	private function _findLanguage() {
		$lang_list = array('en');
		$default_lang = 'en';

		if (isset($this->_config->locale)) {
			$config = $this->_config->locale;
			if (isset($config->mode)) {
				if ($config->mode == 'config') {
					$lang_list = split(',', $config->langlist);
					$lang_list = array_map('trim', $lang_list);
					$default_lang = $lang_list[0];
				} elseif ($config->mode == 'database') {
					if (!isset($this->_db)) {
						throw new Exception('Unable ton configure the locale, no database configuration was specified in the config file');
					}

					$table_name = 'LANGUAGE';
					$abbrev_field = 'abbrev';
					$priority_field = 'priority';

					if (isset($config->database)) {
						$db_config = $config->database;

						$table_name = $db_config->get('table', $table_name);
						$abbrev_field = $db_config->get('abbrevfield', $abbrev_field);
						$priority_field = $db_config->get('priorityfield', $priority_field);
					}

					$db_lang_list = $this->_db->select()->from($table_name, $abbrev_field)->order($priority_field)->query()->fetchAll();
					$default_lang = null;
					$lang_list = array();
					foreach ($db_lang_list as $db_lang) {
						if (is_null($default_lang)) {
							$default_lang = $db_lang[$abbrev_field];
						}
						$lang_list[] = $db_lang[$abbrev_field];
					}
				}
			}
		}

		$this->_language_list_supported = $lang_list;

		return array($lang_list, $default_lang);
	}

	/**
	 * Get the list of language supported by the website
	 *
	 * @return array
	 */
	public function getLanguageListSupported() {
		return $this->_language_list_supported;
	}

	/**
	 * Initialize the authentification adapter
	 *
	 * @return Application
	 */
	public function _authentificationInit() {
		$auth_config = new stdClass();
		$auth_config->table_name = "USER";
		$auth_config->identity_column = "name";
		$auth_config->credential_column = "password";
		$auth_config->credential_treatment = "MD5(?)";
		
		if (!is_null($this->_config->authentification)) {
			$auth_config = $this->_config->authentification;
		}

		if (!isset($this->_db)) {
			throw new Exception('Unable ton configure the authentification, no database configuration was specified in the config file');
		}

		$table_name = $auth_config->table_name;
		$identity_column = $auth_config->identity_column;
		$credential_column = $auth_config->credential_column;
		$credential_treatment = $auth_config->credential_treatment;

		$this->_auth_adapter = new Zend_Auth_Adapter_DbTable($this->_db, $table_name, $identity_column, $credential_column, $credential_treatment);

		return $this;
	}

	/**
	 * Return the authentification adapter
	 *
	 * @return Zend_Auth_Adapter_Interface
	 */
	public function getAuthAdapter() {
		return $this->_auth_adapter;
	}

	/**
	 * Get the debug status setted in the config file
	 *
	 * @return bool
	 */
	public function getDebugStatus() {
		return isset($this->_config->debug) && $this->_config->debug == 1;
	}

	/**
	 * Get the title of the application
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->_title;
	}

	/**
	 * Dispatch the request
	 *
	 * @return Application
	 */
	public function dispatch() {
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->throwExceptions($this->getDebugStatus());
		$frontController->addControllerDirectory('../app/controllers', 'default');
		/**
		 * Checking modules directory configuration
		 */
		if (isset($this->_config->application)) {
			$app_conf = $this->_config->application;
			if (isset($app_conf->modules_dir)) {
				if (substr($app_conf->modules_dir, 0, 1)) {
					$modules_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $app_conf->modules_dir;
				} else {
					$modules_dir = $app_conf->modules_dir;
				}

				if (is_readable($modules_dir)) {
					$frontController->addModuleDirectory($modules_dir);
				} else {
					throw new Exception('Configuration file specified a module directory but it isn\'t readable');
				}
			}
		}
		$frontController->dispatch();

		return $this;
	}

	/**
	 * Initialize the application
	 *
	 * @param string $config_file
	 * @param string $section
	 * @return Application
	 */
	public static function init($config_file = '../config.ini', $section = 'www') {
		if (!is_null(self::$_instance)) {
			throw new Exception('You can\'t initialize the application more than one time');
		}

		self::$_instance = new Application($config_file, $section);

		return self::getInstance();
	}

	/**
	 * Initialize and dispatch the application
	 *
	 * @param string $config_file
	 * @param string $section
	 * @return Application
	 */
	public static function run($config_file = '../config.ini', $section = 'www') {
		return self::init($config_file, $section)->dispatch();
	}

	/**
	 * Get the instance for the singleton
	 *
	 * @return Application
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::init();
		}

		return self::$_instance;
	}

}