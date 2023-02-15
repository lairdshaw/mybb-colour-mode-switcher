<?php

// Disallow direct access to this file for security reasons.
if (!defined('IN_MYBB')) {
	die('Direct access to this file is not allowed.');
}

if (!defined('IN_ADMINCP')) {
	global $cache, $plugins_cache;

	if (empty($plugins_cache) || !is_array($plugins_cache)) {
		$plugins_cache = $cache->read('plugins');
	}
	if (!empty($plugins_cache['active']['colourmodeswitcher'])) {
		$plugins->add_hook('global_intermediate', 'cms_hookin__global_intermediate');
		$plugins->add_hook('xmlhttp'            , 'cms_hookin__xmlhttp'            );
	}
}

function colourmodeswitcher_info() {
	global $lang;

	$lang->load('colourmodeswitcher');

	return array(
		'name'          => $lang->cms_name,
		'description'   => $lang->cms_desc,
		// 'website'       => '',
		'author'        => 'Laird Shaw',
		'authorsite'    => 'https://creativeandcritical.net/',
		'version'       => '1.0.0',
		'guid'          => '',
		'codename'      => 'colourmodeswitcher',
		'compatibility' => '18*'
	);
}

function colourmodeswitcher_install() {
	global $db;

	cms_create_settings();

	if (!$db->field_exists('colourmode', 'users')) {
		$db->add_column('users', 'colourmode', "ENUM('light', 'dark', 'detect') NOT NULL DEFAULT 'detect'");
	}
}

function colourmodeswitcher_uninstall() {
	global $db;

	cms_remove_settings();

	if ($db->field_exists('colourmode', 'users')) {
		$db->drop_column('users', 'colourmode');
	}
}

function colourmodeswitcher_is_installed() {
	global $db;
	$prefix = 'colourmodeswitcher_';

	$query = $db->simple_select('settinggroups', 'COUNT(gid) AS cnt', "name = '{$prefix}settings'");

	return ($db->fetch_field($query, 'cnt') >= 1);
}

/**
 * Creates this plugin's settings. Assumes that the settings do not already
 * exist, i.e., that they have already been deleted if they were pre-existing.
 */
function cms_create_settings() {
	global $db, $lang;
	$prefix = 'colourmodeswitcher_';

	$lang->load('colourmodeswitcher');

	$query = $db->simple_select('settinggroups', 'MAX(disporder) as max_disporder');
	$disporder = intval($db->fetch_field($query, 'max_disporder')) + 1;

	// Insert the plugin's settings group into the database.
	$setting_group = array(
		'name'         => $prefix.'settings',
		'title'        => $db->escape_string($lang->cms_settings_title),
		'description'  => $db->escape_string($lang->cms_settings_desc ),
		'disporder'    => $disporder,
		'isdefault'    => 0
	);
	$db->insert_query('settinggroups', $setting_group);
	$gid = $db->insert_id();

	// Define the plugin's settings.
	$settings = array(
		'roundo_theme_tid' => array(
			'title'       => $lang->cms_setting_roundo_theme_tid_title,
			'description' => $lang->cms_setting_roundo_theme_tid_desc ,
			'optionscode' => 'numeric'                                ,
			'value'       => ''                                       ,
		),
	);

	// Insert each of this plugin's settings into the database.
	$disporder = 1;
	foreach ($settings as $name => $setting) {
		$insert_settings = array(
			'name'        => $db->escape_string($prefix.$name          ),
			'title'       => $db->escape_string($setting['title'      ]),
			'description' => $db->escape_string($setting['description']),
			'optionscode' => $db->escape_string($setting['optionscode']),
			'value'       => $db->escape_string($setting['value'      ]),
			'disporder'   => $disporder                                 ,
			'gid'         => $gid                                       ,
			'isdefault'   => 0                                          ,
		);
		$db->insert_query('settings', $insert_settings);
		$disporder++;
	}

	rebuild_settings();
}

/**
 * Removes this plugin's settings, including its settings group.
 * Accounts for the possibility that the setting group + settings were
 * accidentally created multiple times.
 */
function cms_remove_settings() {
	global $db;
	$prefix = 'colourmodeswitcher_';

	$rebuild = false;
	$query = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'");
	while ($gid = $db->fetch_field($query, 'gid')) {
		$db->delete_query('settinggroups', "gid='{$gid}'");
		$db->delete_query('settings', "gid='{$gid}'");
		$rebuild = true;
	}
	if ($rebuild) {
		rebuild_settings();
	}
}

function cms_hookin__global_intermediate() {
	global $mybb, $theme, $colourmodeswitcher_head_html, $colourmode_light_class, $colourmode_dark_class, $colourmode_detect_class;

	if ($theme['tid'] == $mybb->settings['colourmodeswitcher_roundo_theme_tid']) {
		$media_suppressed = ' media="max-width: 1px"';
		if ($mybb->user['uid'] == 0) {
			if (!empty($mybb->cookies['colourmode']) && in_array($mybb->cookies['colourmode'], ['light', 'dark', 'detect'])) {
				$mode = $mybb->cookies['colourmode'];
			} else	$mode = 'detect';
		} else	$mode = $mybb->user['colourmode'];
		$media = $mode == 'detect' ? '' : $media_suppressed;
		$ss_url = $ss_name = '';
		if (!empty($theme['stylesheets']['darkmode']['global'][0])) {
			$ss_name = $theme['stylesheets']['darkmode']['global'][0];
			if (strpos($ss_name, 'css.php') !== false) {
				$ss_url = $mybb->settings['bburl'].'/'.$ss_name;
			} else {
				$ss_url = $mybb->get_asset_url($ss_name);
				if (file_exists(MYBB_ROOT.$ss_name)) {
					$ss_url .= '?t='.filemtime(MYBB_ROOT.$ss_name);
				}
			}
		}
		$colourmode_light_class = $colourmode_dark_class = $colourmode_detect_class = '';
		$varname = "colourmode_{$mode}_class";
		$$varname = ' class="active_colourmode_icon"';
		$darkmode_import = !empty($ss_url) ? '@import url("'.$ss_url.'") (prefers-color-scheme: dark) or (prefers-dark-interface);' : '';
		$darkmode_ss_link = $mode == 'dark' ? '<link rel="stylesheet" href="'.$ss_url.'" id="colourmodeswitcher_style_element_dark">' : '';
		$ss_editor_url = $mybb->settings['bburl'].'/jscripts/sceditor/styles/roundo-darko-overrides.css';
		$colourmodeswitcher_head_html = <<<EOF
<script>
var dark_ss_url = '{$ss_url}';
var dark_editor_ss_url = '{$ss_editor_url}';
var colourmode = '$mode';
var mybb_uid    = '{$mybb->user['uid']}';
</script>
<style id="colourmodeswitcher_style_element_detect"{$media}>
{$darkmode_import} 
</style>
{$darkmode_ss_link}
EOF;
	}
}

function cms_hookin__xmlhttp() {
	global $mybb, $db;

	if ($mybb->input['action'] === 'setcolourmode') {
		$new_mode = $mybb->get_input('colourmode');
		if (in_array($new_mode, ['light', 'dark', 'detect'])) {
			$db->update_query('users', ['colourmode' => $new_mode], "uid='{$mybb->user['uid']}'");
		}
		exit;
	}
}
