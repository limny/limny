<?php

/**
 * Administration themes methods
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Theme {
	// themes directory
	public $themes_path;

	/**
	 * set themes directory property
	 * @return void
	 */
	public function __construct() {
		$this->themes_path = PATH . DS . 'themes';
	}

	/**
	 * get list of themes
	 * @return array
	 */
	public function themes() {
		$themes = [];

		foreach (scandir($this->themes_path) as $file)
			if (substr($file, 0, 1) !== '.' && file_exists($this->themes_path . DS . $file . DS . 'theme.ini'))
				$themes[] = $file;

		return $themes;
	}

	/**
	 * get theme information
	 * @param  string        $theme_name
	 * @return array/boolean
	 */
	public function theme_info($theme_name) {
		$info_file = $this->themes_path . DS . $theme_name . DS . 'theme.ini';

		if (file_exists($info_file))
			return parse_ini_file($info_file);

		return false;
	}

	/**
	 * get theme editable files
	 * @param  string        $theme_name
	 * @param  string        $path
	 * @return array/boolean
	 */
	public function theme_files($theme_name, $path = null, $recursive = false) {
		if (empty($path))
			$path = $this->themes_path . DS . $theme_name;

		$files = [];

		foreach (scandir($path) as $file)
			if (in_array($file, ['.', '..']) === false)
				if ($recursive === true && is_dir($path . DS . $file))
					$files = array_merge($files, $this->theme_files($theme_name, $path . DS . $file, true));
				else if (in_array(strtolower(substr($file, strrpos($file, '.') + 1)), ['php', 'tpl', 'htm', 'html', 'css', 'js']))
					$files[] = $recursive === true ? substr($path . DS . $file, strlen($this->themes_path . DS . $theme_name) + 1) : $file;

		if (count($files) > 0) {
			asort($files);

			return $files;
		}

		return false;
	}
}

?>