<?php

$application = load_lib('application', true, true);

$security_app_install = defined('APPLICATION_INSTALL') === false || APPLICATION_INSTALL === true;
$security_app_enabled = defined('APPLICATION_ENABLED') === false || APPLICATION_ENABLED === true;
$security_app_uninstall = defined('APPLICATION_UNINSTALL') === false || APPLICATION_UNINSTALL === true;

$admin->title = APPLICATIONS;

$all_apps = $application->all_apps();
$installed_apps = $application->apps();
$uninstalled_apps = array_diff($all_apps, array_keys($installed_apps));
ksort($installed_apps);
asort($uninstalled_apps);

if (isset($_POST['action']))
	switch ($_POST['action']) {
		case 'app-enabled':
			if ($security_app_enabled)
				die($application->update_enabled(@$_POST['id'], @$_POST['enabled']));
			break;

		case 'app-uninstall':
			if ($security_app_uninstall)
				die($application->uninstall_app(@$_POST['id']));
			break;

		case 'app-install':
			if ($security_app_install)
				die($application->install_app(@$_POST['name']));
			break;
	}

if (count($installed_apps) > 0 || count($uninstalled_apps) > 0) {
	$admin->content = '<br>';

	if (count($installed_apps) > 0) {
		$admin->content .= '<h3>' . INSTALLED . '</h3>
<table class="table table-hover apps">
	<tbody>';

		foreach ($installed_apps as $name => $app) {
			$data = parse_ini_file($application->apps_path . DS . $name . DS . 'app.ini');

			$admin->content .= '<tr class="active">
		<td class="col-md-1 text-center"><input type="checkbox" value="1" data-id="' . $app['id'] . '" class="enabled" ' . ($app['enabled'] == '1' ? 'checked' : null) . ' ' . ($security_app_enabled ? null : 'disabled') . '></td>
		<td class="col-md-2">' . @$data['name'] . ' ' . @$data['version'] . '</td>
		<td class="col-md-2 text-gray">(' . @$data['creator'] . ')</td>
		<td class="text-gray">' . @$data['description'] . '</td>
		<td class="col-md-3 manage-buttons">';

			if (empty($app['required_by']))
				$admin->content .= $security_app_uninstall ? '<span class="text-red" style="display:none">' . SENTENCE_7 . '</span> <button class="btn btn-danger btn-xs btn-visible-hover uninstall">' . UNINSTALL . '</button> <button class="btn btn-danger btn-xs uninstall-confirm" style="display:none" data-id="' . $app['id'] . '">' . YES . '</button> <button class="btn btn-info btn-xs uninstall-cancel" style="display:none">' . NO . '</button>' : '<span class="text-red">' . SENTENCE_35 . '</span>';
			else
				$admin->content .= '<strong>' . REQUIRED_BY . ':</strong> ' . $app['required_by'];

			$admin->content .= '</td>
	</tr>';
		}

		$admin->content .= '</tbody></table>';
	}

	if (count($uninstalled_apps) > 0) {
		$core_version = $admin->config->version;
		
		$admin->content .= '<h3>' . UNINSTALLED . '</h3>
<table class="table apps">
	<tbody>';

		foreach ($uninstalled_apps as $name) {
			if ($security_app_install)
				$install = '<button class="btn btn-success btn-xs btn-visible-hover install" data-name="' . $name . '">' . INSTALL . '</button>';
			else
				$install = '<span class="text-red">' . SENTENCE_35 . '</span>';

			if ($data = $application->app_info($name)) {
				if (isset($data['compatibility']) && empty($data['compatibility']) === false && $admin->is_compatible($data['compatibility'], $core_version) === false) {
					$install = '<strong class="text-red">' . NOT_COMPATIBLE . '</strong>';
				} else if (isset($data['dependson']) && empty($data['dependson']) === false) {
					$depends_on = explode(',', $data['dependson']);

					foreach ($depends_on as $key => $app) {
						if (($space_pos = strpos($app, ' ')) !== false) {
							$app_name = substr($app, 0, $space_pos);
							$app_version = substr($app, $space_pos + 1);
						} else {
							$app_name = $app;
							$app_version = null;
						}

						$app_name = trim($app_name);

						$depends_on[$app_name] = $app_version;						

						if (in_array($app_name, array_keys($installed_apps)) === false)
							$uninstalled_dependencies[] = $app_name;
						else if (empty($app_version) === false) {
							if ($dependent_data = $application->app_info($app_name))
								if (isset($dependent_data['version']) && $admin->is_compatible($app_version, $dependent_data['version']) === false)
									$uninstalled_dependencies[] = $app_name;
						}

						unset($depends_on[$key]);
					}

					if (isset($uninstalled_dependencies) && count($uninstalled_dependencies) > 0) {
						$install = '<strong>' . DEPENDS_ON . ':</strong> ';

						foreach ($depends_on as $app_name => $version)
							$install .= ucfirst($app_name) . ' ' . $version . ', ';

						$install = substr($install, 0, -2);
					}
				}
			}

			$admin->content .= '<tr class="warning">
		<td class="col-md-1 text-center">&nbsp;</td>
		<td class="col-md-2">' . @$data['name'] . ' ' . @$data['version'] . '</td>
		<td class="col-md-2 text-gray">(' . @$data['creator'] . ')</td>
		<td class="text-gray">' . @$data['description'] . '</td>
		<td class="col-md-3 manage-buttons text-gray">' . $install . '</td>
	</tr>';
		}

		$admin->content .= '</tbody></table>';
	}

	$admin->head = '<script type="text/javascript" src="' . BASE . '/' . ADMIN_DIR . '/misc/js/apps.js"></script>';
} else
	$admin->content = SENTENCE_6;

?>