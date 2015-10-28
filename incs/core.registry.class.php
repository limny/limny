<?php

/**
 * registry for storing values
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CoreRegistry {
	// variables to store
	private $vars = [];

	/**
	 * set property
	 * @param string $name
	 * @param *      $value
	 * @return void
	 */
	public function __set($name, $value) {
		$this->vars[$name] = $value;
	}

	/**
	 * get property value
	 * @param  string $name
	 * @return *
	 */
	public function __get($name) {
		return $this->vars[$name];
	}
}

?>