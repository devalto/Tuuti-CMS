<?php
/**
 * ADAFramework
 *
 * @author Sylvain Filteau <sfilteau@ada-consult.com>
 * @version 1.0
 * @package ADA_View
 */

/**
 * Helper qui crée des urls
 *
 * @package ADA_View
 */
class Zend_View_Helper_StaticUrl {

	public function staticUrl($url) {
		if ($url[0] != '/') {
			$base_url = Zend_Controller_Front::getInstance()->getBaseUrl();
			if (strlen($base_url) == 0 || $base_url[strlen($base_url) - 1] != '/')
				$base_url .= '/';
			return $base_url . $url;
		} else {
			return $url;
		}
	}

}
