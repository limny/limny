<?php

/**
 * Limny core functions
 *
 * @package Limny
 * @author Hamid Samak <hamid@limny.org>
 * @copyright 2009-2015 Limny
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * define constant if not exists
 * @param  string  $name
 * @param  string  $value
 * @param  boolean $case_insensitive
 * @return boolean
 */
function def($name, $value, $case_insensitive = false) {
	if (defined($name) === false)
		return define($name, $value, $case_insensitive);
	
	return false;
}

/**
 * redirect page to entry URL
 * @param  string $url
 */
function redirect($url) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
	header('Location: ' . $url);
	exit;
}

/**
 * is administrator signed in
 * @return boolean
 */
function admin_signed_in() {
	if (isset($_SESSION['limny']['admin']))
		return true;

	return false;
}

/**
 * difference between current time and given time
 * @param  integer $timestamp unix timestamp
 * @return boolean/array      boolean on error and array on success
 */
function time_diff($timestamp) {
	$current_time = time();

	if ($timestamp > $current_time)
		return false;

	$diff = $current_time - $timestamp;
	$times = [
		60 => 'second',
		3600 => 'minute',
		86400 => 'hour',
		604800 => 'day',
		2419200 => 'week',
		29030400 => 'month',
		290304000 => 'year',
		2903040000 => 'decade'
	];
	
	foreach ($times as $seconds => $unit) {
		if ($diff < $seconds) {
			$result = ($seconds === 60) ? $diff : floor($diff / $prev_seconds);

			return [$result, $unit];
		}

		$prev_seconds = $seconds;
	}

	return [floor($diff / $prev_seconds * 10), $unit];
}

/**
 * load Limny library
 * @param  string  $lib_name      library file name
 * @param  boolean $return_object return result as new instance of object
 * @param  boolean $admin_lib     if true read from /admin directory otherwise read from /incs
 * @param  array   $parameters    available parameter for creating new instance
 * @return object/boolean
 */
function load_lib($lib_name, $return_object = true, $admin_lib = false, $parameters = []) {
	$lib_name = str_replace(['.', '/', '\\'], '', $lib_name);
	$lib_file = PATH . DS . ($admin_lib === true ? 'admin' : 'incs') . DS . strtolower($lib_name) . '.class.php';

	if (file_exists($lib_file)) {
		require_once $lib_file;

		if ($return_object === true) {
			$object_name = ucfirst($lib_name);

			if (class_exists($object_name) === false)
				return false;

			global $registry;
			
			$lib_object = new $object_name($registry, $parameters);

			return $lib_object;
		}

		return true;
	}

	die('Limny error: Library file <em>' . $lib_name . '</em> not found.');
}

/**
 * find string with first and last given occurrence characters
 * @param  string $string
 * @param  string $start_char
 * @param  string $end_char
 * @return string
 */
function substring($string, $start_char, $end_char) {
	$start_pos = strpos($string, $start_char);
	
	if ($start_pos !== false)
		$string = substr($string, $start_pos + strlen($start_char));

	$end_pos = strpos($string, $end_char);

	if ($end_pos !== false)
		$string = substr($string, 0, $end_pos);
	
	return $string;
}

/**
 * convert given date or timestamp to configured date format
 * @param  string/integer $date_or_timestamp
 * @param  string         $format            PHP date format
 * @return boolean/string                    return boolean on error
 */
function system_date($date_or_timestamp, $format = null) {
	global $registry;

	$calendar = $registry->config->calendar;

	if (empty($format))
		$format = $registry->config->date_format;

	$is_timestamp = is_numeric($date_or_timestamp);
	$date_in_format = $is_timestamp ? date($format, $date_or_timestamp) : $date_or_timestamp;

	if ($calendar == 'solar') {
		if ($is_timestamp)
			$date = date('Y-m-d', $date_or_timestamp);
		else if (strlen($date) > 9 && strpos($date, '-') !== false)
			$date = substr($date_or_timestamp, 0, 10);

		if (isset($date) && strlen($date) === 10) {
			$solar = load_lib('solar');
			$date = explode('-', $date);

			$date = $solar->gregorian_to_solar($date[0], $date[1], $date[2]);
			$date = implode('/', $date);

			if (strlen($date_in_format) > 10)
				$date .= substr($date_in_format, 10);

			$date_in_format = $date;
		} else
			return false;
	}

	return $date_in_format;
}

/**
 * parse current q parameter from HTTP GET
 * @param  string $q
 * @return array     return q parameter as array and current language
 */
function query($q = null) {
	if (empty($q))
		$q = isset($_GET['q']) ? $_GET['q'] : null;

	if (empty($q))
		return ['param' => []];
	
	$q = explode('/', $q);
	$q = array_filter($q);

	if (isset($q[0]) && strlen($q[0]) === 2) {
		$lang = str_replace(['.', '/', '\\'], '', $q[0]);
		$lang_file = PATH . DS . 'langs' . DS . $lang . DS . 'main.php';

		if (file_exists($lang_file)) {
			if (count($q) < 2)
				$q = [];
			else {
				unset($q[0]);
				$q = array_values($q);
			}

			return ['param' => $q, 'lang' => $lang];
		}
	}

	return ['param' => $q];
}

/**
 * create URL based on configuration and arguments
 * @param  string  $query
 * @param  boolean $full           if true URL will be with website address
 * @param  boolean $lang_if_exists use current language other than system configured language
 * @return string
 */
function url($query, $full = false, $lang_if_exists = true) {
	global $registry;

	if (is_array($query))
		$query = implode('/', $query);

	$url = '';

	if ($full === true)
		$url .= $registry->config->address;
	else
		$url .= BASE . (substr(BASE, -6) === '/admin' ? '/..' : null);

	$url .= '/';

	if ($registry->config->url_mode === 'simple')
		$url .= 'indx.php?q=';

	if ($lang_if_exists === true) {
		if (isset($registry->q) && isset($registry->q['lang']))
			$url .= $registry->q['lang'] . '/';
	}

	$url .= $query;

	return $url;
}

/**
 * generates random hashes
 * @param  integer $length hash length
 * @return string
 */
function rand_hash($length = 32) {
	$alphanumeric = [range('A', 'Z'), range('a', 'z'), range('0', '25')];
	
	$result = '';

	for ($i = 0; $i < $length; $i++) {
		$result .= $alphanumeric[mt_rand(0, 2)][mt_rand(0, 25)];

		if ($i % 2)
			$result .= $alphanumeric[mt_rand(0, 2)][mt_rand(0, 25)];
	}

	while (strlen($result) > $length)
		$result = substr($result, 0, -1);

	return $result;
}

/**
 * send mail using PHPMailer library and configured SMTP information
 * @param  string/array $to          receiver(s) (array mode: name => address)
 * @param  string       $subject     
 * @param  string       $message
 * @param  array        $attachments array of attachment files
 * @return boolean
 */
function send_mail($to, $subject, $message, $attachments = []) {
	global $registry;

	require_once PATH . DS . 'incs' . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';
	$mail = new PHPMailer;
	
	$mail->isSMTP();
	$mail->SMTPDebug = 0;
	$mail->Host = $registry->config->smtp_host;
	$mail->Port = $registry->config->smtp_port;
	$mail->SMTPSecure = $registry->config->smtp_security;
	$mail->SMTPAuth = empty($registry->config->smtp_auth) ? false : true;
	$mail->Username = $registry->config->smtp_username;
	$mail->Password = $registry->config->smtp_password;
	$mail->setFrom($registry->config->smtp_username, $registry->config->title);
	$mail->Subject = $subject;
	$mail->msgHTML($message);

	if (is_string($to))
		$mail->addAddress($to);
	else if (is_array($to))
		foreach ($to as $name => $address)
			$mail->addAddress($address, is_numeric($name) ? null : $name);

	if (is_string($attachments))
		$mail->addAttachment($attachments);
	else if (is_array($attachments))
		foreach ($attachments as $attachment)
			$mail->addAttachment($attachment);

	if ($mail->send())
		return true;
	else
		log_error(1, $mail->ErrorInfo);

	return false;
}

/**
 * load custom view from theme path or application path and use given variables
 * @param  string         $app_name  application name
 * @param  string         $view_name view file name
 * @param  array          $vars      variable name => variable value
 * @return string/boolean            boolean on error
 */
function load_view($app_name, $view_name, $vars = []) {
	if (isset($vars['vars']) || isset($vars['view_file']))
		die('Limny error: It\'s not possible to use <em>vars</em> and <em>view_file</em> names for variables');

	global $registry;

	$theme = $registry->config->theme;

	$theme_view = PATH . DS . 'themes' . DS . $theme . DS . 'views' . DS . $app_name . '-' . $view_name;
	$app_view = PATH . DS . 'apps' . DS . $app_name . DS . 'views' . DS . $view_name;

	foreach ([$theme_view, $app_view] as $view_file)
		if (file_exists($view_file)) {
			foreach ($vars as $var_name => $var_value)
				${$var_name} = $var_value;

			ob_start();
			include $view_file;
			$data = ob_get_contents();
			ob_end_clean();

			return $data;
		}

	die('Limny error: View file <em>' . $view_name . '</em> not found.');

	return false;
}

/**
 * show navigation items
 * @param  array  $items item URL => item title
 * @return string
 */
function nav($items) {
	foreach ($items as $url => $title) {
		$link = is_numeric($url) ? false : true;

		$items[$url] = '<span class="nav-item">' . ($link ? '<a href="' . url($url) . '">' : null) . $title . ($link ? '</a>' : null) . '</span>';
	}

	return implode(' &#8250; ', $items);
}

/**
 * load custom CSS file from theme path or application path
 * @param  string         $app_name application name
 * @param  string         $css_name CSS file name
 * @return string/boolean           boolean on error
 */
function load_css($app_name, $css_name) {
	global $registry;

	$theme = $registry->config->theme;

	$theme_css = 'themes' . DS . $theme . DS . 'css' . DS . $app_name . '-' . $css_name;
	$app_css = 'apps' . DS . $app_name . DS . 'css' . DS . $css_name;

	$base = BASE . (substr(BASE, -6) === '/admin' ? '/..' : null);

	foreach ([$theme_css, $app_css] as $css_file)
		if (file_exists(PATH . DS . $css_file))
			return '<link rel="stylesheet" href="' . $base . '/' . str_replace(DS, '/', $css_file) . '">' . "\n";

	return false;
}

/**
 * log errors to error log file
 * @param  integer  $no   error number
 * @param  string   $str  error message
 * @param  string   $file error file
 * @param  integer  $line
 * @return boolean
 */
function log_error($no, $str, $file = null, $line = null) {
	if (defined('ERROR_LOG') === true && ERROR_LOG === false)
		return false;

	$log_file = PATH . DS . '.limny_error';
	$types = [1 => 'Error', 2 => 'Warning', 4 => 'Parse', 8 => 'Notice', 2048 => 'Strict', 8192 => 'Deprecated'];

	$data = file_exists($log_file) ? file_get_contents($log_file) : '';
	$data = explode("\n\n", $data);
	$data = array_filter($data);
	
	if (count($data) >= 100)
		$data = array_slice($data, 1);

	$error = "[PHP ERROR]\n";
	
	if (isset($no) && isset($types[$no]))
		$error .= "TYPE = {$types[$no]}\n";

	$error .= "MESG = {$str}\n";

	if (isset($file))
		$error .= "FILE = {$file}\n";

	if (isset($line))
		$error .= "LINE = {$line}\n";

	$error .= "DATE = " . date('r') . "\n";

	$error = trim($error);

	$data[] = $error;
	$data = implode("\n\n", $data);
	file_put_contents($log_file, $data);

	if (defined('ERROR_SHOW') === true && ERROR_SHOW === true)
		echo "<pre>{$error}</pre>";

	return true;
}

/**
 * is user signed in
 * @return boolean
 */
function user_signed_in() {
	if (isset($_SESSION['limny']['user']))
		return true;

	return false;
}

/**
 * get current language
 * @return string language name in two characters
 */
function language() {
	global $registry;

	//$q = query();

	if (isset($registry->q['lang']))
		return $registry->q['lang'];
	
	return $registry->config->language;
}

?>