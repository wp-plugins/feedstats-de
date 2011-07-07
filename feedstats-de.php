<?php
/**
 * @package FeedStats
 * @author Frank B&uuml;ltge
 */
 
/*
Plugin Name: FeedStats
Plugin URI: http://bueltge.de/wp-feedstats-de-plugin/171/
Description: Simple statistictool for feeds.
Version: 3.7.3
Author: Andres Nieto Porras, Frank B&uuml;ltge
Author URI: http://bueltge.de/
Last Change: 07/07/2011
*/

define('FEEDSTATS_DAY', 60*60*24);

/**
 * ------------------------------------------------------
 *  ACKNOWLEDGEMENTS
 * ------------------------------------------------------
 * Idea and first release by Andres Nieto Porras
 * - http://www.anieto2k.com/
 * 
 * Thx to Thomas R. Koll - http://blog.tomk32.de
 * for many improvements for a better code and performance
 * 
 * Thx to Frank Bueltge - http://bueltge.de
 * Statistic, multilingualism and improvements
 * 
 * Thx to Neil - http://wmfield.idv.tw
 * for traditional Chinese (zh_TW) translation
 * 
 * Thx to burningHat - http://blog.burninghat.net
 * for french (fr_FR) translation
 * 
 * Thx to Baris Unver - http://beyn.org
 * for turkish (tr_TR) translation
 * 
 * FeedReaderButton (gif) by http://www.nasendackel.de
 * FeedReaderButton (gif - traditional Chinese (zh_TW))
 *  by http://www.wmfield.idv.tw/485
 * ------------------------------------------------------
 */

/**
 * ------------------------------------------------------
 *  FEED-READER BUTTON
 * ------------------------------------------------------
 * Function for button with reader:
 * ------------------------------------------------------
 * feedstats_getfeeds_button()
 * 
 * Example:
 * ------------------------------------------------------
 * <div id="feeds_button"><?php feedstats_getfeeds_button(); ?></div>
 * 
 * 
 * Example for style-css:
 * ------------------------------------------------------
 * #feeds_button {
 * 	width: 74px;
 * 	height: 14px;
 * 	text-align: left;
 * 	font-size: 10px;
 * 	padding: 1px 15px 15px 3px;
 * 	color: #fff;
 * 	background: url(wp-content/plugins/feedstats-de/images/feedstats-de.gif) no-repeat;
 * 	margin-bottom: 2px;
 * }
 * 
 * Example for style-css in traditional Chinese (zh_TW) translation:
 * ------------------------------------------------------
 * 	background: url(wp-content/plugins/feedstats-de/images/feedstats-de-zh_TW.gif) no-repeat;
 */

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );


/**
 * load language file
 *
 */
function feedstats_textdomain() {

	if ( function_exists('load_plugin_textdomain') ) {
		if ( !defined('WP_PLUGIN_DIR') ) {
			load_plugin_textdomain('feedstats', str_replace( ABSPATH, '', dirname(__FILE__) ) . '/languages');
		} else {
			load_plugin_textdomain('feedstats', false, dirname( plugin_basename(__FILE__) ) . '/languages');
		}
	}
}


/**
 * Add action link(s) to plugins page
 * Thanks Dion Hulse -- http://dd32.id.au/wordpress-plugins/?configure-link
 */
function feedstats_filter_plugin_actions($links, $file){
	static $this_plugin;

	if ( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="options-general.php?page=feedstats-de-settings.php">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links); // before other links
	}
	return $links;
}


/**
 * @version WP 2.7
 * Add action link(s) to plugins page
 *
 * @package Secure WordPress
 *
 * @param $links
 * @return $links
 */
function feedstats_filter_plugin_actions_new($links) {

	$settings_link = '<a href="options-general.php?page=feedstats-de-settings.php">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
	
	return $links;
}


/**
 * Images/ Icons in base64-encoding
 * @use function feedstats_get_resource_url() for display
 */
if( isset($_GET['resource']) && !empty($_GET['resource'])) {
	# base64 encoding performed by base64img.php from http://php.holtsmark.no
	$resources = array(
		'feedstats.gif' => 
		'R0lGODlhCwALAMQAANjY2Ojo6Ht7e97e3oGBgfr6+p2dnY6Ojv'.
		'X19a2traWlpXR0dKKiouLi4sfHx5aWlpqamoqKioeHh6GhoZ6e'.
		'npubm/z8/JWVldzc3JKSko+Pj4yMjImJiZSUlJiYmP///yH5BA'.
		'AAAAAALAAAAAALAAsAAAVpIDZ5kDFNDKMME5QkHXdkmqdA0/cV'.
		'zrVpG4NBQ0kAPgGIROKpVBqOSsISiBAuBg+mYFEkPo4F8RC5DA'.
		'oaAEJwoHQagAyP8slIKBdEgAAAcCwQBAoZGRsCHBILEgIdGAwP'.
		'Dx0HHBESGRghADs='.
		'',
		'feedstats32.gif' => 
		'R0lGODlhIAAgAMQfAIGBgcrKyuLi4qurq9XV1bq6usLCwpWVlX'.
		'x8fHh4eHR0dHBwcKSkpPX19ZKSkv39/aCgoJ2dnezs7LOzs4SE'.
		'hImJiYeHh5eXl5GRkYyMjI2NjY+Pj46OjpCQkP///////yH5BA'.
		'EAAB8ALAAAAAAgACAAAAX/4CcSl3VEUISqaKpCMHxYFyGKD+RM'.
		'DBtdvmAMwmAMChfGQ2SEYDIaqEbD0Ww4mw5G6+h2D44k7yNIZs'.
		'5SKgeL7bi5XgfGcegJGJNO9AK7rNlZb3NeWlsdBzx1ZxoBHo4P'.
		'AgEDhm5bXlsbV5kDBwcQUo2Ooh4NARGCGIZXVVEQB3xooaOiDw'.
		'EXlR2ZrGgQF7BQsrOiDQO5uVVoFRURr58Zax1EBQQPwg8FulEZ'.
		'yhYWzHxTalduiBLCAXpn3BQUF83hVVmBWgUNswHqFRbsAO7gax'.
		'MEEJDkIBCGCwJmTVhHAQCATv84FBj1gAADQQQoQtjX0CEdPms2'.
		'TDyH4YqGjKIk/1hw6BABhlcMQo4UJuBAFA4JRU1giQDBoSRYNk'.
		'AoECDnKAkHtjmw56iBhZ4IEnCAmSvTloPBPBDQoG+mhwk9EyTQ'.
		'ECamsTe5IDB1VGBfBXOOJEQVW6FsrqtVq1xY20BDwwGjOiRQoK'.
		'BuEkNbIkyIMCUDhGqODDi0sHYA4cJ2tWyQhe+MrAYNEaDUSniB'.
		'YQZyos1ioMzBqAs9ATsSUNpCZpGzCuizYBRsggO0FCxYQMHupl'.
		'kDulEwIMpAVAqjEgwvnuQYh9ECOAKYICqAWASjEEynU6zDGg0D'.
		'JFXgByCCgfcMBicoQL+AcNMYGBQ4sCaf9pZzDXbZcAQuEAEDAh'.
		'y4QUkUDAEYYGn3FQiAAwJ8gAcEHSizXkcAhCXWgAUOB0AROPww'.
		'QQpgYIBMgy11iAAA3jgQwRI3EHAABSewAIQvPPoC0Ssd4GiDCC'.
		'EAADs='.
		'');
	
	if(array_key_exists($_GET['resource'], $resources)) {

		$content = base64_decode($resources[ $_GET['resource'] ]);

		$lastMod = filemtime(__FILE__);
		$client = ( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false );
		// Checking if the client is validating his cache and if it is current.
		if (isset($client) && (strtotime($client) == $lastMod)) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 304);
			exit;
		} else {
			// Image not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $lastMod).' GMT', true, 200);
			header('Content-Length: '.strlen($content));
			header('Content-Type: image/' . substr(strrchr($_GET['resource'], '.'), 1) );
			echo $content;
			exit;
		}
	}
}


/**
 * Display Images/ Icons in base64-encoding
 * @return $resourceID
 */
function feedstats_get_resource_url($resourceID) {
	
	return trailingslashit( get_bloginfo('url') ) . '?resource=' . $resourceID;
}


/**
 * settings in plugin-admin-page
 */
function feedstats_add_settings_page() {
	global $wp_version;
	
	$plugin = plugin_basename(__FILE__);
	
	if ( version_compare( $wp_version, '2.6.999', '>' ) && function_exists('add_contextual_help') ) {
		$menutitle  = '';
		$menutitle .= '<img src="' . feedstats_get_resource_url('feedstats.gif') . '" alt="" />' . ' ';
		$menutitle .= __('FeedStats', 'feedstats');
		$menutitle_count = ' <span id="awaiting-mod" class="update-plugins count-' . get_feedstats_getfeeds_button() . '"><span class="comment-count">' . get_feedstats_getfeeds_button() . '</span></span>';
		
		$user_level = get_option('fs_user_level');
		switch ($user_level) {
			case 0:
				$user_level = 'read';
				break;
			case 1:
				$user_level = 'edit_posts';
				break;
			case 2:
				$user_level = 'edit_published_posts';
				break;
			case 5:
				$user_level = 'moderate_comments';
				break;
			case 9:
				$user_level = 'manage_options';
				break;
		}
		
		$hook = add_submenu_page('index.php', __('FeedStats', 'feedstats'), $menutitle . $menutitle_count, $user_level, $plugin, 'feedstats_display_stats');
		add_contextual_help( $hook, __('<a href="http://wordpress.org/extend/plugins/feedstats-de/">Documentation</a>', 'feedstats') );
		$hook = add_options_page(__('Settings FeedStats', 'feedstats'), $menutitle, 'manage_options', 'feedstats-de-settings.php', 'feedstats_admin_option_page');
		add_contextual_help( $hook, __('<a href="http://wordpress.org/extend/plugins/feedstats-de/">Documentation</a>', 'feedstats') );
		add_filter( 'plugin_action_links_' . $plugin, 'feedstats_filter_plugin_actions_new' );
	} else {
		add_submenu_page('index.php', __('FeedStats', 'feedstats'), __('FeedStats', 'feedstats'), get_option('fs_user_level'), $plugin, 'feedstats_display_stats');
		add_options_page(__('Settings FeedStats', 'feedstats'), __('FeedStats', 'feedstats'), 'manage_options', 'feedstats-de-settings.php', 'feedstats_admin_option_page');
		
		add_filter('plugin_action_links', 'feedstats_filter_plugin_actions', 10, 2);
	}
	
}


/**
 * credit in wp-footer
 */
function feedstats_admin_footer() {
	if ( basename($_SERVER['REQUEST_URI']) == 'feedstats-de.php' || basename($_SERVER['REQUEST_URI']) == 'admin.php?page=feedstats-de-settings.php' ) {
		$plugin_data = get_plugin_data( __FILE__ );
		printf('%1$s ' . __('plugin') . ' | ' . __('Version') . ' <a href="http://bueltge.de/wp-feedstats-de-plugin/171/#historie" title="' . __('History', 'adminimize') . '">%2$s</a> | ' . __('Author') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}
}


$location = get_option('siteurl') . '/wp-admin/options-general.php?page=feedstats-de/feedstats-de.php'; // Form Action URI

// Installation functions
function feedstats_genereta_tables() {
	global $wpdb;
	
	$fs_data_query = "CREATE TABLE " . $wpdb->prefix . "fs_data (
					time_install int(11) NOT NULL default '0',
					max_visits mediumint(8) unsigned NOT NULL default '0',
					max_visits_time int(11) NOT NULL default '0',
					max_online mediumint(8) unsigned NOT NULL default '0',
					max_online_time int(11) NOT NULL default '0'
				) TYPE=MyISAM;";
	$fs_visits_query = "CREATE TABLE " . $wpdb->prefix . "fs_visits (
					visit_id mediumint(8) unsigned NOT NULL auto_increment,
					ip varchar(20) NOT NULL default '',
					url varchar(255) NOT NULL default '',
					time_begin int(11) NOT NULL default '0',
					time_last int(11) NOT NULL default '0',
					PRIMARY KEY (visit_id)
				) TYPE=MyISAM;";

	$fs_data_index_ip = "CREATE INDEX refresh USING BTREE ON " . $wpdb->prefix . "fs_visits(ip, time_last);";
	$fs_data_index_tg = "CREATE INDEX time_begin USING BTREE ON " . $wpdb->prefix . "fs_visits(time_begin);";
	$fs_data_index_tl = "CREATE INDEX time_last USING BTREE ON " . $wpdb->prefix . "fs_visits(time_last);";
	
	if (file_exists(ABSPATH . '/wp-admin/upgrade-functions.php')) {
		@require_once (ABSPATH . '/wp-admin/upgrade-functions.php');
	} elseif (file_exists(ABSPATH . '/wp-admin/includes/upgrade.php')) {
		@require_once (ABSPATH . '/wp-admin/includes/upgrade.php');
	} elseif (file_exists(ABSPATH . WPINC . '/upgrade-functions.php')) {
		@require_once (ABSPATH . WPINC . '/upgrade-functions.php');
	} elseif (file_exists(ABSPATH . '/wp-admin/upgrades.php')) {
		@require_once (ABSPATH . '/wp-admin/upgrades.php');
	} else {
		die (__('Error in file: ' . __FILE__ . ' on line: ' . __LINE__ . '.<br />The WordPress file "upgrade-functions.php" or "upgrade.php" could not be included.'));
	}
	
	maybe_create_table($wpdb->prefix . 'fs_data', $fs_data_query);
	maybe_create_table($wpdb->prefix . 'fs_visits', $fs_visits_query);

	$wpdb->query($fs_data_index_ip);
	$wpdb->query($fs_data_index_tg);
	$wpdb->query($fs_data_index_tl);

	$time = time();
	
	$count_data = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_data');
	if ($count_data==0) {
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "fs_data (time_install, max_visits, max_visits_time) VALUES (".$time.",0,".$time.")");
	}
}

// Upgrade functions
function feedstats_version_control() {
	global $wpdb;
	
	// Version 1.6.0 to 1.7.0
	// Added 4 more fields to visits table: referer, platform, browser and version
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD referer varchar(255) NOT NULL default '' AFTER ip";
	maybe_add_column($wpdb->prefix . 'fs_visits','referer',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD platform varchar(50) NOT NULL default '' AFTER referer";
	maybe_add_column($wpdb->prefix . 'fs_visits','platform',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD browser varchar(50) NOT NULL default '' AFTER platform";
	maybe_add_column($wpdb->prefix . 'fs_visits','browser',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD version varchar(15) NOT NULL default '' AFTER browser";
	maybe_add_column($wpdb->prefix . 'fs_visits','version',$create_dll);
	// Added 2 more fields to data table: max_online and max_online_time
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_data ADD max_online mediumint(8) unsigned NOT NULL default '0' AFTER max_hits_time";
	maybe_add_column($wpdb->prefix . 'fs_data','max_online',$create_dll);
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_data ADD max_online_time int(11) NOT NULL default '0' AFTER max_online";
	maybe_add_column($wpdb->prefix . 'fs_data','max_online_time',$create_dll);
	
	// Version 1.7.0 to 1.7.1
	// Fixed search engine user agent which should return no version
	$wpdb->query("UPDATE " . $wpdb->prefix . "fs_visits SET version='' WHERE browser='Crawler/Search Engine'");
	// Added 1 more field to visits table: search_terms
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD search_terms varchar(255) NOT NULL default '' AFTER version";
	maybe_add_column($wpdb->prefix . 'fs_visits','search_terms',$create_dll);
	
	// Version 1.7.4 to 1.8.0
	// Added 1 more field to visits table: url
	$create_dll = "ALTER TABLE " . $wpdb->prefix . "fs_visits ADD url varchar(255) NOT NULL default '' AFTER ip";
	maybe_add_column($wpdb->prefix . 'fs_visits','url',$create_dll);
}

// Global set of functions
function feedstats_tr($s) {
	global $feedstats_tr;

	$return = ($feedstats_tr[$s]!='') ? $feedstats_tr[$s] : $s;
	if (get_bloginfo('charset') == 'UTF-8') {
		$return = utf8_encode($return);
	}
	return $return;
}

function feedstats_reset_db() {
	global $wpdb;
	
	$wpdb->get_var("DROP TABLE " . $wpdb->prefix . "fs_visits," . $wpdb->prefix . 'fs_data');
	feedstats_genereta_tables();
}

function feedstats_get_ip() {
	global $_SERVER;
	
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		return($_SERVER['HTTP_CLIENT_IP']);
	} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return($_SERVER['HTTP_X_FORWARDED_FOR']);
	} else if (isset($_SERVER['REMOTE_ADDR'])) {
		return($_SERVER['REMOTE_ADDR']);
	} else {
		return($_SERVER['REMOTE_HOST']);
	}
}

function feedstats_get_midnight($time) {
	return date('U', mktime(0, 0, 0, 1, date('z', $time)+1, date('y',$time)));
}


// is feed url (is_feed())
function feedstats_feed_url() {
	switch ( basename($_SERVER['PHP_SELF']) ) {
		case 'wp-rdf.php':
		case 'wp-rss.php':
		case 'wp-rss2.php':
		case 'wp-atom.php':
		case 'wp-commentsrss2.php':
		case 'feed':
		case 'rss2':
		case 'atom':
			return true;
		break;
	}
	
	if ( is_feed() )
		return true;
	
	if ( isset($_GET['feed']) )
		return true;

	if ( preg_match("/^\/(feed|rss2?|atom|rdf)/Uis", $_SERVER['REQUEST_URI']) )
		return true;
	
	if ( preg_match("/\/(feed|rss2?|atom|rdf)\/?$/Uis", $_SERVER['REQUEST_URI']) )
		return true;
	
	return false;
}


// Main/System functions
function feedstats_track($title = '', $more_link_text = '', $stripteaser = '', $more_file = '', $cut = '', $encode_html = '') {
	
	global $wpdb, $_SERVER;

	$time = time();
	$url  = $_SERVER['REQUEST_URI'];
	
	if ( !feedstats_feed_url() ) {
		return $title;
		return $more_link_text;
		return $stripteaser;
		return $more_file;
		return $cut;
		return $encode_html;
	}
	
	if ( $url == get_bloginfo('rdf_url') ) {
		$url = 'RDF';
	} else if ( $url == get_bloginfo('rss_url') ) {
		$url = 'RSS';
	} else if ( $url == get_bloginfo('rss2_url') ) {
		$url = 'RSS2';
	} else if ( $url == get_bloginfo('atom_url') ) {
		$url = 'ATOM';
	} else if ( $url == get_bloginfo('comments_rss2_url') ) {
		$url = 'COMMENT RSS';
	} else if ( $url == get_bloginfo('comments_atom_url') ) {
		$url = 'COMMENT ATOM';
	} else if ( preg_match("/^\/(feed|rss2?|atom|rdf)/Uis", $url) ) {
		$url = $_SERVER['REQUEST_URI'];
	} else if ( preg_match("/\/(feed|rss2?|atom|rdf)\/?$/Uis", $url) ) {
		$url = $_SERVER['REQUEST_URI'];
	} else if ( isset($_GET["feed"]) ) {
		$url = $_SERVER['REQUEST_URI'];
	}
	
	$time_delete = feedstats_get_midnight( $time-(FEEDSTATS_DAY*get_option('fs_days')) );	
	$wpdb->query("DELETE FROM " . $wpdb->prefix . "fs_visits WHERE time_begin < ".$time_delete);
	
	if ( in_array( feedstats_get_ip(), array( get_option('fs_ifs_not_tracked') ) ) ) {
		return $title;
		return $more_link_text;
		return $stripteaser;
		return $more_file;
		return $cut;
		return $encode_html;
	}
		
	$time_insert_visit = $time - get_option('fs_session_timeout');
	
	if ($wpdb->is_admin || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
		$sessions = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE ip='" . feedstats_get_ip() . "' AND time_last > " . $time_insert_visit);
		if ($sessions > 0) {
			$wpdb->query("UPDATE " . $wpdb->prefix . "fs_visits SET time_last=" . $time . ",url='" . $url . "' WHERE ip='" . feedstats_get_ip() . "' AND time_last > " . $time_insert_visit);
		}
		return;
	}
	
	$ip_time_query = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE ip='".feedstats_get_ip()."' AND time_last > " . $time_insert_visit);
	
	if ($ip_time_query == 0) {
		$wpdb->query("INSERT INTO " . $wpdb->prefix . "fs_visits (ip, url, time_begin, time_last) VALUES ('" . feedstats_get_ip() . "','" . $url . "'," . $time . "," . $time . ")");
	} else {
		$wpdb->query("UPDATE " . $wpdb->prefix . "fs_visits SET time_last=" . $time . ",url='" . $url . "' WHERE ip='" . feedstats_get_ip() . "' AND time_last > " . $time_insert_visit);
	}
	
	$time_start       = feedstats_get_midnight($time);
	$time_end         = $time_start + FEEDSTATS_DAY;
	$count_visits_day = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE time_begin >= " . $time_start . " AND time_begin < " . $time_end);
	$max_visits       = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');

	if ($count_visits_day>=$max_visits) {
		$wpdb->query("UPDATE " . $wpdb->prefix . "fs_data SET max_visits = " . $count_visits_day . ", max_visits_time = " . $time);		
	}
	
	$time_visits_online = $time - get_option('fs_visits_online');
	$count_online = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "fs_visits WHERE time_last > " . $time_visits_online);
	$max_online = $wpdb->get_var("SELECT max_online FROM " . $wpdb->prefix . 'fs_data');
	
	if ($count_online>=$max_online) {
		$wpdb->query("UPDATE " . $wpdb->prefix . "fs_data SET max_online = " . $count_online . ", max_online_time = " . $time);
	}
	
	return $title;
	return $more_link_text;
	return $stripteaser;
	return $more_file;
	return $cut;
	return $encode_html;
}

function feedstats_display_stats() {
	global $wpdb;
	
	if ( isset($_GET['fs_action']) && $_GET['fs_action'] == 'reset')
		feedstats_reset_db();
		
	$time = time();
	
	$time_begin = feedstats_get_midnight( $wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data') );
	$num_days   = ceil( ($time-$time_begin)/FEEDSTATS_DAY );
	$num_days   = htmlspecialchars($num_days, ENT_QUOTES);
	if ( $num_days > get_option('fs_days') ) {
		$num_days = get_option('fs_days') + 1;
	}
	$visits = array();
	$count_visits_total  = 0;
	
	for ($i=0; $i < $num_days; $i++) {
		$day_time          = $time - ($i * FEEDSTATS_DAY);
		$time_start        = feedstats_get_midnight($day_time);
		$time_end          = $time_start + FEEDSTATS_DAY;
		$count_visits_day  = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE time_begin >= " . $time_start . " AND time_begin < " . $time_end);
		$visits[$day_time] = $count_visits_day;
		
		if ($i!=0 && $time_start!=$time_begin) {
			$count_visits_total += $count_visits_day;
		}
		
	}
	
	$total_visits    = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits    = htmlspecialchars($total_visits, ENT_QUOTES);
	
	$average_visits  = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits  = htmlspecialchars($average_visits, ENT_QUOTES);

	$max_visits      = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');
	$max_visits      = htmlspecialchars($max_visits, ENT_QUOTES);
	
	$max_visits_time = date(get_option('date_format'), $wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_visits_time = htmlspecialchars($max_visits_time, ENT_QUOTES);
	
	$max_online      = $wpdb->get_var("SELECT max_online FROM " . $wpdb->prefix . 'fs_data');
	$max_online      = htmlspecialchars($max_online, ENT_QUOTES);
	
	$max_online_time = date(get_option('date_format'), $wpdb->get_var("SELECT max_online_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_online_time = htmlspecialchars($max_online_time, ENT_QUOTES);

	$referers_query  = $wpdb->get_results("SELECT DISTINCT url FROM " . $wpdb->prefix . "fs_visits WHERE LEFT(url, '" . strlen(get_settings('home')) . "') != '" . get_settings('home') . "' AND url <> '' ORDER BY url DESC");
	
	$referers        = array();
	
	if ($referers_query) {
		foreach ($referers_query as $r) {
			$refer['cont']  = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "fs_visits WHERE url = '" . $r->url . "';");
			$refer['cont']  = htmlspecialchars($refer['cont']);
			$refer['title'] = $r->url;
			$refer['title'] = htmlspecialchars($refer['title']);
			array_push($referers,$refer);
		}
	}

	$time_visits_online = $time - get_option('fs_visits_online');
	
	$online_query       = $wpdb->get_results("SELECT ip,url FROM " . $wpdb->prefix . "fs_visits WHERE time_last > ".$time_visits_online);
	
	$online             = array();
	
	if ($online_query) {
		foreach ($online_query as $visit) {
			$o['ip']  = $visit->ip;
			$o['url'] = $visit->url;
			array_push($online,$o);
		}
	}
	
	$people_online = count($online);	
?>
	<div class="wrap">
		<h2><img src="<?php echo feedstats_get_resource_url('feedstats32.gif'); ?>" alt="" width="32" height="32" /> FeedStats</h2>
		<p id="feeds_button"><?php feedstats_getfeeds_button(); ?></p>
		<br class="clear" />
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0" summary="feedstast view">
			<tr valign="top">
				<td colspan="3" align="center" valign="top" style="height:160px;">
	
					<table align="center" style="height: 140px; border: 1px solid #CCC;" summary="feedstast view two">
						<tr valign="top">
							<th colspan="<?php echo count($visits); ?>" align="center"><?php echo feedstats_tr(__('Visits', 'feedstats')); ?></th>
						</tr>
						<tr>
							<?php $i = 1; krsort($visits); foreach ($visits as $day => $num) { ?>
							<td align="center" style="padding-left: 5px; font-size: 10px; color:#A3A3A3;"><?php echo $num; ?></td>
							<?php
								if ( $i == get_option('fs_view_days') )
									break;
								$i++;
							} ?>
							<td align="center" style="padding-left: 5px; font-size: 10px; color: #CCC"><?php echo $average_visits; ?></td>
							<td align="center" style="padding-left: 5px; font-size: 10px; color:#FF0000"><?php echo $max_visits; ?></td>
						</tr>
						<tr>
							<?php 
							if ($max_visits == 0) { ?>
								<td valign="bottom" align="center"><?php _e('No datas', 'feedstats'); ?></td>
							<?php
							} else {
								$i = 1;
								foreach ($visits as $day => $num) { ?>
								<td valign="bottom" align="center"><div title="<?php echo date('j. M',$day), ": ", $num; ?>" style="width: 16px; height: <?php echo round(100*($num/$max_visits)); ?>px; background-color: #A3A3A3; border-bottom: 1px solid #A3A3A3;">&nbsp;</div></td>
								<?php
									if ( $i == get_option('fs_view_days') )
										break;
									$i++;
								} ?>
								<td valign="bottom" style="padding-left: 5px;"><div title="<?php _e('Average', 'feedstats').": ".$average_visits; ?>" style="width: 16px; height: <?php echo round(100*($average_visits/$max_visits)); ?>px; background-color: #CCC; border-bottom: 1px solid #CCC;">&nbsp;</div></td>
								<td valign="bottom" style="padding-left: 5px;"><div title="<?php _e('Maximum', 'feedstats').": ".$max_visits; ?>" style="width: 16px; height: <?php echo round(100*($max_visits/$max_visits)); ?>px; background-color:#FFCC66; border-bottom: 1px solid #CCC;">&nbsp;</div></td>
								<?php
							}
							?>
						</tr>
						<tr>
							<?php $i = 1; foreach ($visits as $day => $num) { ?>
							<td align="center" style="font-size: 10px;"><?php echo date('j',$day); ?></td>
							<?php
								if ( $i == get_option('fs_view_days') )
									break;
								$i++;
							} ?>
							<td align="center" style="padding-left: 5px; font-size: 10px;">&Oslash;</td>
							<td align="center" style="padding-left: 5px; font-size: 10px; color:#FF0000">Max</td>
						</tr>
					</table>
	
				</td>
			</tr>

			<tr valign="top"> 
				<td width="6%" rowspan="2" valign="top" style="padding-right:3px;">
					
					<table summary="feedstast view one" class="widefat" style="margin: 5px 5px 0 0;">
						<thead>
						<tr valign="top">
							<th><?php _e('Day', 'feedstats'); ?></th>
							<th><?php _e('Visits', 'feedstats'); ?></th>
						</tr>
						</thead>
						<tbody>
						<?php
							krsort($visits);
							$class = '';
							foreach ($visits as $day=>$num) {
								$class = ($class=='form-invalid') ? '' : 'form-invalid';
								if (date('j M Y',$day)==date('j M Y',time())) {
									$day_s = __('Today', 'feedstats');
								} else if (date(get_option('date_format'),$day)==date(get_option('date_format'),$time_begin)) {
									$day_s = __('First Day', 'feedstats');
								} else {
									$day_s = date('j.M',$day);
								}
						?>
						<tr class="<?php echo $class; ?>">
							<th><?php echo $day_s; ?></th>
							<td><?php echo $num; ?></td>
						</tr>
						<?php } //end foreach ?>
					</tbody>
					</table> 
					
				</td>
				
				<td width="45%" align="right" valign="top" style="padding-right:3px;">
					
					<table style="margin: 5px 5px 0 0;" summary="feedstast view three" class="widefat">
						<thead>
							<tr valign="top"> 
							<th width="278" colspan="2" scope="col"><?php echo str_replace('%N%', get_option('fs_num_referers'), __('Last %N% Referer', 'feedstats')); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ($referers) {
								arsort($referers);
								$class = '';
								foreach ($referers as $r) {
									$class = ($class=='form-invalid') ? '' : 'form-invalid';
							?>
							<tr class="<?php echo $class; ?>"> 
								<th><?php echo htmlspecialchars($r['cont']); ?></th>
								<td><?php echo htmlspecialchars((strlen($r['title'])>50) ? substr_replace($r['title'],"...",50) : $r['title']); ?></td>
							</tr>
							<?php } //end foreach
							} //end if ?>
						</tbody>
					</table>
					
				</td>
				<td width="35%" align="right" valign="top">
					
					<table style="margin: 5px 0 0 0;" summary="feedstast view four" class="widefat">
						<thead>
							<tr> 
								<th><?php _e('Statistic', 'feedstats'); ?></th>
								<th><?php _e('Visits', 'feedstats'); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr class="form-invalid"> 
								<th><?php _e('Average', 'feedstats'); ?></th>
								<td><?php echo ($average_visits != 0) ? $average_visits : '-' ?></td>
							</tr>
							<tr> 
								<th><?php _e('Maximum', 'feedstats'); ?></th>
								<td><?php echo $max_visits ?> (<?php echo $max_visits_time ?>)</td>
							</tr>
							<tr class="form-invalid"> 
								<th><?php _e('Total', 'feedstats'); ?> <?php _e(' (Last ', 'feedstats') . _e($num_days) . _e(' Days)', 'feedstats'); ?></th>
								<td><?php echo $total_visits ?></td>
							</tr>
							<tr> 
								<th><?php _e('Maximum online', 'feedstats'); ?></th>
								<td><?php echo $max_online ?> (<?php echo $max_online_time ?>)</td>
							</tr>
							</tbody>
					</table>
					
				</td>
			</tr>
		</table>
		
		<br class="clear" />
		
		<h3><?php _e('Reset Statistic', 'feedstats'); ?></h3>
		<p><a class="button button-primary" href="index.php?page=feedstats-de/feedstats-de.php&amp;fs_action=reset" onclick="return confirm('<?php _e('You are about to delete all data and reset stats. OK to delete, Cancel to stop', 'feedstats'); ?>');"><?php _e('Reset Statistic', 'feedstats'); ?> &raquo;</a></p>
	</div>

<?php
}


function feedstats_getfeeds() {
	global $wpdb;
	
	$total_visits = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits = htmlspecialchars($total_visits, ENT_QUOTES);

	$time       = time();
	$time_begin = feedstats_get_midnight($wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data'));
	$num_days   = ceil(($time-$time_begin)/FEEDSTATS_DAY);
	$num_days   = htmlspecialchars($num_days, ENT_QUOTES);
	
	if ( $num_days > get_option('fs_days') ) {
		$num_days = get_option('fs_days') + 1;
	}
		
	$average_visits  = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits  = htmlspecialchars($average_visits, ENT_QUOTES);
	
	$max_visits      = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');
	$max_visits      = htmlspecialchars($max_visits, ENT_QUOTES);
	
	$max_visits_time = date(get_option('date_format'), $wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_visits_time = htmlspecialchars($max_visits_time, ENT_QUOTES);
	?>
	<div id="feeds_readers">
		<h3><?php _e('FeedReaders', 'feedstats'); ?></h3>
		<ul>
			<li><?php _e('Total', 'feedstats') . _e(': ') . _e( attribute_escape($total_visits) ); ?><small><?php _e(' (Last ', 'feedstats') . _e($num_days) . _e(' Days)', 'feedstats'); ?></small></li>
			<li><?php _e('Maximum', 'feedstats') . _e(': ') . _e( attribute_escape($max_visits) ); ?> <small>(<?php echo $max_visits_time; ?>)</small></li>
			<li><?php _e('Average', 'feedstats') . _e(': ') . _e( attribute_escape($average_visits) ); ?></li>
		</ul>
	</div>
	<?php
}


// for older functions
function fs_getfeeds() {
	feedstats_getfeeds();
}


// feedstats-button
function get_feedstats_getfeeds_button() {
	global $wpdb;
	
	$total_visits = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits = htmlspecialchars($total_visits, ENT_QUOTES);
	
	$time         = time();
	$time_begin   = feedstats_get_midnight( $wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data') );
	$num_days     = ceil( ($time-$time_begin) / FEEDSTATS_DAY );
	
	if ( $num_days > get_option('fs_days') ) {
		$num_days = get_option('fs_days') + 1;
	}
		
	$average_visits = ($num_days) ? round( $total_visits / ($num_days) ) : '0';
	$average_visits = htmlspecialchars($average_visits, ENT_QUOTES);
	
	return $average_visits;
}


function feedstats_getfeeds_button() {
	echo get_feedstats_getfeeds_button();
}

// for older functions
function fs_getfeeds_button() {
	feedstats_getfeeds_button();
}


// style im header
function feedstats_admin_header() {
	$fs_feed_button_style = '<style type="text/css" media="screen">';
	$fs_feed_button_style.= '#feeds_button {
		float: right;
		width: 74px;
		height: 14px;
		text-align: left;
		font-size: 10px;
		padding: 1px 15px 15px 3px;
		color: #fff;
		background: url(' . WP_CONTENT_URL . '/plugins/feedstats-de/images/feedstats.gif) no-repeat 0 1px;
		margin: 0;
	}';
	$fs_feed_button_style.= '</style>';
	$fs_feed_button_style.= "\n";
	
	print ($fs_feed_button_style);
}


// wp-dashboard (Tellerrand) information
function feedstats_add_dashboard() {
	global $wpdb, $wp_version;

	$total_visits = $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . 'fs_visits');
	$total_visits = htmlspecialchars($total_visits, ENT_QUOTES);

	$time         = time();
	$time_begin   = feedstats_get_midnight($wpdb->get_var("SELECT time_install FROM " . $wpdb->prefix . 'fs_data'));
	$num_days     = ceil(($time-$time_begin)/FEEDSTATS_DAY);
	
	if ( $num_days > get_option('fs_days') ) {
		$num_days = get_option('fs_days') + 1;
	}
		
	$average_visits = ($num_days) ? round($total_visits/($num_days)) : '0';
	$average_visits = htmlspecialchars($average_visits, ENT_QUOTES);
	
	$max_visits = $wpdb->get_var("SELECT max_visits FROM " . $wpdb->prefix . 'fs_data');
	$max_visits = htmlspecialchars($max_visits, ENT_QUOTES);
	
	//$max_visits_time = date('j. F Y',$wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data'));
	$max_visits_time = htmlspecialchars(strftime('%d. %B %Y',$wpdb->get_var("SELECT max_visits_time FROM " . $wpdb->prefix . 'fs_data')), ENT_QUOTES);
	
	$content = '';
	if ( version_compare( $wp_version, '2.6.999', '<' ) ) {
		$content  = '<h3>' . __('FeedStats', 'feedstats') . ' <a href="admin.php?page=feedstats-de/feedstats-de-settings.php" title="' . __('to settings of FeedStats', 'feedstats') . '">&raquo;</a></h3>';
	}
	$content .= '<ul><li>' . __('Total', 'feedstats') . __(': ') . attribute_escape($total_visits) . __(' (Last ', 'feedstats') . $num_days . __(' Days)', 'feedstats') . '</li>';
	$content .= '<li>' . __('Maximum', 'feedstats') . __(': ') . attribute_escape($max_visits) . ' (' . attribute_escape($max_visits_time) . ')</li>';
	$content .= '<li>' . __('Average', 'feedstats') . __(': ') . attribute_escape($average_visits) . '</li>';
	$content .= '</ul>';
	$content .= '<p class="textright"><a href="index.php?page=feedstats-de/feedstats-de.php" class="button">' . __('View all', 'feedstats') . '</a></p>';
	
	print ($content);
}


/**
 * add dashboard widget
 * >= WordPress 2.7
 */
function feedstats_add_dashboard_new() {
	wp_add_dashboard_widget( 'feedstats_dashboard_widget', __('FeedStats', 'feedstats') . ' <a href="admin.php?page=feedstats-de-settings.php" title="' . __('to settings of FeedStats', 'feedstats') . '">&raquo;</a>', 'feedstats_add_dashboard' );
}


// Program flow
function feedstats_activate() {
	add_option('fs_view_days', '15');
	add_option('fs_days', '30');
	add_option('fs_user_level', '1');
	add_option('fs_session_timeout', '3600');
	add_option('fs_visits_online', '300');
}

if ( function_exists('register_activation_hook') ) {
	register_activation_hook(__FILE__, 'feedstats_genereta_tables');
	register_activation_hook(__FILE__, 'feedstats_activate');
	register_activation_hook(__FILE__, 'feedstats_version_control');
}

if ( function_exists('add_action') ) {
	add_action('init', 'feedstats_textdomain');
	add_action('the_title_rss', 'feedstats_track');
	add_action('the_content_rss', 'feedstats_track');
	if ( is_admin() ) {
		add_action('admin_menu', 'feedstats_add_settings_page');
		add_action('in_admin_footer', 'feedstats_admin_footer');
		if (strpos($_SERVER['REQUEST_URI'], 'page=feedstats-de/feedstats-de') !== false)
			add_action('admin_head', 'feedstats_admin_header');
	}
	
	$admin = dirname($_SERVER['SCRIPT_FILENAME']);
	$admin = substr($admin, strrpos($admin, '/') +1 );
	
	if ( is_admin() && basename($_SERVER['SCRIPT_FILENAME']) == 'index.php' && get_option('fs_ifs_dashboardinfo') == '1') {
		if ( version_compare( $wp_version, '2.6.999', '>' ) ) {
			add_action('wp_dashboard_setup', 'feedstats_add_dashboard_new');
		} else {
			add_action('activity_box_end', 'feedstats_add_dashboard');
		}
	}
}


// some basic security with nonce
if ( !function_exists('wp_nonce_field') ) {
	function feedstats_nonce_field($action = -1) { return; }
	$FeedStats_nonce = -1;
} else {
	function feedstats_nonce_field($action = -1) { return wp_nonce_field($action); }
	$FeedStats_nonce = 'FeedStats-update-key';
}

require_once('feedstats-de-settings.php');
?>
