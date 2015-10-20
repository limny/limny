<?php

$admin->title = CONFIGURATION;

$form = load_lib('form');
$application = load_lib('application', true, true);

$security_config_update = defined('CONFIG_UPDATE') === false || CONFIG_UPDATE === true;

$applications = [];

foreach ($application->apps(true, 'rich') as $name => $app)
	$applications[$name] = $app['name'];

foreach ($admin->languages() as $code => $name)
	$languages[$code] = $name . ' (' . $code . ')';

foreach (timezone_identifiers_list() as $name)
	$timezones[$name] = $name;

$divider = '<hr>';

$form->form_options = [
	'title' => ['label' => TITLE, 'type' => 'text'],
	'motto' => ['label' => MOTTO, 'type' => 'text'],
	'description' => ['label' => DESCRIPTION, 'type' => 'text'],
	$divider,
	'address' => ['label' => ADDRESS, 'type' => 'text'],
	'header' => ['label' => HEADER, 'type' => 'text'],
	'footer' => ['label' => FOOTER, 'type' => 'textarea'],
	'url_mode' => ['label' => URL_MODE, 'type' => 'radio', 'items' => ['simple' => SIMPLE . '(/index.php?q=app/page)', 'standard' => STANDARD . ' (/app/page)']],
	$divider,
	'default_content' => ['label' => DEFAULT_CONTENT, 'type' => 'combo', 'items' => ['app' => APPLICATION, 'query' => QUERY, 'text' => TEXT]],
	'default_app' => ['label' => APPLICATION, 'type' => 'combo', 'items' => $applications],
	'default_query' => ['label' => QUERY, 'type' => 'text'],
	'default_text' => ['label' => TEXT, 'type' => 'textarea'],
	$divider,
	'theme' => ['label' => THEME, 'type' => 'combo', 'items' => $admin->themes()],
	'language' => ['label' => LANGUAGE, 'type' => 'combo', 'items' => $languages],
	'cache_lifetime' => ['label' => CACHE_LIFETIME, 'type' => 'number'],
	$divider,
	'calendar' => ['label' => CALENDAR, 'type' => 'radio', 'items' => ['gregorian' => GREGORIAN, 'solar' => SOLAR_HIJRI]],
	'date_format' => ['label' => DATE_FORMAT, 'type' => 'text'],
	'timezone' => ['label' => TIME_ZONE, 'type' => 'combo', 'items' => $timezones],
	$divider,
	'user_registration' => ['label' => USER_REGISTRATION, 'type' => 'radio', 'items' => ['1' => ENABLE, '0' => DISABLE]],
	'email_confirmation' => ['label' => EMAIL_CONFIRMATION, 'type' => 'radio', 'items' => ['1' => ENABLE, '0' => DISABLE]],
	$divider,
	'smtp_host' => ['label' => SMTP_HOST, 'type' => 'text'],
	'smtp_port' => ['label' => SMTP_PORT, 'type' => 'number'],
	'smtp_security' => ['label' => SMTP_SECURITY, 'type' => 'radio', 'items' => ['ssl' => 'SSL', 'tls' => 'TLS']],
	'smtp_auth' => ['label' => SMTP_AUTHENTICATION, 'type' => 'radio', 'items' => ['1' => YES, '0' => NO]],
	'smtp_username' => ['label' => SMTP_USERNAME, 'type' => 'text'],
	'smtp_password' => ['label' => SMTP_PASSWORD, 'type' => 'password']
];

if (isset($_POST['update']) && $security_config_update) {
	global $db;

	$fields = array_keys($form->form_options);

	foreach ($fields as $field)
		if (is_string($field) && isset($_POST[$field])) {
			if ($field === 'timezone' && in_array($_POST['timezone'], $timezones) === false)
				continue;
			else if ($field === 'smtp_password' && empty($_POST['smtp_password']))
				continue;
			
			$case_statement[] = 'WHEN ' . $db->quote($field) . ' THEN :' . $field;
			$values_array[':' . $field] = $_POST[$field];
		}

	if (isset($case_statement) && isset($values_array)) {
		$db->prepare('UPDATE ' . DB_PRFX . 'config SET value = CASE name ' . implode(' ', $case_statement) . ' ELSE value END')->execute($values_array);

		redirect(BASE . '/' . ADMIN_DIR . '/config/updated');
	}
}

$config_values = (array) $config->config;
$form->form_values = $config_values;

$form_items = $form->fields();

if (count($form_items) > 0)
	foreach ($form_items as $label => $element) {
		if ($label === CACHE_LIFETIME)
			$help = SENTENCE_4;
		else if ($label === TIME_ZONE)
			$help = CURRENT_DATE_TIME . ': ' . system_date(time(), 'r');
		else if ($label === DATE_FORMAT)
			$help = SENTENCE_30;
		else
			$help = '';

		$data[] = '<div class="form-group">
	<label class="col-sm-2 control-label">' . $label . '</label>
	<div class="col-sm-6">
		' . $element . '
		' . (empty($help) ? null : '<span class="help-block">' . $help . '</span>') . '
	</div>
</div>';
	}

$admin->head = '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/js/config.js"></script>';

$admin->content = '<form class="form-horizontal" role="form" method="post" action="' . BASE . '/' . ADMIN_DIR . '/config">
' . (isset($admin->q[1]) && $admin->q[1] === 'updated' ? '<div class="form-group">
	<div class="col-sm-offset-1 col-sm-7">
		<p class="message bg-info">' . SENTENCE_5 . '</p>
	</div>
</div>' : null) .
implode("\n", $data) . '
<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		' . ($security_config_update ? '<button name="update" type="submit" class="btn btn-primary">' . UPDATE . '</button>' : '<span class="text-red">' . SENTENCE_35 . '</span>') . '
	</div>
</div>
</form>';

?>